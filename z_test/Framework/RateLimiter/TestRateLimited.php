<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:20
 */

namespace Kinikit\MVC\Framework\RateLimiter;


use Kinikit\MVC\Framework\Controller;

/**
 *
 * @ratelimiter \Kinikit\MVC\Framework\RateLimiter\TestRateLimiter
 *
 * @ratelimitmultiplier 0.5
 *
 */
class TestRateLimited extends Controller {

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