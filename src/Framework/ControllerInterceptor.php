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
     * @param Controller $controllerInstance - The controller being called
     * @param string $methodName - The method name
     * @param array $params - The parameters passed to the method
     * @param ClassAnnotations $classAnnotations - The class annotations for the controller class for convenience.
     *
     * @return boolean
     */
    public function beforeMethod($controllerInstance, $methodName, $params, $classAnnotations) {
        return true;
    }


    /**
     * After method interceptor for controller.  This is called after every method
     * is invoked.  Useful for logging etc or final checking based upon results of method.
     *
     * This should return true if the returnValue is to be returned or false otherwise.
     *
     * @param $controllerInstance The controller instance being called
     * @param $methodName - The method name being called.
     * @param $params - The input params to the method
     * @param $result - The return value from the method
     * @param $classAnnotations - The class annotations for the controller class for convenience.
     *
     * @return boolean
     */
    public function afterMethod($controllerInstance, $methodName, $params, $returnValue, $classAnnotations) {
        return true;
    }


    /**
     * Exception interceptor.  This is called when an exception is raised in a controller method.
     * Useful for logging etc.
     *
     * This doesn't return a value but throws the exception back to the client after completion.
     *
     * @param $controllerInstance - The controller instance being called.
     * @param $methodName - The method being called
     * @param $params - The input params to the method.
     * @param $exception - The exception object thrown
     * @param $classAnnotations - The class annotations for the controller class for convenience.
     */
    public function onException($controllerInstance, $methodName, $params, $exception, $classAnnotations) {

    }
}

?>