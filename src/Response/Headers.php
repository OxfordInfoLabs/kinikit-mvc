<?php

namespace Kinikit\MVC\Response;


class Headers {


    // Mapping constants for header names

    // Access control
    const HEADER_ACCESS_CONTROL_ALLOW_ORIGIN = "access-control-allow-origin";
    const HEADER_ACCESS_CONTROL_ALLOW_CREDENTIALS = "access-control-allow-credentials";
    const HEADER_ACCESS_CONTROL_EXPOSE_HEADERS = "access-control-expose-headers";
    const HEADER_ACCESS_CONTROL_MAX_AGE = "access-control-max-age";
    const HEADER_ACCESS_CONTROL_ALLOW_METHODS = "access-control-allow-methods";
    const HEADER_ACCESS_CONTROL_ALLOW_HEADERS = "access-control-allow-headers";

    // Content
    const HEADER_CONTENT_DISPOSITION = "content-disposition";
    const HEADER_CONTENT_ENCODING = "content-encoding";
    const HEADER_CONTENT_LANGUAGE = "content-language";
    const HEADER_CONTENT_LENGTH = "content-length";
    const HEADER_CONTENT_LOCATION = "content-location";
    const HEADER_CONTENT_MD5 = "content-md5";
    const HEADER_CONTENT_RANGE = "content-range";
    const HEADER_CONTENT_TYPE = "content-type";


    // Caching and Lifecycle
    const HEADER_AGE = "age";
    const HEADER_CACHE_CONTROL = "cache-control";
    const HEADER_DATE = "date";
    const HEADER_ETAG = "etag";
    const HEADER_EXPIRES = "expires";
    const HEADER_LAST_MODIFIED = "last-modified";
    const HEADER_RETRY_AFTER = "retry-after";
    const HEADER_LOCATION = "location";

    // Rate Limiting
    const HEADER_RATELIMIT_LIMIT = "x-ratelimit-limit";
    const HEADER_RATELIMIT_REMAINING = "x-ratelimit-remaining";
    const HEADER_RATELIMIT_RESET = "x-ratelimit-reset";

    // General
    const HEADER_SET_COOKIE = "set-cookie";
    const HEADER_COOKIE = "cookie";

    // Multiple value headers
    const MULTIPLE_VALUE_HEADERS = [self::HEADER_SET_COOKIE];

    /**
     * Get a specific header by name - either returns an array if a multiple value header
     * or simply a string if single.
     *
     * @return mixed
     */
    public function get($headerName) {
        $allHeaders = $this->getAll();
        return $allHeaders[strtolower($headerName)] ?? null;
    }


    /**
     * Set a header by name
     *
     * @param string $headerName
     * @param string $value
     */
    public function set($headerName, $value) {
        header($headerName . ": " . $value, !in_array($headerName, self::MULTIPLE_VALUE_HEADERS));
    }


    /**
     * Get all headers
     */
    public function getAll() {
        $headers = php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();
        $allHeaders = [];


        // Loop through all headers, keep just the last one if not multiple.
        for ($i = sizeof($headers) - 1; $i >= 0; $i--) {
            $header = $headers[$i];
            $explodedHeader = explode(":", $header, 2);
            if (sizeof($explodedHeader) == 2) {

                $headerName = strtolower(trim($explodedHeader[0]));
                $headerValue = trim($explodedHeader[1]);

                $multipleValue = in_array($headerName, self::MULTIPLE_VALUE_HEADERS);

                // Initialise an array if necessary or set the value
                if (isset($allHeaders[$headerName])) {
                    if ($multipleValue) {
                        $allHeaders[$headerName][] = $headerValue;
                    }
                } else {
                    if ($multipleValue) {
                        $allHeaders[$headerName] = [$headerValue];
                    } else {
                        $allHeaders[$headerName] = $headerValue;
                    }
                }

            }
        }

        return $allHeaders;
    }

}
