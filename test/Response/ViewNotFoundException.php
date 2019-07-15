<?php


namespace Kinikit\MVC\Response;


use Throwable;

class ViewNotFoundException extends \Exception {

    public function __construct($viewPath) {
        parent::__construct("The view with path $viewPath cannot be resolved to any included search paths.");
    }


}
