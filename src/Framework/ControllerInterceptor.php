<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Object\SerialisableObject;
use Kinikit\Core\Util\Annotation\ClassAnnotations;

/**
 * Implementation of the operation interceptor for trapping calls to controllers if required.  The beforeHandleRequest method is called
 * before the handle request for the controller in order to allow for vetoeing of the controller if required.
 *
 * @author mark
 *
 */
class ControllerInterceptor extends SerialisableObject {

    /**
     * Method level interceptor for controller.  This is called before every method
     * is invoked to allow vetoing for e.g. permission issues.
     *
     * This should return true if the method is allowed to be called or false otherwise
     *
     * @param Controller $controllerInstance
     * @param string $methodName
     * @param ClassAnnotations $classAnnotations
     *
     * @return boolean
     */
    public function beforeMethod($controllerInstance, $methodName, $classAnnotations) {
        return true;
    }


    /**
     * After method interceptor for controller.  This is called after every method
     * is invoked.  Useful for logging etc.
     *
     * @param $controllerInstance
     * @param $methodName
     * @param $classAnnotations
     */
    public function afterMethod($controllerInstance, $methodName, $result, $classAnnotations) {
        return true;
    }


    public function onException($controllerInstance, $methodName, $classAnnotations) {

    }
}

?>