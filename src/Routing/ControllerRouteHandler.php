<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Reflection\Method;
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
     * ControllerRouteHandler constructor.
     *
     * @param Method $targetMethod
     * @param Request $request
     */
    public function __construct($targetMethod, $request) {
        $this->targetMethod = $targetMethod;
        $this->request = $request;
    }


    /**
     * Handle the route and return a response
     *
     * @return Response
     */
    public function handleRoute() {

        // Get an instance of the class represented by this method.
        $instance = Container::instance()->get($this->targetMethod->getDeclaringClassInspector()->getClassName());

        // Execute the method
        $result = $this->targetMethod->call($instance, []);

        return new JSONResponse($result);


    }
}
