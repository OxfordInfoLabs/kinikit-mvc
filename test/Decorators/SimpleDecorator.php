<?php

// Simple test controller 
use Kinikit\MVC\Framework\Controller;

class SimpleDecorator extends Controller {

    public static $executed = false;

    /**
     * @param $requestParameters
     * @return void
     */
    public function defaultHandler($requestParameters) {
        SimpleDecorator::$executed = true;
    }

}

?>