<?php


namespace Kinikit\MVC\Decorators;


use Kinikit\MVC\Response\View;

class Zone {

    /**
     * Handle request method
     */
    public function handleRequest() {
        return new View("Zone", ["menu" => "standard"]);
    }
}
