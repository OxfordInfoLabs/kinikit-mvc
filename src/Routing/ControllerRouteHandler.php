<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Binding\ObjectBinder;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\Method;
use Kinikit\Core\Serialisation\JSON\JSONToObjectConverter;
use Kinikit\Core\Util\Primitive;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\JSONResponse;
use Kinikit\MVC\Response\Response;

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


        // if we have a payload, ensure we de-jsonify it and assume the next sequential parameter by default.
        if ($this->request->getPayload()) {
            $converter = Container::instance()->get(JSONToObjectConverter::class);
            $payloadParam = $this->targetMethod->getParameters()[sizeof($params)];
            $params[$payloadParam->getName()] = $converter->convert($this->request->getPayload(), $payloadParam->getType());
        }


        // Poke in all other regular parameters at the end.
        foreach ($this->request->getParameters() as $key => $value) {
            $params[$key] = $this->sanitiseParamValue($key, $value);
        }


        // Execute the method
        $result = $this->targetMethod->call($instance, $params);

        return new JSONResponse($result);


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
}
