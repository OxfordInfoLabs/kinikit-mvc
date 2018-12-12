<?php

namespace Kinikit\MVC\Framework\RateLimiter;

use Kinikit\MVC\Exception\RateLimitExceededException;

include_once "autoloader.php";

/**
 * Test cases
 *
 * Class RateLimiterEvaluatorTest
 */
class RateLimiterEvaluatorTest extends \PHPUnit\Framework\TestCase {

    public function testRateLimitsAreAppliedAndHeadersReturnedWhenControllerWithRateLimitsSuppliedToEvaluator() {

        $rateLimited = new TestRateLimited();

        $evaluator = RateLimiterEvaluator::instance();


        // Set the X_FORWARDED Header for testing
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "100.100.100.100";

        // First request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "defaultHandler");

        // Second request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "defaultHandler");

        // Third request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "defaultHandler");

        // Fourth request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "defaultHandler");

        // Fifth request should fail.
        try {
            $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "defaultHandler");
            $this->fail("should have thrown here");
        } catch (RateLimitExceededException $e) {
            // Yaah
        }


        // First request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "explicitLimitMethod");

        // Second request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "explicitLimitMethod");

        // Third request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "explicitLimitMethod");

        // Fourth request should fail.
        try {
            $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "explicitLimitMethod");
            $this->fail("should have thrown here");
        } catch (RateLimitExceededException $e) {
            // Yaah
        }


        // First request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "multiplierLimitMethod");

        // Second request should succeed.
        $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "multiplierLimitMethod");

        // Third request should fail.
        try {
            $evaluator->evaluateRateLimitersForControllerMethod($rateLimited, "multiplierLimitMethod");
            $this->fail("should have thrown here");
        } catch (RateLimitExceededException $e) {
            // Yaah
        }

        $this->assertTrue(true);


    }

}