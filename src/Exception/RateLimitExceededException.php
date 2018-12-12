<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 06/12/2018
 * Time: 12:52
 */

namespace Kinikit\MVC\Exception;


use Kinikit\Core\Exception\SerialisableException;


class RateLimitExceededException extends SerialisableException {

    /**
     * Construct a rate limit exceeded exception.
     *
     * RateLimitExceededException constructor.
     * @param null $ipAddress
     * @param null $limit
     * @param $resetTime
     */
    public function __construct($ipAddress = null, $limit = null, $resetTime = null) {
        $resetDate = date_create_from_format("U", $resetTime)->format("d/m/Y H:i:s");
        parent::__construct("Rate limit of $limit exceeded for $ipAddress.  You can make another request after $resetDate");
    }

}