<?php

// Simple test controller 
use Kinikit\MVC\Framework\Controller;

class SimpleSubController extends Controller
{

    public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
    public function defaultHandler($requestParameters)
    {
        SimpleSubController::$executed = true;
    }

}

?>