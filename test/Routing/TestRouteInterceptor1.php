<?php


namespace Kinikit\MVC\Routing;

use Kinikit\MVC\Response\SimpleResponse;

/**
 * @rateLimited
 *
 * @noProxy
 *
 * Class TestRouteInterceptor1
 * @package Kinikit\MVC\Routing
 */
class TestRouteInterceptor1 extends RouteInterceptor {

    public static $beforeRoutes = 0;
    public static $afterRoutes = 0;

    public static $returnResponseBefore = false;


    public function beforeRoute($request) {
        self::$beforeRoutes++;

        if (self::$returnResponseBefore)
            return new SimpleResponse("RESPONSE");
        else
            parent::beforeRoute($request);
    }

    public function afterRoute($response) {
        self::$afterRoutes++;
        return parent::afterRoute($response);
    }


}
