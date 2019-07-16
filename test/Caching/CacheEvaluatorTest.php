<?php

namespace Kinikit\MVC\Caching;


use Kinikit\Core\DependencyInjection\Container;

class CacheEvaluatorTest extends \PHPUnit\Framework\TestCase {


    public function testCanCacheObjectsAndGetThemBackUsingConfig() {

        /**
         * @var $testCache TestCache
         */
        $testCache = Container::instance()->get(TestCache::class);

        /**
         * @var $cacheEvaluator CacheEvaluator
         */
        $cacheEvaluator = Container::instance()->get(CacheEvaluator::class);

        $config = new CacheConfig(TestCache::class, "1h");
        $cacheEvaluator->cacheResult($config, "http://www.google.com", "MOPALOP");

        $this->assertEquals(["MOPALOP", 60], $testCache->getCachedItems()["http://www.google.com"]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult($config, "http://www.google.com"));

        $config = new CacheConfig(TestCache::class, "2d");
        $cacheEvaluator->cacheResult($config, "http://www.google.co.uk", "MOPALOP");

        $this->assertEquals(["MOPALOP", 60 * 48], $testCache->getCachedItems()["http://www.google.co.uk"]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult($config, "http://www.google.co.uk"));



        $config = new CacheConfig(TestCache::class, "3y");
        $cacheEvaluator->cacheResult($config, "http://www.microsoft.com", "Try a lot");

        $this->assertEquals(["Try a lot", 60 * 24 * 365 * 3], $testCache->getCachedItems()["http://www.microsoft.com"]);
        $this->assertEquals("Try a lot", $cacheEvaluator->getCachedResult($config, "http://www.microsoft.com"));


    }

}
