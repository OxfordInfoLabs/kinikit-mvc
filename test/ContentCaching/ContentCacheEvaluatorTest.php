<?php

namespace Kinikit\MVC\ContentCaching;

use Kinikit\Core\DependencyInjection\Container;

include_once "autoloader.php";

class ContentCacheEvaluatorTest extends \PHPUnit\Framework\TestCase {


    public function testCanCacheObjectsAndGetThemBackUsingConfig() {

        /**
         * @var $testCache TestCache
         */
        Container::instance()->addClassMapping(ContentCache::class, TestCache::class);


        $testCache = Container::instance()->get(ContentCache::class);

        /**
         * @var $cacheEvaluator CacheEvaluator
         */
        $cacheEvaluator = Container::instance()->get(ContentCacheEvaluator::class);

        $config = new ContentCacheConfig("1h");
        $cacheEvaluator->cacheResult($config, "http://www.google.com", "MOPALOP");

        $this->assertEquals(["MOPALOP", 60], $testCache->getCachedItems()["http://www.google.com"]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult($config, "http://www.google.com"));

        $config = new ContentCacheConfig("2d");
        $cacheEvaluator->cacheResult($config, "http://www.google.co.uk", "MOPALOP");

        $this->assertEquals(["MOPALOP", 60 * 48], $testCache->getCachedItems()["http://www.google.co.uk"]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult($config, "http://www.google.co.uk"));


        $config = new ContentCacheConfig("3y");
        $cacheEvaluator->cacheResult($config, "http://www.microsoft.com", "Try a lot");

        $this->assertEquals(["Try a lot", 60 * 24 * 365 * 3], $testCache->getCachedItems()["http://www.microsoft.com"]);
        $this->assertEquals("Try a lot", $cacheEvaluator->getCachedResult($config, "http://www.microsoft.com"));


    }

}
