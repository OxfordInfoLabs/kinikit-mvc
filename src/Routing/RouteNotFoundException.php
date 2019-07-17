<?php


namespace Kinikit\MVC\Routing;


use Kinikit\Core\Exception\ItemNotFoundException;

class RouteNotFoundException extends ItemNotFoundException {

    public function __construct($route) {
        parent::__construct("The route $route cannot be found.");
    }

}
