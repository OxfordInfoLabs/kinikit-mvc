<?php

namespace Kinikit\MVC\Caching;

use Kinikit\Core\Util\Annotation\ClassAnnotations;

/**
 * Read / Write data to the cache if a cache value is set for a specific method.
 *
 * @package Kinikit\MVC\Framework\Caching
 */
class CacheEvaluator {

    /**
     * @var Cache
     */
    private $defaultCache;

    private $controllerCaches = array();


    /**
     * Construct with default cache implementation.
     *
     * CacheEvaluator constructor.
     *
     * @param Cache $defaultCache
     */
    public function __construct($defaultCache) {
        $this->defaultCache = $defaultCache;
    }


    /**
     * Get cached result for a controller using class annotations.
     *
     * @param $controllerObject
     * @param $methodName
     * @param $params
     * @param $classAnnotations ClassAnnotations
     */
    public function getCachedResult($controllerObject, $methodName, $params, $classAnnotations) {

        $cacheTime = $classAnnotations->getMethodAnnotationsForMatchingTag("cacheTime", $methodName);
        if ($cacheTime) {
            $cacheTime = $cacheTime[0]->getValue();
            return $this->getCache($controllerObject, $classAnnotations)->getCachedResult($controllerObject, $methodName, $params, $this->convertCacheTime($cacheTime));
        }

    }


    /**
     * Cache result if required.
     *
     * @param $controllerObject
     * @param $methodName
     * @param $params
     * @param $classAnnotations ClassAnnotations
     */
    public function cacheResult($controllerObject, $methodName, $params, $classAnnotations) {

        $cacheTime = $classAnnotations->getMethodAnnotationsForMatchingTag("cacheTime", $methodName);
        if ($cacheTime) {
            $cacheTime = $cacheTime[0]->getValue();
            return $this->getCache($controllerObject, $classAnnotations)->cacheResult($controllerObject, $methodName, $params, $this->convertCacheTime($cacheTime));
        }

    }


    /**
     * @param $controllerObject
     * @param $classAnnotations
     * @return Cache
     */
    private function getCache($controllerObject, $classAnnotations) {

        $controllerName = get_class($controllerObject);

        if (!isset($this->controllerCaches[$controllerName])) {

            if (!$classAnnotations) {
                $classAnnotations = ClassAnnotationParser::instance()->parse($controllerName);
            }

            // Look for an annotation based rate limiter
            $cacheProvider = $classAnnotations->getClassAnnotationForMatchingTag("cache");

            // Either use an explicit class configured cache or the default.
            if ($cacheProvider) {
                $cacheClass = $cacheProvider->getValue();
                $cache = new $cacheClass();
            } else {
                $cache = $this->defaultCache;
            }

            $this->controllerCaches[$controllerName] = $cache;
        }

        return $this->controllerCaches[$controllerName];

    }


    // Convert cache time
    private function convertCacheTime($cacheTime) {

        $cacheTime = trim($cacheTime);

        if (is_numeric($cacheTime)) return $cacheTime;
        else {
            $period = substr($cacheTime, "-1");
            $cacheTime = substr($cacheTime, 0, strlen($cacheTime) - 1);

            if (is_numeric($cacheTime)) {
                switch ($period) {
                    case "h":
                        $cacheTime = $cacheTime * 60;
                        break;
                    case "d":
                        $cacheTime = $cacheTime * 60 * 24;
                        break;
                    case "y":
                        $cacheTime = $cacheTime * 60 * 24 * 365;
                        break;

                }
            }

        }

        return $cacheTime;

    }

}
