<?php


namespace Kinikit\MVC\RateLimiter;


class RateLimitConfig {

    private $rateLimit;

    private $rateLimitMultiplier;

    private $timeWindowInMinutes;

    /**
     * RateLimitConfig constructor.
     *
     * @param $rateLimit
     * @param $rateLimitMultiplier
     * @param $timeWindowInMinutes
     */
    public function __construct($rateLimit, $rateLimitMultiplier, $timeWindowInMinutes) {
        $this->rateLimit = $rateLimit;
        $this->rateLimitMultiplier = $rateLimitMultiplier;
        $this->timeWindowInMinutes = $timeWindowInMinutes;
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

    /**
     * @return mixed
     */
    public function getTimeWindowInMinutes() {
        return $this->timeWindowInMinutes;
    }

    /**
     * @param mixed $timeWindowInMinutes
     */
    public function setTimeWindowInMinutes($timeWindowInMinutes): void {
        $this->timeWindowInMinutes = $timeWindowInMinutes;
    }


}
