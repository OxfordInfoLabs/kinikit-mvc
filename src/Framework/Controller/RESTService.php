<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 15/10/2018
 * Time: 10:49
 */

namespace Kinikit\MVC\Framework\Controller;


use Kinikit\Core\Exception\NoneExistentMethodException;
use Kinikit\Core\Exception\SerialisableException;
use Kinikit\Core\Util\Annotation\ClassAnnotationParser;
use Kinikit\Core\Util\HTTP\HttpRemoteRequest;
use Kinikit\Core\Util\HTTP\HttpRequest;
use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\Core\Util\Logging\Logger;
use Kinikit\MVC\Exception\ControllerMethodNotFoundException;

class RESTService extends WebService {


    /**
     * Handle request method for rest service.  This essentially inspects the incoming request and
     * looks for methods which match by annotation.
     *
     * @param $requestParameters
     * @return \Kinikit\MVC\Framework\ModelAndView|void
     */
    public function handleRequest($requestParameters) {

        $className = get_class($this);
        $explodedClass = explode("\\", $className);
        $shortClassName = array_pop($explodedClass);

        // Inspect the request method.  Look for Override for Java or native request method.
        $requestMethod = isset($_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"]) ? $_SERVER["HTTP_X_HTTP_METHOD_OVERRIDE"] : $_SERVER["REQUEST_METHOD"];

        if ($requestMethod == "HEAD") {
            $requestMethod = "GET";
            $requestParameters["HEADERS_ONLY"] = 1;
        }

        // Check the URL fragments going backwards
        $urlHelper = URLHelper::getCurrentURLInstance();


        // Grab the HTTP annotation for the matching tag
        $annotations = ClassAnnotationParser::instance()->parse($className);
        $httpAnnotations = $annotations->getMethodAnnotationsForMatchingTag("http");

        $methodFound = null;
        $foundSegmentParams = null;
        foreach ($httpAnnotations as $methodName => $annotation) {

            $segmentParams = array();

            $value = $annotation[0]->getValue();

            if (is_numeric(strpos($value, $requestMethod))) {

                preg_match("/\/\S+/", $value, $matches);

                if (sizeof($matches) > 0) {


                    preg_match_all("/\\$[0-9a-zA-Z_]+/", $matches[0], $segmentVars);
                    $segmentVars = $segmentVars[0];

                    $newMatchString = $shortClassName . $matches[0];
                    foreach ($segmentVars as $segmentVar) {
                        $newMatchString = str_replace($segmentVar, "([^/]+)?", $newMatchString);
                    }

                    $newMatchString = "/" . str_replace("/", "\\/", $newMatchString) . "$/";

                    preg_match($newMatchString, rtrim($urlHelper->getRequestPath(), "/"), $urlMatches);


                    if (sizeof($urlMatches) > 0) {
                        $matches = true;
                        $segmentParams = array();
                        if (sizeof($urlMatches) == sizeof($segmentVars) + 1) {
                            for ($i = 1; $i < sizeof($urlMatches); $i++) {
                                if (strpos($urlMatches[$i], "/")) {
                                    $matches = false;
                                }
                                $segmentParams[ltrim($segmentVars[$i - 1], "$")] = $urlMatches[$i];
                            }
                        }

                        if ($matches) {


                            if ($foundSegmentParams === null || sizeof($foundSegmentParams) > sizeof($segmentVars)) {
                                $methodFound = $methodName;
                                $foundSegmentParams = $segmentParams;
                            }
                        }

                    }


                } else {

                    $newMatchString = "/" . $shortClassName . "\\/*$/";

                    preg_match($newMatchString, $urlHelper->getRequestPath(), $urlMatch);

                    if (sizeof($urlMatch) > 0) {
                        $methodFound = $methodName;
                        $foundSegmentParams = array();
                    }


                }

            }
        }


        if ($methodFound) {

            $requestParameters = array_merge($requestParameters, $foundSegmentParams);

            if (isset($requestParameters["payload"])) {
                $reflectionClass = new \ReflectionClass($className);
                $method = $reflectionClass->getMethod($methodFound);
                $params = $method->getParameters();

                if ($requestParameters["payload"]) {
                    if (sizeof($params) > sizeof($foundSegmentParams)) {
                        $requestParameters[$params[sizeof($foundSegmentParams)]->getName()] = $requestParameters["payload"];
                    }
                }


            }


            // New URL
            $newURL = "/" . $className . "/" . $methodFound;
            URLHelper::setTestURL($newURL);

            return parent::handleRequest($requestParameters);


        } else {

            $exception = new ControllerMethodNotFoundException($className, $requestMethod . " " . $urlHelper->getRequestPath());


            header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
            return $this->convertToWebServiceOutput($exception);
        }


    }


}
