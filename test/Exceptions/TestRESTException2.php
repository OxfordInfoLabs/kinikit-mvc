<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 26/10/2018
 * Time: 10:34
 */

namespace Kinikit\MVC\Exceptions;


use Kinikit\Core\Exception\SerialisableException;


class TestRESTException2 extends SerialisableException {

    private $extraProperty;


    public function __construct($extraProperty) {
        parent::__construct("Test REST Exception", 111);
        $this->extraProperty = $extraProperty;
    }

    /**
     * @return mixed
     */
    public function getExtraProperty() {
        return $this->extraProperty;
    }

    /**
     * @param mixed $extraProperty
     */
    public function setExtraProperty($extraProperty) {
        $this->extraProperty = $extraProperty;
    }


}