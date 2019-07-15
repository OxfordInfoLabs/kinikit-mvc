<?php


namespace Kinikit\MVC\Routing;


use Kinikit\MVC\Response\Response;
use Kinikit\MVC\Response\View;
use Kinikit\MVC\Response\ViewNotFoundException;

class ViewOnlyRouteHandler extends RouteHandler {


    /**
     * @var View
     */
    private $view;

    /**
     * Construct with a view path to draw.
     *
     * ViewOnlyRouteHandler constructor.
     * @throws ViewNotFoundException
     */
    public function __construct($viewName, $model = []) {
        $this->view = new View($viewName, $model);
        parent::__construct(null, null);
    }

    /**
     * For view only routes we simply echo the view with no further processing.
     *
     * @return Response
     */
    public function handleRoute() {
        return $this->view;
    }
}
