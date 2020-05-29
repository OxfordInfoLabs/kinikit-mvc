<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\Core\Binding\ObjectBinder;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\StatusException;
use Kinikit\Core\Exception\WrongParameterTypeException;
use Kinikit\Core\Reflection\ClassInspectorProvider;
use Kinikit\Core\Reflection\Method;
use Kinikit\Core\Serialisation\JSON\JSONToObjectConverter;
use Kinikit\Core\Util\Primitive;
use Kinikit\MVC\ContentCaching\ContentCacheConfig;
use Kinikit\MVC\RateLimiter\RateLimiterConfig;
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

        // Populate rate limit and caching data.
        list($rateLimiterConfig, $caching) = $this->getRateLimiterAndCaching();

        // Determine the route type based upon the return value of the method.
        $routeType = $targetMethod->getReturnType() && $targetMethod->getReturnType()->isInstanceOf(Response::class) &&
        !($targetMethod->getReturnType()->isInstanceOf(JSONResponse::class)) ? self::ROUTE_TYPE_WEB : self::ROUTE_TYPE_JSON;

        parent::__construct($rateLimiterConfig, $caching, $routeType);
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


        /**
         * @var ClassInspectorProvider $classInspectorProvider
         */
        $classInspectorProvider = Container::instance()->get(ClassInspectorProvider::class);
        $classInspector = $classInspectorProvider->getClassInspector(get_class($instance));

        // Grab the proxied method
        $proxiedMethod = $classInspector->getPublicMethod($this->targetMethod->getMethodName());

        // Execute the method - Exceptions are handled higher up the food chain.
        $result = $proxiedMethod->call($instance, $params);

        if ($result instanceof Response) {

            // Inject common view params to model for convenience.
            if ($result instanceof View) {
                $this->injectParamsIntoViewModel($result, ["request" => $this->request]);
            }

            return $result;
        } else {
            return new JSONResponse($result);
        }


    }


    // Sanitise parameter values
    private function sanitiseParamValue($paramKey, $paramValue) {

        $methodParams = $this->targetMethod->getIndexedParameters();


        // Only bother if there are method params matching our method.
        if (isset($methodParams[$paramKey])) {

            if ($methodParams[$paramKey]->isPrimitive()) {

                if (!Primitive::isOfPrimitiveType($methodParams[$paramKey]->getType(), $paramValue))
                    throw new WrongParameterTypeException("The parameter $paramKey is of the wrong type");

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


    // Populate rate limit and caching info from annotations if they are set.
    private function getRateLimiterAndCaching() {

        // Derive rate limit and caching rules by checking method and falling back to class
        $methodAnnotations = $this->targetMethod->getMethodAnnotations();

        $rateLimited = isset($methodAnnotations["rateLimited"][0]);
        $rateLimit = isset($methodAnnotations["rateLimit"][0]) ? $methodAnnotations["rateLimit"][0]->getValue() : null;
        $rateLimitMultiplier = isset($methodAnnotations["rateLimitMultiplier"][0]) ? $methodAnnotations["rateLimitMultiplier"][0]->getValue() : null;

        $cached = isset($methodAnnotations["cached"][0]);
        $cacheTime = isset($methodAnnotations["cacheTime"][0]) ? $methodAnnotations["cacheTime"][0]->getValue() : null;


        $controllerAnnotations = $this->targetMethod->getDeclaringClassInspector()->getClassAnnotations();

        if (!$rateLimited)
            $rateLimited = isset($controllerAnnotations["rateLimited"][0]);

        // Only check rate limits if none already set.
        if (!$rateLimitMultiplier && !$rateLimit) {
            if (isset($controllerAnnotations["rateLimit"][0])) {
                $rateLimit = $controllerAnnotations["rateLimit"][0]->getValue();
            } else if (isset($controllerAnnotations["rateLimitMultiplier"][0])) {
                $rateLimitMultiplier = $controllerAnnotations["rateLimitMultiplier"][0]->getValue();
            }
        }

        if (!$cached)
            $cached = isset($controllerAnnotations["cached"][0]);


        if (!$cacheTime)
            $cacheTime = isset($controllerAnnotations["cacheTime"][0]) ? $controllerAnnotations["cacheTime"][0]->getValue() : null;


        return array($rateLimited || $rateLimit || $rateLimitMultiplier ? new RateLimiterConfig($rateLimit, $rateLimitMultiplier) : null,
            $cached || $cacheTime ? new ContentCacheConfig($cacheTime) : null);

    }

}
