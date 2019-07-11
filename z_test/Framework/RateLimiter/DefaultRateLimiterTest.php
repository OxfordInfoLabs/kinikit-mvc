<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 16:01
 */

namespace Kinikit\MVC\Framework\RateLimiter;


include_once "autoloader.php";


class DefaultRateLimiterTest extends \PHPUnit\Framework\TestCase {

    public function testDefaultRateLimiterUsesFileSystemToStoreRateLimits() {


        $rateLimiter = new DefaultRateLimiter();

        $date = date("U");

        // Increment rate limits
        $this->assertEquals(1, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("H", fgets($file));

        $this->assertEquals(2, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("HH", fgets($file));

        $this->assertEquals(3, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("HHH", fgets($file));

        $this->assertEquals(4, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("HHHH", fgets($file));

        $date = date("U") + 10;

        $this->assertEquals(1, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("H", fgets($file));

        $this->assertEquals(2, $rateLimiter->getNumberOfRequestsInWindow($date, "212.33.55.666", "TestController", "John"));
        $file = fopen("ratelimits/212.33.55.666", "r");
        $this->assertEquals($date, fgets($file, 11));
        $this->assertEquals("HH", fgets($file));


    }

}
