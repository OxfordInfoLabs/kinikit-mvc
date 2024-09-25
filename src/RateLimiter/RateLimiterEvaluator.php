<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:01
 */

namespace Kinikit\MVC\RateLimiter;


use Kinikit\MVC\Request\Request;
use Kinikit\MVC\Response\Headers;

/**
 * Evaluate rate limits if a rate limiter has been configured on a controller.
 *
 * @noProxy
 *
 * Class RateLimiterEvaluator
 * @package Kinikit\MVC\Framework\RateLimiter
 */
class RateLimiterEvaluator {

    /**
     * @var RateLimiter
     */
    private $rateLimiter;

    /**
     * @var Request
     */
    private $request;


    /**
     * @var Headers
     */
    private $responseHeaders;

    /**
     * RateLimiterEvaluator constructor.
     *
     * @param RateLimiter $rateLimiter
     * @param Request $request
     * @param Headers $responseHeaders
     */
    public function __construct($rateLimiter, $request, $responseHeaders) {
        $this->rateLimiter = $rateLimiter;
        $this->request = $request;
        $this->responseHeaders = $responseHeaders;
    }


    /**
     * Evaluate a rate limiter for the current request using the supplied config
     *
     * @param RateLimiterConfig $rateLimiterConfig
     * @throws RateLimitExceededException
     */
    public function evaluateRateLimiter($rateLimiterConfig) {


        // Grab the window size and default rate
        $windowSizeInSeconds = $this->rateLimiter->getTimeWindowInMinutes() * 60;

        // Work out the start of the window.
        $startOfDay = date_create_from_format("d/m/Y H:i:s", date("d/m/Y 00:00:00"))->format("U");
        $secondsSinceStart = time() - $startOfDay;
        $windowStart = time() - ($secondsSinceStart % $windowSizeInSeconds);

        // Derive the appropriate rate limit depending upon how specific this has been defined.
        $defaultRateLimit = $rateLimit = $this->rateLimiter->getDefaultRateLimit();

        if ($rateLimiterConfig->getRateLimit()) {
            $rateLimit = $rateLimiterConfig->getRateLimit();
        } else if ($rateLimiterConfig->getRateLimitMultiplier()) {
            $rateLimit = $defaultRateLimit * $rateLimiterConfig->getRateLimitMultiplier();
        }

        // Now get the number of requests from the current IP address
        $sourceIp = $this->request->getRemoteIPAddress();
        $numberOfRequests = $this->rateLimiter->getNumberOfRequestsInWindow($windowStart, $sourceIp);

        // Set headers
        if (!headers_sent()) {
            $this->responseHeaders->set(Headers::HEADER_RATELIMIT_LIMIT, $rateLimit);
            $this->responseHeaders->set(Headers::HEADER_RATELIMIT_REMAINING, max($rateLimit - $numberOfRequests, 0));
            $this->responseHeaders->set(Headers::HEADER_RATELIMIT_RESET, ($windowStart + $windowSizeInSeconds));
        }

        if ($numberOfRequests > $rateLimit) {
            throw new RateLimitExceededException($sourceIp, $rateLimit, $windowStart + $windowSizeInSeconds);
        }


    }


}
