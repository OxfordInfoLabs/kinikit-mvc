<?php


namespace Kinikit\MVC\RouteHandler;


use Kinikit\Core\Reflection\Method;
use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Response;
use Kinikit\MVC\Response\View;

class DecoratorRouteHandler extends ControllerRouteHandler {

    /**
     * @var RouteHandler
     */
    private $contentRouteHandler;


    /**
     * ControllerRouteHandler constructor.
     *
     * @param Method $targetDecoratorMethod
     * @param RouteHandler $contentRouteHandler
     * @param Request $request
     */
    public function __construct($targetDecoratorMethod, $contentRouteHandler, $request) {
        parent::__construct($targetDecoratorMethod, $request, "");

        // Ensure this is a web route - avoids unnecessary markup in decorators.
        $this->routeType = self::ROUTE_TYPE_WEB;

        $this->contentRouteHandler = $contentRouteHandler;
    }


    /**
     * Handle this route and return a response
     *
     * @return Response
     */
    public function handleRoute() {

        // Firstly handle the content route handler to see whether we need to proceed.
        $contentResponse = $this->contentRouteHandler->handleRoute();

        if ($contentResponse instanceof View) {

            // Process decorator
            $myResponse = parent::handleRoute();

            // If our response is a view, continue.
            if ($myResponse instanceof View) {

                $contentModel = $contentResponse->getModel();

                // Get content as string
                ob_start();
                $contentResponse->send();
                $content = ob_get_contents();
                ob_end_clean();

                // Literally poke in additional model.
                $this->injectParamsIntoViewModel($myResponse, ["contentModel" => $contentModel, "content" => $content]);

            }

            return $myResponse;


        } else {
            return $contentResponse;
        }

    }

}
