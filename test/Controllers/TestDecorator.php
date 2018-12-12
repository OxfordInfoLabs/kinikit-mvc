<?php


use Kinikit\MVC\Framework\Controller\Decorator;
use Kinikit\MVC\Framework\ModelAndView;

class TestDecorator extends Decorator {

    /**
     * Implement required method
     */
    public function handleDecoratorRequest() {
        return new ModelAndView ("banana", array("test" => "Bodger", "test2" => "Badger"));
    }

}

?>