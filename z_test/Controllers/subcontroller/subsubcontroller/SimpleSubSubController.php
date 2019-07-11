<?php

namespace Kinikit\MVC\Controllers\subcontroller\subsubcontroller;

// Simple test controller 
use Kinikit\MVC\Framework\Controller;

class SimpleSubSubController extends Controller {

    public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
    public function defaultHandler($requestParameters) {
        SimpleSubSubController::$executed = true;
    }

}

?>
