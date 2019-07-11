<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:01
 */

namespace Kinikit\MVC\RateLimiter;


use Kinikit\Core\Annotation\ClassAnnotationParser;
use Kinikit\Core\Annotation\ClassAnnotations;

/**
 * Evaluate rate limits if a rate limiter has been configured on a controller.
 *
 * @noProxy
 *
 * Class RateLimiterEvaluator
 * @package Kinikit\MVC\Framework\RateLimiter
 */
class RateLimiterEvaluator {

    private $classAnnotationParser;

    /**
     * RateLimiterEvaluator constructor.
     *
     * @param ClassAnnotationParser $classAnnotationParser
     */
    public function __construct($classAnnotationParser) {
        $this->classAnnotationParser = $classAnnotationParser;
    }

    public function getRateLimitsForControllerMethod($controllerName, $methodName, $annotations = null) {

        if (!$annotations) {
            $annotations = $this->classAnnotationParser->parse($controllerName);
        }

        // Look for an annotation based rate limiter
        $classRateLimiters = $annotations->getClassAnnotationForMatchingTag("ratelimiter");

        if ($classRateLimiters) {

            $rateLimit = null;
            $rateLimitMultiplier = 1;

            if ($annotations->getClassAnnotationForMatchingTag("ratelimit")) {
                $rateLimit = $annotations->getClassAnnotationForMatchingTag("ratelimit")->getValue();
                $rateLimitMultiplier = null;
            } else if ($annotations->getClassAnnotationForMatchingTag("ratelimitmultiplier")) {
                $rateLimitMultiplier = $annotations->getClassAnnotationForMatchingTag("ratelimitmultiplier")->getValue();
            }

            if ($annotations->getMethodAnnotationsForMatchingTag("ratelimit", $methodName)) {
                $rateLimit = $annotations->getMethodAnnotationsForMatchingTag("ratelimit", $methodName)[0]->getValue();
                $rateLimitMultiplier = null;
            } else if ($annotations->getMethodAnnotationsForMatchingTag("ratelimitmultiplier", $methodName)) {
                $rateLimitMultiplier = $annotations->getMethodAnnotationsForMatchingTag("ratelimitmultiplier", $methodName)[0]->getValue();
            }

            $rateLimiterClass = $classRateLimiters->getValue();
            $rateLimiter = new $rateLimiterClass();

            return array($rateLimit, $rateLimitMultiplier, $rateLimiter->getTimeWindowInMinutes());

        } else {
            return null;
        }

    }


    /**
     * Evaluate any rate limits for the controller and method if one is defined.
     *
     * @param Controller $controllerInstance
     * @param string $methodName
     * @param ClassAnnotations $annotations
     * @return bool
     */
    public function evaluateRateLimitersForControllerMethod($controllerInstance, $methodName, $annotations = null) {

        $controllerClass = get_class($controllerInstance);

        if (!$annotations) {
            $annotations = $this->classAnnotationParser->parse($controllerClass);
        }

        // Look for an annotation based rate limiter
        $classRateLimiters = $annotations->getClassAnnotationForMatchingTag("ratelimiter");

        if ($classRateLimiters) {
            $rateLimiterClass = $classRateLimiters->getValue();

            // Create the rate limiter.
            $rateLimiter = new $rateLimiterClass();

            // Grab the window size and default rate
            $windowSizeInSeconds = $rateLimiter->getTimeWindowInMinutes() * 60;

            // Work out the start of the window.
            $startOfDay = date_create_from_format("d/m/Y H:i:s", date("d/m/Y 00:00:00"))->format("U");
            $secondsSinceStart = time() - $startOfDay;
            $windowStart = time() - ($secondsSinceStart % $windowSizeInSeconds);

            // Derive the appropriate rate limit depending upon how specific this has been defined.
            $defaultRateLimit = $rateLimit = $rateLimiter->getDefaultRateLimit();

            if ($annotations->getClassAnnotationForMatchingTag("ratelimit")) {
                $rateLimit = $annotations->getClassAnnotationForMatchingTag("ratelimit")->getValue();
            } else if ($annotations->getClassAnnotationForMatchingTag("ratelimitmultiplier")) {
                $rateLimit = $defaultRateLimit * $annotations->getClassAnnotationForMatchingTag("ratelimitmultiplier")->getValue();
            }

            if ($annotations->getMethodAnnotationsForMatchingTag("ratelimit", $methodName)) {
                $rateLimit = $annotations->getMethodAnnotationsForMatchingTag("ratelimit", $methodName)[0]->getValue();
            } else if ($annotations->getMethodAnnotationsForMatchingTag("ratelimitmultiplier", $methodName)) {
                $rateLimit = $defaultRateLimit * $annotations->getMethodAnnotationsForMatchingTag("ratelimitmultiplier", $methodName)[0]->getValue();
            }

            // Now get the number of requests from the current IP address since
            $sourceIp = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : "");

            // Get current rate
            $numberOfRequests = $rateLimiter->getNumberOfRequestsInWindow($windowStart, $sourceIp, $controllerClass, $methodName);

            // Set headers
            if (!headers_sent()) {
                header("X-RateLimit-Limit: $rateLimit");
                header("X-RateLimit-Remaining: " . max($rateLimit - $numberOfRequests, 0));
                header("X-RateLimit-Reset: " . ($windowStart + $windowSizeInSeconds));
            }

            if ($numberOfRequests > $rateLimit) {
                throw new RateLimitExceededException($sourceIp, $rateLimit, $windowStart + $windowSizeInSeconds);
            }


        }


    }


}
