<?php


namespace Kinikit\MVC\Request;


class Request {

    /**
     * @var URL
     */
    private $url;

    /**
     * @var Headers
     */
    private $headers;

    /**
     * @var string
     */
    private $requestMethod;


    /**
     * @var string
     */
    private $remoteIPAddress;

    /**
     * @var URL
     */
    private $referringURL;


    /**
     * Request parameters
     *
     * @var mixed[string]
     */
    private $parameters = array();

    /**
     * @var string
     */
    private $payload;


    /**
     * @var FileUpload[string]
     */
    private $fileUploads = array();


    const METHOD_HEAD = "HEAD";
    const METHOD_GET = "GET";
    const METHOD_POST = "POST";
    const METHOD_PUT = "PUT";
    const METHOD_PATCH = "PATCH";
    const METHOD_DELETE = "DELETE";


    /**
     * Construct and parse current request straight away.
     *
     * Request constructor.
     * @param Headers $headers
     */
    public function __construct($headers) {
        $this->headers = $headers;
        $this->parseCurrentRequest();
    }

    /**
     * Get the URL object for this request
     *
     * @return URL
     */
    public function getUrl() {
        return $this->url;
    }

    /**
     * Get the headers for this request as a Headers object.
     *
     * @return Headers
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Get the request method (one of the method_ class constants).
     *
     * @return string
     */
    public function getRequestMethod() {
        return $this->requestMethod;
    }

    /**
     * Get the IP address of the calling user.
     *
     * @return string
     */
    public function getRemoteIPAddress() {
        return $this->remoteIPAddress;
    }

    /**
     * Get the referring url as an object.
     *
     * @return URL
     */
    public function getReferringURL() {
        return $this->referringURL;
    }

    /**
     * @return mixed
     */
    public function getParameters() {
        return $this->parameters;
    }

    /**
     * Get a single parameter by key
     *
     * @param $key
     * @return string
     */
    public function getParameter($key) {
        return isset($this->parameters[$key]) ? $this->parameters[$key] : null;
    }


    /**
     * @return string
     */
    public function getPayload() {
        return $this->payload;
    }

    /**
     * Get all file uploads keyed in by
     *
     * @return FileUpload[string]
     */
    public function getFileUploads() {
        return $this->fileUploads;
    }

    /**
     * Get a single file upload using the parameter name (usually set in a form).
     *
     * @param $parameterName
     * @return FileUpload
     */
    public function getFileUpload($parameterName) {
        return isset($this->fileUploads[$parameterName]) ? $this->fileUploads[$parameterName] : null;
    }


    // Parse the current request and populate accordingly.
    private function parseCurrentRequest() {

        // Do the URL first
        $url = isset($_SERVER["HTTPS"]) ? "https" : "http";
        $url .= "://" . $_SERVER["HTTP_HOST"] . ":" . $_SERVER["SERVER_PORT"];
        $url .= $_SERVER["REQUEST_URI"];
        if (isset($_SERVER['QUERY_STRING'])) {
            $url .= "?" . $_SERVER['QUERY_STRING'];
        }
        $this->url = new URL($url);


        // Now add other properties
        $this->requestMethod = isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : null;
        $this->remoteIPAddress = isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : (isset($_SERVER["REMOTE_ADDR"]) ? $_SERVER["REMOTE_ADDR"] : null);

        if (isset($_SERVER["HTTP_REFERER"])) {
            $this->referringURL = new URL($_SERVER["HTTP_REFERER"]);
        }

        // Now handle parameters.   We need to read these from input stream if not a get request.
        if ($this->requestMethod != self::METHOD_GET && $this->requestMethod != self::METHOD_HEAD) {

            // Grab the PHP input stream.
            $directPHPInput = file_get_contents("php://input");
            $explodedParams = explode("&", $directPHPInput);

            // If only one param and not a key value pair, assume payload.
            if (sizeof($explodedParams) == 1 && !preg_match("/^[a-z0-9A-Z]+\=/", $explodedParams[0])) {
                $this->payload = rawurldecode($explodedParams[0]);
            } else {

                // Convert post params
                foreach ($explodedParams as $param) {
                    $explodedParam = explode("=", $param);

                    if (sizeof($explodedParam) == 2) {
                        $this->parameters [urldecode($explodedParam[0])] = urldecode($explodedParam[1]);
                    }
                }
            }


        }

        // Always merge in Get params as well.
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

            $this->parameters[urldecode($key)] = $decoded;
        }


        // Finally handle File uploads
        if (isset($_FILES) && sizeof($_FILES) > 0) {
            foreach ($_FILES as $key => $file) {
                $this->fileUploads[$key] = new FileUpload($key, $file);
            }
        }


    }


}
