<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Response;

class DecoratorRouteHandler extends RouteHandler {

    /**
     * @var Method
     */
    private $targetDecoratorMethod;

    /**
     * @var Method
     */
    private $targetControllerMethod;

    /**
     * @var Request
     */
    private $request;


    /**
     * ControllerRouteHandler constructor.
     *
     * @param Method $targetDecoratorMethod
     * @param Method $targetControllerMethod
     * @param Request $request
     */
    public function __construct($targetDecoratorMethod, $targetControllerMethod, $request) {
        $this->targetDecoratorMethod = $targetDecoratorMethod;
        $this->targetControllerMethod = $targetControllerMethod;
        $this->request = $request;
    }


    /**
     * Handle this route and return a response
     *
     * @return Response
     */
    public function handleRoute() {
        // TODO: Implement executeAndSendResponse() method.
    }

}
