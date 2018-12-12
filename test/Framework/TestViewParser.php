<?php


namespace Kinikit\MVC\Framework;


class TestViewParser implements TemplateParser {


    public function parseTemplateText($viewText, &$model) {
        return str_replace(array("1", "2", "3", "4", "5"), array("One", "Two", "Three", "Four", "Five"), $viewText);
    }


}