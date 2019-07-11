<?php
/**
 * Created by PhpStorm.
 * User: nathanalan
 * Date: 14/09/2018
 * Time: 16:21
 */

namespace Kinikit\MVC\Exception;


use Kinikit\Core\Exception\SerialisableException;

class ControllerMethodNotFoundException extends SerialisableException {

    public function __construct($controllerName, $methodName) {
        parent::__construct ( "An attempt was made to access the method '" . $methodName . "' on the Controller '" . $controllerName . "'.  Whilst the Controller does exist, the method does not exist" );
    }

}