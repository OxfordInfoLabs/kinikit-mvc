<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\MVC\Request\Request;
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
     * @param string $viewName
     * @param Request $request
     *
     * ViewOnlyRouteHandler constructor.
     * @throws ViewNotFoundException
     */
    public function __construct($viewName, $request) {
        $this->view = new View($viewName, ["request" => $request]);
        parent::__construct(null, null, self::ROUTE_TYPE_WEB);
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
