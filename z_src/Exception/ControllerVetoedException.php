<?php

namespace Kinikit\MVC\Exception;

class ControllerVetoedException extends \Exception {
	
	public function __construct($controller) {
		parent::__construct ( "Access to the controller '" . $controller . "' has been vetoed by a controller interceptor" );
	}

}

?>