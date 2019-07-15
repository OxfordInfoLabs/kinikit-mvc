<?php

namespace Kinikit\MVC\Framework;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Util\Annotation\ClassAnnotationParser;

use Kinikit\Core\Configuration;
use Kinikit\Core\Exception\SerialisableException;

use Kinikit\Core\Util\ObjectArrayUtils;
use Kinikit\Core\Util\Serialisation\JSON\ObjectToJSONConverter;
use Kinikit\Core\Util\Serialisation\XML\ObjectToXMLConverter;
use Kinikit\MVC\Exception\AccessDeniedException;

use Kinikit\MVC\Exception\RateLimitExceededException;
use Kinikit\MVC\Framework\Caching\CacheEvaluator;
use Kinikit\MVC\Framework\Controller\WebService;
use Kinikit\MVC\Exception\TooFewControllerMethodParametersException;
use Kinikit\MVC\Framework\HTTP\HttpRequest;
use Kinikit\MVC\Framework\HTTP\HttpSession;
use Kinikit\MVC\Framework\HTTP\URLHelper;
use Kinikit\MVC\Framework\RateLimiter\RateLimiterEvaluator;

/**
 * MVC Controller interface.  This is an extremely simple interface which implements the single method handleRequest
 * The MVC dispatcher will call this method to invoke the controller by naming convention.
 *
 * @author mark
 *
 */
abstract class Controller {

    /**
     * Handle request, called by the MVC dispatcher to execute the controller.
     * Should return a suitable model and view object.
     *
     * @param HttpRequest $request
     * @return ModelAndView
     * @throws TooFewControllerMethodParametersException
     * @throws \Kinikit\Core\Exception\ClassNotSerialisableException
     * @throws \ReflectionException
     */
    public function handleRequest($request) {

        // Grab current url
        $currentURLHelper = URLHelper::getCurrentURLInstance();

        $segments = $currentURLHelper->getAllSegments();
        $methodName = array_pop($segments);
        $className = get_class($this);

        $annotations = ClassAnnotationParser::instance()->parse($className);

        $acceptHeader = isset($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : null;
        $isJSON = is_numeric(strpos($acceptHeader, "application/json"));
        $isXML = is_numeric(strpos($acceptHeader, "application/xml")) && !is_numeric(strpos($acceptHeader, "text/html"));
        $isWebService = ($this instanceof WebService) || $isJSON || $isXML || $annotations->getMethodAnnotationsForMatchingTag("webservice", $methodName);

        // Initialise result
        $result = null;

        try {


            // Now inspect the class for the method being accessed
            $reflectionClass = new \ReflectionClass ($className);

            // Throw if no method found on the service.
            if (!$reflectionClass->hasMethod($methodName)) {
                $methodName = "defaultHandler";
            }
            $method = $reflectionClass->getMethod($methodName);

            $requestParameters = $request->getAllParameters();

            $functionParams = $requestParameters;
            $functionParams["request"] = $request;


            // Get the supplied function parameters in either possible format
            // supplied.
            $suppliedParams = $this->getSuppliedFunctionParameters($functionParams, $method, $annotations);

            // Now find out how many are required
            $paramsSupplied = sizeof($suppliedParams);
            $paramsRequired = $method->getNumberOfRequiredParameters();

            if ($paramsSupplied < $paramsRequired) {
                throw new TooFewControllerMethodParametersException ($className, $methodName, $paramsSupplied, $paramsRequired);
            }


            // Evaluate rate limiters for the passed controller.
            RateLimiterEvaluator::instance()->evaluateRateLimitersForControllerMethod($this, $methodName, $annotations);

            // Attempt to get a value from the cache.
            $cacheEvaluator = new CacheEvaluator();
            $result = $cacheEvaluator->getCachedResult($this, $methodName, $suppliedParams, $annotations);

            // If no result from the cache, make a real call.
            if (!$result) {


                // Call the function in question - ensuring we invoke proxy interceptor.
                $proxyInstance = Container::instance()->get(get_class($this));
                $result = call_user_func_array(array($proxyInstance, $methodName), $suppliedParams);
            }


            // Attempt to cache the result value
            $cacheEvaluator->cacheResult($this, $methodName, $suppliedParams, $annotations);


        } catch (RateLimitExceededException $e) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 429 Rate Limit Exceeded', true, 429);
            $result = $e;
        } catch (\Exception $e) {


            if ($isWebService) {
                header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
                if ($e instanceof SerialisableException) {
                    $result = $e;
                } else {
                    $result = new SerialisableException(null, null, $e);
                }

            } else {

                $params = array("referrer" => URLHelper::getCurrentURLInstance()->getURL(), "error" => $e->getMessage());

                if ($e instanceof AccessDeniedException && Configuration::instance()->getParameter("access.denied.path")) {
                    $result = new Redirection(Configuration::instance()->getParameter("access.denied.path"), $params);
                } else if (Configuration::instance()->getParameter("error.path")) {
                    $result = new Redirection(Configuration::instance()->getParameter("error.path"), $params);
                } else {
                    throw $e;
                }
            }
        }


        if ($result instanceof Redirection) {
            return $result;
        } else if ($result instanceof ModelAndView) {

            // If no content type specified yet, set the default one.
            if (!headers_sent() && !preg_grep("/Content-Type/", headers_list())) {
                header("Content-Type: text/html");
            }

            // Merge into the model all request and session parameters suitably prefixed.
            $result->injectAdditionalModel(array("request" => $requestParameters));
            $result->injectAdditionalModel(array("session" => HttpSession::instance()->getAllValues()));

            // Evaluate the model and view.
            return $result;
        } else {

            return self::convertToWebServiceOutput($result);
        }

    }


