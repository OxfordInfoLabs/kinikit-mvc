<?php

namespace Kinikit\MVC\Exception;

/**
 * Exception raised if no view is supplied to the model and view.
 * 
 * @author mark
 *
 */
class NoViewSuppliedException extends \Exception {
	
	public function __construct() {
		parent::__construct ( "No view was supplied to the model and view" );
	}

}

?>