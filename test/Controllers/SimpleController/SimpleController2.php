<?php

// Simple test controller 
use Kinikit\MVC\Framework\Controller;

class SimpleController2 extends Controller
{

    public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
    public function defaultHandler($requestParameters)
    {
        SimpleController2::$executed = true;
    }


}