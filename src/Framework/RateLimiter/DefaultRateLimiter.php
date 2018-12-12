<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 14:02
 */

namespace Kinikit\MVC\Framework\RateLimiter;


use Kinikit\Core\Configuration;
use Kinikit\Core\Util\Logging\Logger;

class DefaultRateLimiter implements RateLimiter {

    /**
     * Get the time window in minutes for which rate is being measured.  This looks for a configuration parameter
     * or uses 1 as a default
     *
     * @return integer
     */
    public function getTimeWindowInMinutes() {
        return Configuration::readParameter("ratelimiter.timewindow") ? Configuration::readParameter("ratelimiter.timewindow") : 1;
    }

    /**
     * Return the default rate limit (in requests per time window).  This is used if no multipliers are supplied
     * at the controller or method level.  This looks for a configuration parameter or uses 60 as default
     *
     * @return integer
     */
    public function getDefaultRateLimit() {
        return Configuration::readParameter("ratelimiter.defaultratelimit") ? Configuration::readParameter("ratelimiter.defaultratelimit") : 60;
    }

    /**
     * Get the number of requests within the current window for a source IP Address, controller and method.
     *
     * @return integer
     */
    public function getNumberOfRequestsInWindow($windowStartTime, $sourceIPAddress, $controller, $method) {

        $handle = @fopen(Configuration::readParameter("ratelimiter.storedir") . "/" . $sourceIPAddress, "a+");

        if (!$handle) {
            mkdir(Configuration::readParameter("ratelimiter.storedir"));
            $handle = @fopen(Configuration::readParameter("ratelimiter.storedir") . "/" . $sourceIPAddress, "a+");
        }
        if ($handle) {
            fseek($handle, 0);
            $storedWindow = fgets($handle, 11);
            if ($storedWindow != $windowStartTime) {
                ftruncate($handle, 0);
                fputs($handle, $windowStartTime);

            }

            fputs($handle, "H");
            fseek($handle, 0, SEEK_END);

            return ftell($handle) - 10;

        } else {
            Logger::log("Cannot write rate limiter data");
        }

    }
}