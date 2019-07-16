<?php

namespace Kinikit\MVC\RateLimiter;

use Kinikit\Core\DependencyInjection\Container;


include_once "autoloader.php";

/**
 * Test cases
 *
 * Class RateLimiterEvaluatorTest
 */
class RateLimiterEvaluatorTest extends \PHPUnit\Framework\TestCase {


    public function setUp(): void {

        if (file_exists("ratelimits"))
            passthru("rm -rf ratelimits");

        mkdir("ratelimits");

    }


    public function testRateLimitsAreAppliedAndHeadersReturnedWhenControllerWithRateLimitsSuppliedToEvaluator() {


        // Set the X_FORWARDED Header for testing
        $_SERVER["HTTPS"] = 1;
        $_SERVER["SERVER_PORT"] = 443;
        $_SERVER['HTTP_HOST'] = "www.myspace.com";
        $_SERVER['REQUEST_URI'] = "/home/myshop";
        $_SERVER['QUERY_STRING'] = "hello=mark&test=11";
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "100.100.100.100";

        $evaluator = Container::instance()->get(RateLimiterEvaluator::class);


        $config = new RateLimitConfig(null, 3);

        // First request should succeed.
        $evaluator->evaluateRateLimiter($config);

        // Second request should succeed.
        $evaluator->evaluateRateLimiter($config);

        // Third request should succeed.
        $evaluator->evaluateRateLimiter($config);

        // Fourth request should fail.
        try {
            $evaluator->evaluateRateLimiter($config);
            $this->fail("should have thrown here");
        } catch (RateLimitExceededException $e) {
            // Yaah
        }


        $_SERVER["HTTP_X_FORWARDED_FOR"] = "100.100.100.10";


        $config = new RateLimitConfig(TestRateLimiter::class, null, 0.25);


        // First request should succeed.
        $evaluator->evaluateRateLimiter($config);

        // Second request should succeed.
        $evaluator->evaluateRateLimiter($config);

        // Third request should fail.
        try {
            $evaluator->evaluateRateLimiter($config);
            $this->fail("should have thrown here");
        } catch (RateLimitExceededException $e) {
            // Yaah
        }

        $this->assertTrue(true);


    }

}
