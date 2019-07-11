<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 22/10/2018
 * Time: 12:03
 */

namespace Kinikit\MVC\Framework\API;


use Kinikit\Core\Object\SerialisableObject;

class APIGlobalParam extends SerialisableObject {

    private $name;
    private $description;
    private $index;

    /**
     * Construct
     *
     * APIGlobalParam constructor.
     * @param $name
     * @param $description
     */
    public function __construct($name = null, $description = null) {
        $this->name = $name;
        $this->description = $description;
    }


    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description) {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * @param mixed $index
     */
    public function setIndex($index) {
        $this->index = $index;
    }


}