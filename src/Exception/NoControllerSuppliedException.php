<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception raised if an attempt is made to dispatch without a controller name available.
 * 
 * @author mark
 *
 */
class NoControllerSuppliedException extends \Exception {
	
	public function __construct($message = null) {
		$message = $message ? $message : "No controller was supplied for dispatch";
		parent::__construct ( $message );
	}
}

?>