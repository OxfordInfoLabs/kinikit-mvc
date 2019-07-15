<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;

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
