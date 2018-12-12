<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception raised if too few method parameters are passed to a service method.
 *
 * @author mark
 *
 */
class TooFewControllerMethodParametersException extends \Exception {

    /**
     * Construct with required data to write an informed message
     *
     * @param string $controllerName
     * @param string $controllerMethod
     * @param integer $parametersPassed
     * @param integer $parametersRequired
     */
    public function __construct($controllerName, $controllerMethod, $parametersPassed, $parametersRequired) {
        parent::__construct("Too few parameters were passed to the service method '" . $controllerMethod . "' on the service '" . $controllerName . "'.  " . $parametersPassed . " were passed, " . $parametersRequired . " are required.");
    }

}

?>