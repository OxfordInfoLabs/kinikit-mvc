<?php

namespace Kinikit\MVC\Controllers;

use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\ModelAndView;

/**
 * Built in controller useful if no logic is required except to present a view.
 * The view name should be supplied as the
 * second url fragment which will simply be returned in the model and view.
 *
 * @author mark
 *
 */
class view extends Controller {

    /**
     * Simply pull out the second fragment and return
     *
     * @param $requestParameters
     * @return ModelAndView
     * @throws \Kinikit\MVC\Exception\NoViewSuppliedException
     */
    public function defaultHandler($requestParameters) {

        $url = URLHelper::getCurrentURLInstance();

        // Work out the view name going backwards
        $viewName = "";
        for ($i = $url->getSegmentCount() - 1; $i >= 0; $i--) {
            $segment = $url->getSegment($i);

            if ($segment == "View") {
                break;
            }

            $viewName = "/" . $segment . $viewName;
        }


        return new ModelAndView (substr($viewName, 1));
    }

}

?>