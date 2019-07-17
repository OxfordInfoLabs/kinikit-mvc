<?php


namespace Kinikit\MVC\RateLimiter;


class RateLimiterConfig {

    /**
     * @var integer
     */
    private $rateLimit;

    /**
     * @var integer
     */
    private $rateLimitMultiplier;


    /**
     * RateLimitConfig constructor.
     *
     * @param $rateLimit
     * @param $rateLimitMultiplier
     * @param $timeWindowInMinutes
     */
    public function __construct($rateLimit = null, $rateLimitMultiplier = null) {
        $this->rateLimit = $rateLimit;
        $this->rateLimitMultiplier = $rateLimitMultiplier;
    }


    /**
     * @return mixed
     */
    public function getRateLimit() {
        return $this->rateLimit;
    }

    /**
     * @param mixed $rateLimit
     */
    public function setRateLimit($rateLimit): void {
        $this->rateLimit = $rateLimit;
    }

    /**
     * @return mixed
     */
    public function getRateLimitMultiplier() {
        return $this->rateLimitMultiplier;
    }

    /**
     * @param mixed $rateLimitMultiplier
     */
    public function setRateLimitMultiplier($rateLimitMultiplier): void {
        $this->rateLimitMultiplier = $rateLimitMultiplier;
    }


}
