<?php


namespace Kinikit\MVC\Routing;

/**
 *
 * @cacheTime 3d
 *
 * @noProxy
 * Class TestRouteInterceptor2
 * @package Kinikit\MVC\Routing
 */
class TestRouteInterceptor2 extends RouteInterceptor {

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
