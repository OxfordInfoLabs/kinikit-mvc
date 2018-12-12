<?php
/**
 * Created by PhpStorm.
 * User: nathanalan
 * Date: 14/09/2018
 * Time: 14:27
 */

namespace Kinikit\MVC\Framework;


use Kinikit\Core\Object\SerialisableObject;

class ControllerInterceptorDefinition extends SerialisableObject {

    private $className;
    private $controllers;

    /**
     * ControllerInterceptorDefinition constructor.
     * @param $className
     */
    public function __construct($className = null) {
        $this->className = $className;
    }


    /**
     * @return mixed
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @param mixed $className
     */
    public function setClassName($className) {
        $this->className = $className;
    }

    /**
     * @return mixed
     */
    public function getControllers() {
        return $this->controllers;
    }

    /**
     * @param mixed $controllers
     */
    public function setControllers($controllers) {
        $this->controllers = $controllers;
    }


}