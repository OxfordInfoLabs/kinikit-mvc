<?php


namespace Kinikit\MVC\Routing;

/**
 * @rateLimit 5
 * @cached
 *
 * @noProxy
 * Class TestRouteInterceptor1
 * @package Kinikit\MVC\Routing
 */
class TestRouteInterceptor3 extends RouteInterceptor {

    public static $beforeRoutes = 0;
    public static $afterRoutes = 0;


    public function beforeRoute($request) {
        self::$beforeRoutes++;
        parent::beforeRoute($request);
    }

    public function afterRoute($request, $response) {
        self::$afterRoutes++;
        return parent::afterRoute($request, $response);
    }


}
