<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 19/10/2018
 * Time: 12:39
 */

namespace Kinikit\MVC\Controllers;


use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\MVC\Framework\API\APIConfiguration;
use Kinikit\MVC\Framework\API\Descriptor\APIInfo;
use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\ModelAndView;

class apidoc extends Controller {


    public function defaultHandler($format = "HTML") {

        $model = array("baseURL" => (isset($_SERVER["HTTPS"]) ? "https://" : "http://") . $_SERVER["HTTP_HOST"]);

        $urlHelper = URLHelper::getCurrentURLInstance();

        $segments = $urlHelper->getAllSegments();
        array_shift($segments);

        $apis = APIConfiguration::getAPIConfigs();

        $api = null;

        // Handle the default case first
        if (sizeof($segments) == 0) {
            if (sizeof($apis) == 1) {
                $api = array_values($apis)[0];
            } else {
                $model["apis"] = $apis;
            }
        } else {
            if (isset($apis[$segments[0]])) {
                $api = $apis[$segments[0]];
            } else {
                throw new \Exception("No API has been found with key " . $segments[0]);
            }
        }


        if ($api) {
            $apiInfo = new APIInfo($api);
            $metaData = $apiInfo->getMetaData();

            // If we are in a listing mode, lookup the appropriate object
            if (sizeof($segments) > 2) {
                $remainingPath = $urlHelper->getPartialURLFromSegment(3);
                if ($segments[1] == "api") {

                    // Matching controller
                    $controller = $apiInfo->getAPIControllerByPath($remainingPath);
                    if (!$controller) {
                        $explodedPath = explode("/", $remainingPath);
                        $methodName = array_pop($explodedPath);
                        $controller = $apiInfo->getAPIControllerByPath(join("/", $explodedPath));

                        if ($controller) {
                            $method = $controller->getMethod($methodName);
                            if ($method) {

                                $method->setFullRequestPath("/" . $controller->getRequestPath() . ($method->getRequestPath() ? "/" . $method->getRequestPath() : ""));
                                $method->setHasParams(sizeof($method->getParams()) > 0 || sizeof($metaData->getGlobalParameters()) > 0);
                                $model["method"] = $method;

                            }
                        }
                    }

                    if ($controller) {

                        $model["controller"] = $controller;

                        // Set the method indicator for rendering
                        foreach ($controller->getMethods() as $method) {
                            $method->setHttpMethodIndicator(array($method->getHttpMethod() => 1));
                            $method->setFullRequestPath("/" . $controller->getRequestPath() . ($method->getRequestPath() ? "/" . $method->getRequestPath() : ""));

                        }

                        foreach ($metaData->getControllerSummaries() as $controllerSummary) {
                            if ($controllerSummary->getPath() == $controller->getPath()) {
                                $controllerSummary->setActive(true);
                            }
                        }
                    }

                } else if ($segments[1] == "object") {
                    $model["object"] = $apiInfo->getAPIObjectByPath($remainingPath);
                } else if ($segments[1] == "exception"){
                    $model["exception"] = $apiInfo->getAPIExceptionByPath($remainingPath);
                }
            }


            $model["apiMetaData"] = $metaData;
        }


        return $format == "json" ? $model : new ModelAndView("apidoc", $model);

    }


}