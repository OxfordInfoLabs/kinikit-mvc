<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;

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
     * Execute any route logic and stream the response straight to
     * stdout.  Typically route handlers should defer any heavy lifting
     * to this method as the framework will optimise for rate limiting
     * prior to calling this method.
     *
     * @return mixed
     */
    public function executeAndSendResponse() {
        // TODO: Implement executeAndSendResponse() method.
    }

}
