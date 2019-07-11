<?php

namespace Kinikit\MVC\Controllers;

use Kinikit\MVC\Framework\Controller;

// Simple test controller 
class SimpleController extends Controller {
	
	public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
	public function defaultHandler($requestParameters) {
		SimpleController::$executed = true;
	}


}

?>
