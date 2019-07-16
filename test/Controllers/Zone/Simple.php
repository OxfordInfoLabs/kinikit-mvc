<?php


class Simple {

    /**
     * Default handle request method
     *
     * @return \Kinikit\MVC\Response\View
     */
    public function handleRequest() {
        return new \Kinikit\MVC\Response\View("Simple", ["title" => "Marjorie"]);
    }


}
