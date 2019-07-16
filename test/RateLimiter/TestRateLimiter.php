<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:08
 */

namespace Kinikit\MVC\RateLimiter;


class TestRateLimiter implements RateLimiter {

    public static $timeWindow = 1;
    public static $defaultRateLimit = 8;
    public static $requests = array();


    /**
     * Construct with time window and default rate limit.
     *
     * TestRateLimiter constructor.
     * @param $timeWindow
     * @param $defaultRateLimit
     */
    public function __construct() {
    }

    /**
     * Get the time window in minutes for which rate is being measured.
     *
     * @return integer
     */
    public function getTimeWindowInMinutes() {
        return self::$timeWindow;
    }

    /**
     * Return the default rate limit (in requests per time window).  This is used if no multipliers are supplied
     * at the controller or method level.
     *
     * @return integer
     */
    public function getDefaultRateLimit() {
        return self::$defaultRateLimit;
    }

    /**
     * Get the number of requests within the current window for a source IP Address, controller and method.
     *
     * @return integer
     */
    public function getNumberOfRequestsInWindow($windowStartTime, $sourceIPAddress) {


        if (!isset(self::$requests[$windowStartTime])) {
            self::$requests[$windowStartTime] = array();
        }

        if (!isset(self::$requests[$windowStartTime][$sourceIPAddress])) {
            self::$requests[$windowStartTime][$sourceIPAddress] = 0;
        }

        self::$requests[$windowStartTime][$sourceIPAddress]++;

        return self::$requests[$windowStartTime][$sourceIPAddress];

    }
}
