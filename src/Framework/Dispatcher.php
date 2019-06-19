<?php

namespace Kinikit\MVC\Framework;

use ErrorException;
use Kinikit\Core\Configuration;
use Kinikit\Core\Exception\SerialisableException;
use Kinikit\Core\Init;
use Kinikit\Core\Util\Annotation\ClassAnnotationParser;
use Kinikit\Core\Util\ArrayUtils;
use Kinikit\Core\Util\HTTP\HttpRequest;
use Kinikit\Core\Util\HTTP\HttpSession;
use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\Core\Util\Logging\Logger;
use Kinikit\MVC\Exception\ControllerNotFoundException;

/**
 * Main entry point into the MVC Framework from the outside.  Bootstrapping index.php files should instantiate one of these in order to
 * dispatch the request to an appropriate controller for handling.
 *
 * @author mark
 *
 */
class Dispatcher {

    /**
     * Main dispatch routine, which dispatches to the controller identified by the URL and evaluates the returned model and view.
     */
    public function dispatch() {

        // Call the core init to bootstrap framework.
        new Init();

        // Lookup the current url and dispatch to the controller identified.
        $currentURL = URLHelper::getCurrentURLInstance();

        // If no first segment, reset the current url to the welcome path if present.
        if (!trim($currentURL->getFirstSegment())) {
            $welcomePath = Configuration::readParameter("welcome.path");
            if ($welcomePath) {
                URLHelper::setTestURL($welcomePath);
                $currentURL = URLHelper::getCurrentURLInstance();
            }
        }


        // If we have ensure secure set, upgrade to secure.
        if (Configuration::readParameter("ensure.secure") && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")) {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }

        // Grab the first segment and dispatch the controller if found
        if ($currentURL->getSegmentCount() > 0) {

            try {
                $instance = ControllerResolver::instance()->resolveControllerForURL($currentURL->getURL());
            } catch (SerialisableException $e) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                print Controller::convertToWebServiceOutput($e);
                exit();
            }


            if ($instance) {


                $requestParameters = HttpRequest::instance()->getAllValues();

                $result = $instance->handleRequest($requestParameters);

                if ($result instanceof ModelAndView) {
                    print $result->evaluate();
                } else if ($result instanceof Redirection) {
                    $result->redirect();
                } else {
                    print $result;
                }


            } else {

                $unknownPath = Configuration::readParameter("unknown.path");
                if ($unknownPath && $unknownPath != URLHelper::getCurrentURLInstance()->getURL()) {

                    if (!headers_sent()) {
                        // Set the page not found header
                        header("HTTP/1.0 404 Not Found");
                    }

                    // Redispatch the unknown path.
                    URLHelper::setTestURL($unknownPath);
                    $this->dispatch();

                    return;
                } else {
                    throw new ControllerNotFoundException ($currentURL->getURL());
                }
            }

        }


    }


}

