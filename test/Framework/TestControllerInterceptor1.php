<?php


namespace Kinikit\MVC\Framework;


class TestControllerInterceptor1 extends ControllerInterceptor {

    private $succeed;
    public static $interceptorRuns = array();

    public function __construct($controllerName = null, $succeed = null) {
        $this->succeed = $succeed;
    }

    /**
     *
     */
    /**
     * @return the $succeed
     */
    public function getSucceed() {
        return $this->succeed;
    }

    /**
     * @param $succeed the $succeed to set
     */
    public function setSucceed($succeed) {
        $this->succeed = $succeed;
    }

    public function beforeHandleRequest($controllerName) {
        TestControllerInterceptor1::$interceptorRuns [] = "TestControllerInterceptor1";
        return $this->succeed;
    }

}

?>