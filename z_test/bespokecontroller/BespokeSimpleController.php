<?php

namespace Kinikit\MVC\bespokecontroller;

// Simple test controller 
use Kinikit\MVC\Framework\Controller;

class BespokeSimpleController extends Controller {
	
	public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
	public function defaultHandler($requestParameters) {
		BespokeSimpleController::$executed = true;
	}

}

?>
