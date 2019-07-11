<?php

namespace Kinikit\MVC\Framework\Controller;

use Kinikit\Core\Util\ArrayUtils;
use Kinikit\Core\Util\HTTP\HttpRequest;
use Kinikit\Core\Util\HTTP\HttpSession;
use Kinikit\MVC\Exception\ControllerNotFoundException;
use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\ControllerResolver;
use Kinikit\MVC\Framework\HTTP\URLHelper;
use Kinikit\MVC\Framework\ModelAndView;
use Kinikit\MVC\Framework\Redirection;

/**
 * Special abstract controller which supports decoration of content.   The second URL fragment is another controller to use as the content
 * to be decorated which is merged into the main model for the decorator and the view contents are merged in as well using the magic
 * placeholder ##content
 *
 * @author mark
 *
 */
abstract class Decorator extends Controller {

    /**
     * Handle the request for the decorator.
     *
     * @param HttpRequest $request
     * @return ModelAndView
     * @throws ControllerNotFoundException
     * @throws \Kinikit\MVC\Exception\ControllerVetoedException
     * @throws \Kinikit\MVC\Exception\NoControllerSuppliedException
     * @throws \Kinikit\MVC\Exception\NoViewSuppliedException
     * @throws \Kinikit\MVC\Exception\ViewNotFoundException
     */
    public function defaultHandler($request) {

        $currentURLHelper = URLHelper::getCurrentURLInstance();

        // Now grab the decorator model and view
        $decoratorModelAndView = $this->handleDecoratorRequest();

        // Check for a content controller fragment.
        if ($decoratorModelAndView && ($currentURLHelper->getSegmentCount() > 1)) {

            $segments = $currentURLHelper->getAllSegments();
            array_shift($segments);

            if (sizeof($segments) > 1)
                array_pop($segments);


            // Resolve a content controller
            $contentController = ControllerResolver::instance()->resolveControllerForURL(join("/", $segments));


            // if no content controller, try with the full URL just in case the Decorator folder convention is being used
            if (!$contentController)
                $contentController = ControllerResolver::instance()->resolveControllerForURL($currentURLHelper->getURL(), 1);

            if (!$contentController) {
                throw new ControllerNotFoundException (join("/", $segments));
            }


            // Pass the decorator model and view to the content controller for convenience.
            $contentModelAndView = $contentController->handleRequest($request);


            // Merge both models accordingly and evaluate the view
            if ($contentModelAndView instanceof Redirection) {
                return $contentModelAndView;
            } else {

                if ($contentModelAndView) {

                    // Merge into the model all request and session parameters suitably prefixed.
                    $contentModelAndView->injectAdditionalModel(array("request" => HttpRequest::instance()->getAllParameters()));
                    $contentModelAndView->injectAdditionalModel(array("session" => HttpSession::instance()->getAllValues()));

                    // Merge both models accordingly
                    $contentModelAndView->injectAdditionalModel($decoratorModelAndView->getModel());

                    // Get the content
                    $content = $contentModelAndView->evaluate();
                    $contentModel = $contentModelAndView->getModel();

                } else {
                    $content = "";
                    $contentModel = array();
                }

                $additionalModel = array_merge($contentModel, array("content" => $content));

                // Add the special content model to the decorator model and view.
                $decoratorModelAndView->injectAdditionalModel($additionalModel);
            }
        }

        // Return the decorator model and view.
        return $decoratorModelAndView;
    }

    /**
     * Handle the decorator request.  This is called after the handleRequest has been called on the
     * content controller.
     *
     * @return ModelAndView
     */
    public abstract function handleDecoratorRequest();

}
