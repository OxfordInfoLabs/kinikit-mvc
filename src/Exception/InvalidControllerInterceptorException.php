<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception raised if an invalid interceptor is added to the collection of interceptors within the interceptor evaluator.
 * 
 * @author mark
 *
 */
class InvalidControllerInterceptorException extends \Exception {
	
	public function __construct($className) {
		parent::__construct ( "An attempt was made to add a controller interceptor of class '" . $className . "' which does not extend ControllerInterceptor" );
	}

}

?>