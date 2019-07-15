<?php

namespace Kinikit\MVC\Decorators;

use Kinikit\MVC\Response\View;

class BespokeDecorator {

    /**
     * Handle request method
     */
    public function handleRequest() {
        return new View("BespokeDecorator");
    }

}


