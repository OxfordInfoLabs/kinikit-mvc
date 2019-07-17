<?php

/**
 * Class Simple
 *
 * @rateLimitMultiplier 3
 */
class Simple {

    /**
     * Default handle request method
     *
     * @return \Kinikit\MVC\Response\View
     */
    public function handleRequest() {
        return new \Kinikit\MVC\Response\View("Simple", ["title" => "Marjorie"]);
    }


    /**
     * Get a simple title one.
     *
     * @cacheTime 30d
     *
     * @param $title
     */
    public function get($title) {
        return new \Kinikit\MVC\Response\View("Simple", ["title" => $title]);
    }


    /**
     *
     * @return \Kinikit\MVC\Response\View
     * @throws Exception
     */
    public function throwsError() {
        throw new \Exception("Bad Web Request", 22);
    }


    /**
     *
     * @return \Kinikit\MVC\Response\View
     * @throws Exception
     */
    public function throwsStatusError() {
        throw new \Kinikit\Core\Exception\StatusException("Bad Custom Web Request", 402, 22);
    }


    /**
     *
     * @cacheTime 1y
     *
     * @return \Kinikit\MVC\Response\Response
     */
    public function redirect() {
        return new \Kinikit\MVC\Response\Redirect("http://www.google.com");
    }


    /**
     * @cached
     *
     * @return \Kinikit\MVC\Response\Response
     */
    public function download() {
        return new \Kinikit\MVC\Response\Download("BINGO", "bingo.txt");
    }


    /**
     * Autowiring test
     *
     * @param \Kinikit\MVC\Request\Request $request
     * @param \Kinikit\MVC\Request\URL $url
     * @param \Kinikit\MVC\Request\Headers $headers
     * @param \Kinikit\MVC\Request\FileUpload[] $fileuploads
     */
    public function autowired($request, $url, $headers, $fileuploads) {
        return [$request, $url, $headers, $fileuploads];
    }

}
