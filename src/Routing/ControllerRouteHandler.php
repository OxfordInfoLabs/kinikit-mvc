<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Binding\ObjectBinder;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\StatusException;
use Kinikit\Core\Reflection\Method;
use Kinikit\Core\Serialisation\JSON\JSONToObjectConverter;
use Kinikit\Core\Util\Primitive;
use Kinikit\MVC\Request\FileUpload;
use Kinikit\MVC\Request\Headers;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Request\URL;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\Response;
use Kinikit\MVC\Response\View;
use Kinikit\MVC\Response\WebErrorResponse;

class ControllerRouteHandler extends RouteHandler {

    /**
     * @var Method
     */
    private $targetMethod;

    /**
     * @var Request
     */
    private $request;


    /**
     * @var string
     */
    private $methodRequestPath;

    /**
     * ControllerRouteHandler constructor.
     *
     * @param Method $targetMethod
     * @param Request $request
     * @param string $methodRequestPath
     */
    public function __construct($targetMethod, $request, $methodRequestPath) {
        $this->targetMethod = $targetMethod;
        $this->request = $request;
        $this->methodRequestPath = $methodRequestPath;
    }


    /**
     * Handle the route and return a response
     *
     * @return Response
     */
    public function handleRoute() {

        // Get an instance of the class represented by this method.
        $instance = Container::instance()->get($this->targetMethod->getDeclaringClassInspector()->getClassName());

        // Gather parameters
        $params = [];

        // Add URL parameters if supplied.
        $methodAnnotations = $this->targetMethod->getMethodAnnotations();

        if (isset($methodAnnotations["http"])) {
            $explodedAnnotation = explode(" ", $methodAnnotations["http"][0]->getValue());
            if (sizeof($explodedAnnotation) > 1) {
                $methodPath = explode("/", ltrim($explodedAnnotation[1], "/"));
                $requestPath = explode("/", $this->methodRequestPath);

                foreach ($methodPath as $index => $item) {
                    if (substr($item, 0, 1) == "$") {
                        $paramKey = substr($item, 1);
                        $paramValue = $this->sanitiseParamValue($paramKey, $requestPath[$index]);
                        $params[$paramKey] = $paramValue;
                    }
                }
            }
        }


        $methodParams = $this->targetMethod->getParameters();

        // if we have a payload, ensure we de-jsonify it and assume the next sequential parameter by default.
        if ($this->request->getPayload()) {
            if (sizeof($methodParams) > sizeof($params)) {
                $payloadParam = $methodParams[sizeof($params)];
                $converter = Container::instance()->get(JSONToObjectConverter::class);
                $params[$payloadParam->getName()] = $converter->convert($this->request->getPayload(), $payloadParam->getType());
            }
        }


        // Poke in all other regular parameters at the end.
        foreach ($this->request->getParameters() as $key => $value) {
            $params[$key] = $this->sanitiseParamValue($key, $value);
        }

        // Finally poke in any other unresolved request objects as autowires.
        foreach ($methodParams as $methodParam) {
            if (!isset($params[$methodParam->getName()])) {
                switch (ltrim($methodParam->getType(), "\\")) {
                    case Request::class:
                        $params[$methodParam->getName()] = $this->request;
                        break;
                    case URL::class:
                        $params[$methodParam->getName()] = $this->request->getUrl();
                        break;
                    case Headers::class:
                        $params[$methodParam->getName()] = $this->request->getHeaders();
                        break;
                    case FileUpload::class . "[]":
                        $params[$methodParam->getName()] = $this->request->getFileUploads();
                        break;
                }
            }
        }


        // Execute the method
        try {
            $result = $this->targetMethod->call($instance, $params);

            if ($result instanceof Response) {

                // Inject common view params to model for convenience.
                if ($result instanceof View) {
                    $this->injectParamsIntoViewModel($result, ["request" => $this->request]);
                }

                return $result;
            } else {
                return new JSONResponse($result);
            }

        } catch (\Throwable $e) {

            $statusCode = $e instanceof StatusException ? $e->getStatusCode() : 500;

            // Non JSON responses are assumed to be HTML web based
            if ($this->targetMethod->getReturnType()->isInstanceOf(Response::class) &&
                !$this->targetMethod->getReturnType()->isInstanceOf(JSONResponse::class)) {
                return new WebErrorResponse($e->getMessage(), $e->getCode(), $statusCode);
            } else {
                return new JSONResponse(["errorMessage" => $e->getMessage(), "errorCode" => $e->getCode()], $statusCode);
            }

        }


    }


    // Sanitise parameter values
    private function sanitiseParamValue($paramKey, $paramValue) {

        $methodParams = $this->targetMethod->getIndexedParameters();

        // Only bother if there are method params matching our method.
        if (isset($methodParams[$paramKey])) {

            if ($methodParams[$paramKey]->isPrimitive()) {
                return Primitive::convertToPrimitive($methodParams[$paramKey]->getType(), $paramValue);
            }

        }

        return $paramValue;


    }


    /**
     * Inject params into the view model using reflection
     *
     * @param View $view
     * @param array $params
     */
    protected function injectParamsIntoViewModel($view, $params) {

        // Get the model and merge.
        $model = $view->getModel();
        $model = array_merge($model, $params);

        // Make accessible and update the view model
        $reflectionProperty = (new \ReflectionClass(View::class))->getProperty("model");
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($view, $model);


    }

}
