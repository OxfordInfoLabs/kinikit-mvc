<?php

namespace Kinikit\MVC\Response;

use Kinikit\Core\DependencyInjection\Container;

/**
 * Base HTTP Response class.  This is an abstract class designed for extension
 * as required for specific types of concrete response.
 *
 * Class Response
 */
abstract class Response {

    /**
     * HTTP Response Code.
     *
     * @var integer
     */
    private $responseCode;


    /**
     * Headers static
     *
     * @var Headers
     */
    private $headers;


    // Common response constants used by the framework.
    const RESPONSE_SUCCESS = 200;
    const RESPONSE_REDIRECT_PERMANENT = 301;
    const RESPONSE_REDIRECT_TEMPORARY = 302;
    const RESPONSE_ACCESS_DENIED = 403;
    const RESPONSE_NOT_FOUND = 404;
    const RESPONSE_RATE_LIMITED = 429;
    const RESPONSE_GENERAL_ERROR = 500;


    /**
     * Constructor for a response
     *
     * Response constructor.
     *
     * @param integer $responseCode
     * @param string $contentType
     */
    public function __construct($responseCode, $contentType) {
        $this->responseCode = $responseCode;
        $this->headers = Container::instance()->get(Headers::class);
        
    }


}
