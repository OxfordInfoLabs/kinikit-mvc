<?php

namespace Kinikit\MVC\Framework\HTTP;

use Kinikit\Core\Util\Logging\Logger;
use Kinikit\Core\Util\Serialisation\JSON\JSONToObjectConverter;
use Kinikit\Core\Util\Serialisation\XML\XMLToObjectConverter;

/**
 * Representation of the current HTTP Request.  This is designed to be injected into controllers etc
 * as a singleton to provide access to useful items such as headers, request params, POST payloads,
 * uploaded files etc.
 *
 * Class HttpRequest
 */
class HttpRequest {

    private static $instance;
    private $parameters = array();
    private $payload = null;
    private $url = null;

    /**
     * Constructor, parses the request objects and populates the member variables.
     *
     *
     * @param URL $url
     * @param string[] $parameters
     *
     */
    public function __construct($url = null, $parameters = null, $payload = null) {


        // Use passed url if supplied, otherwise use current URl
        if ($url) {
            $this->url = $url;
        } else {
            $url = new URL();
        }

        // Use passed parameters is supplied, otherwise use current params.
        if ($parameters) {
            $this->parameters = $parameters;
            if ($payload !== null) $this->payload = $payload;
        } else {
            // GRAB DIRECT POST DATA AS AN ASSOCIATIVE ARRAY
            // Required for data submitted as POST over ajax
            $directPHPInput = file_get_contents("php://input");
            $explodedParams = explode("&", $directPHPInput);

            if (isset($_SERVER["CONTENT_TYPE"]) && $_SERVER["CONTENT_TYPE"] == "application/xml") {
                $converter = new XMLToObjectConverter();
            } else {
                $converter = new JSONToObjectConverter();
            }

            $convertedInput = array();

            // Convert get params
            foreach ($_GET as $key => $value) {

                if (is_array($value)) {
                    $decoded = array();
                    foreach ($value as $valueEntry) {
                        $decoded[] = urldecode($valueEntry);
                    }
                } else
                    $decoded = urldecode($value);

                // Handle booleans.
                if ($decoded == "false") {
                    $decoded = false;
                } else if ($decoded == "true") {
                    $decoded = true;
                }

                $convertedInput[urldecode($key)] = $decoded;
            }



            // If only one param and not a key value pair, assume payload.
            if (sizeof($explodedParams) == 1 && !preg_match("/^[a-z0-9A-Z]+\=/", $explodedParams[0])) {
                $this->payload = $converter->convert(rawurldecode($explodedParams[0]));
            } else {

                // Convert post params
                foreach ($explodedParams as $param) {
                    $explodedParam = explode("=", $param);

                    if (sizeof($explodedParam) == 2) {
                        $decoded = urldecode($explodedParam[1]);

                        $converted = $converter->convert($decoded);
                        $convertedInput[urldecode($explodedParam[0])] = $converted ? $converted : $decoded;
                    }
                }
            }


            $this->parameters = $convertedInput;
        }


    }


    /**
     * Get a request parameter by key
     *
     * @param string $key
     */
    public function getParameter($key) {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
    }


    /**
     * Get all cleaned request parameters
     */
    public function getAllParameters() {
        return $this->parameters;
    }


    /**
     * Get the raw request object
     *
     * @return mixed
     */
    public function getAllRawParameters() {
        return $_REQUEST;
    }


    /**
     * Get the POST/PUT/DELETE payload if one was supplied
     */
    public function getPayload() {
        return $this->payload;
    }


}
