<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Exception\AccessDeniedException;

class RouteNotFoundException extends AccessDeniedException {

    public function __construct($route) {
        parent::__construct("The route $route cannot be found.");
    }

}
