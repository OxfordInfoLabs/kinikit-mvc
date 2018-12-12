<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception thrown if controller attempted to be accessed but cannot be found in the controllers directory.
 *
 * @author mark
 *
 */
class ControllerNotFoundException extends \Exception {

    public function __construct($controllerName) {
        parent::__construct("The controller for path '" . $controllerName . " accessed does not exist.");
    }

}

?>