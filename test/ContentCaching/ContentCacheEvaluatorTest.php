<?php

namespace Kinikit\MVC\ContentCaching;

use Kinikit\Core\Caching\CacheProvider;
use Kinikit\Core\DependencyInjection\Container;

include_once "autoloader.php";

class ContentCacheEvaluatorTest extends \PHPUnit\Framework\TestCase {


    public function testCanCacheObjectsAndGetThemBackUsingConfig() {

        /**
         * @var $testCache TestCache
         */
        Container::instance()->addClassMapping(CacheProvider::class, TestCache::class);


        $testCache = Container::instance()->get(CacheProvider::class);

        /**
         * @var $cacheEvaluator ContentCacheEvaluator
         */
        $cacheEvaluator = Container::instance()->get(ContentCacheEvaluator::class);

        $config = new ContentCacheConfig("1h");
        $cacheEvaluator->cacheResult($config, "http://www.google.com", "MOPALOP");

        $this->assertEquals(["MOPALOP", 3600], $testCache->getCachedItems()[md5("http://www.google.com")]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult("http://www.google.com"));

        $config = new ContentCacheConfig("2d");
        $cacheEvaluator->cacheResult($config, "http://www.google.co.uk", "MOPALOP");

        $this->assertEquals(["MOPALOP", 3600 * 48], $testCache->getCachedItems()[md5("http://www.google.co.uk")]);
        $this->assertEquals("MOPALOP", $cacheEvaluator->getCachedResult("http://www.google.co.uk"));


        $config = new ContentCacheConfig("3y");
        $cacheEvaluator->cacheResult($config, "http://www.microsoft.com", "Try a lot");

        $this->assertEquals(["Try a lot", 3600 * 24 * 365 * 3], $testCache->getCachedItems()[md5("http://www.microsoft.com")]);
        $this->assertEquals("Try a lot", $cacheEvaluator->getCachedResult("http://www.microsoft.com"));


    }

}
