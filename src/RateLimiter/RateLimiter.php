<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 11:41
 */

namespace Kinikit\MVC\RateLimiter;

/**
 * Generic rate limiter interface for all rate limiters.
 *
 * @defaultImplementation Kinikit\MVC\RateLimiter\FileStoreRateLimiter
 *
 * Interface RateLimiter
 * @package Kinikit\MVC\Framework\RateLimiter
 */
interface RateLimiter {

    /**
     * Get the time window in minutes for which rate is being measured.
     *
     * @return integer
     */
    public function getTimeWindowInMinutes();

    /**
     * Return the default rate limit (in requests per time window).  This is used if no multipliers are supplied
     * at the controller or method level.
     *
     * @return integer
     */
    public function getDefaultRateLimit();


    /**
     * Get the number of requests within the current window for a source IP Address.
     *
     * @return integer
     */
    public function getNumberOfRequestsInWindow($windowStartTime, $sourceIPAddress);


}
