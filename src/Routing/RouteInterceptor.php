<?php


namespace Kinikit\MVC\Routing;


use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Response;

class RouteInterceptor {


    /**
     * Before route method.  Useful for global security checking etc.  It receives the global request object as an argument for convenience.
     *
     * Returning a response from this method will prevent execution of the route and send the response instead.
     * Returning null will continue with processing.
     *
     * @param Request $request
     * @return Response|null
     */
    public function beforeRoute($request) {
    }


    /**
     * After route method.  Useful for e.g. logging etc.   Receives the response returned from the route.
     * By default this method returns the response intact but an alternative response can be returned here if required.
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function afterRoute($request, $response) {
        return $response;
    }


}
