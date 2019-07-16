<?php


namespace Kinikit\MVC\Response;

/**
 * Internal only response used by the Controller Route Handler
 * to return a web error which causes an internal re-route to an appropriate controller / view.
 *
 * Class WebErrorResponse
 */
class WebErrorResponse extends Response {

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @var integer
     */
    private $errorCode;

    /**
     * WebErrorResponse constructor.
     *
     * @param string $errorMessage
     * @param integer $errorCode
     * @param integer $responseCode
     */
    public function __construct($errorMessage, $errorCode, $responseCode) {
        parent::__construct($responseCode);
        $this->errorCode = $errorCode;
        $this->errorMessage = $errorMessage;
    }


    /**
     * Return the content type.  This must be implemented by all Responses.
     *
     * @return string
     */
    public function getContentType() {
        return null;
    }

    /**
     * Return the content length.  This must be implemented by all Responses but can return null
     * if no length header is to be added.
     *
     * @return integer
     */
    public function getContentLength() {
        return null;
    }

    /**
     * Echo the content directly to stdout.  This must be implemented by all Responses.
     *
     * @return mixed
     */
    public function streamContent() {
        return null;
    }
}