    /**
     * Default handler
     *
     * @param HttpRequest $request
     * @return mixed
     */
    public abstract function defaultHandler($request);


    /**
     * Get the function parameters by hook or by crook.
     * We allow parameters to be either supplied with keys in the format param1,
     * param2, param3.....paramN which represent the params in that order.
     * Alternatively, parameters can be supplied with keys that actually match
     * the names of the function parameters using reflection. This is great for
     * Constrained JSON calls etc.
     */
    private function getSuppliedFunctionParameters($parameterArray, $method, $classAnnotations) {

        // Check that we have any parameters to sort first of all
        if (sizeof($parameterArray) > 0) {

            $methodAnnotations = $classAnnotations->getMethodAnnotationsForMatchingTag("param", $method->getName());


            $nameBasedParams = array();
            foreach ($method->getParameters() as $methodParam) {
                $key = $methodParam->getName();

                // Deal with parameters which have been declared as object class types and don't exist
                foreach ($methodAnnotations as $annotation) {
                    if (is_numeric(strpos($annotation->getValue(), "$" . $key))) {
                        $paramClass = trim(str_replace("$" . $key, "", $annotation->getValue()));
                        $paramClass = explode(" ", $paramClass);
                        $paramClass = $paramClass[0];

                        if ($paramClass && isset($parameterArray[$key])) {
                            $parameterArray[$key] = ObjectArrayUtils::convertArrayToSerialisableObjects($parameterArray[$key], $paramClass);
                        }
                    }
                }

                if (array_key_exists($key, $parameterArray)) {
                    $paramValue = $parameterArray [$key];
                    $nameBasedParams [$methodParam->getPosition()] = $paramValue === null ? $methodParam->getDefaultValue() : $paramValue;
                } else {
                    $nameBasedParams [$methodParam->getPosition()] = null;
                }
            }
            return $nameBasedParams;


        } else {
            return array();
        }

    }

    /**
     * @param $isXML
     * @param $result
     * @return false|null|string
     * @throws \Kinikit\Core\Exception\ClassNotSerialisableException
     */
    public static function convertToWebServiceOutput($result) {

        $acceptHeader = isset($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : null;
        $isXML = is_numeric(strpos($acceptHeader, "application/xml")) && !is_numeric(strpos($acceptHeader, "text/html"));

        if ($isXML) {
            $contentHeader = $_SERVER["CONTENT_TYPE"];
            $converter = new ObjectToXMLConverter();
        } else {
            $contentHeader = "text/javascript";
            $converter = new ObjectToJSONConverter();
        }


        // Look for JSONP callback.
        if (isset ($_REQUEST ["callback"])) {
            $JSONPCallback = $_REQUEST ["callback"];
            unset ($_REQUEST ["callback"]);
        } else {
            $JSONPCallback = null;
        }

        $accessControlOrigin = Configuration::readParameter("access.control.origin");
        if (!$accessControlOrigin) {
            $accessControlOrigin = "*";
        } else {
            if ($accessControlOrigin == "REFERRER") {
                $HTTP_REFERER = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "http://localhost";
                $splitProtocol = explode("//", $HTTP_REFERER);
                $splitReferer = explode("/", $splitProtocol[1]);

                header('Access-Control-Allow-Credentials: true');
            }
            $accessControlOrigin = $splitProtocol[0] . "//" . $splitReferer[0];
        }


        if (!headers_sent())
            header('Access-Control-Allow-Origin: ' . $accessControlOrigin);

        if ($result !== null) {
            if ($result instanceof SerialisableException) {
                $serialisableData = $result->returnWebServiceSerialisableData();
                $result = $converter->convert($serialisableData, true);
            } else {
                $result = $converter->convert($result);
            }
        }

        // Set the content type header.
        if (!headers_sent())
            header("Content-Type: {$contentHeader}; charset=utf8");

        // Handle JSONP case separately.
        if ($JSONPCallback) {
            return $JSONPCallback . '(' . $result . ');';
        } else if ($result !== null) {
            return $result;
        }
    }
}

?>