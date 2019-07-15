<?php


class NestedSimple {

    /**
     * Default handle request method
     *
     * @return \Kinikit\MVC\Response\View
     */
    public function handleRequest() {
        return new \Kinikit\MVC\Response\View("Simple", ["title" => "Marjorie"]);
    }


    /**
     * Non REST update method
     *
     * @param $data
     */
    public function update($data) {

    }


}
