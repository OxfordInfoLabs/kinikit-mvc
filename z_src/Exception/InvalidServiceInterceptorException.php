<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception raised if an invalid interceptor is added to the collection of interceptors within the interceptor evaluator.
 * 
 * @author matthew
 *
 */
class InvalidServiceInterceptorException extends \Exception {
	
	public function __construct($className) {
		parent::__construct ( "An attempt was made to add a service interceptor of class '" . $className . "' which does not extend ServiceInterceptor" );
	}

}

?>