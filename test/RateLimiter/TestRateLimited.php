<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:20
 */

namespace Kinikit\MVC\RateLimiter;


/**
 *
 * @ratelimiter \Kinikit\MVC\RateLimiter\TestRateLimiter
 *
 * @ratelimitmultiplier 0.5
 *
 */
class TestRateLimited {

    // Default handler
    public function defaultHandler($requestParameters) {
    }


    /**
     * @ratelimit 3
     */
    public function explicitLimitMethod() {

    }


    /**
     * @ratelimitmultiplier 0.25
     */
    public function multiplierLimitMethod() {

    }


}
