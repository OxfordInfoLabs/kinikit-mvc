<?php


namespace Kinikit\MVC\Routing;


class MissingDecoratorHandlerException extends \Exception {

    public function __construct($decorator) {
        parent::__construct("The decorator $decorator must have a handleRequest method.");
    }

}
