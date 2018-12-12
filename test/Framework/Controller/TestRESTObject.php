<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 15/10/2018
 * Time: 11:21
 */

namespace Kinikit\MVC\Framework\Controller;


use Kinikit\Core\Object\SerialisableObject;

class TestRESTObject extends SerialisableObject {

    private $id;
    private $name;
    private $email;
    private $lastStatus;


    public function __construct($id = null, $name = null, $email = null, $lastStatus = null) {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->lastStatus = $lastStatus;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id) {
        $this->id = $id;
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
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getLastStatus() {
        return $this->lastStatus;
    }

    /**
     * @param mixed $lastStatus
     */
    public function setLastStatus($lastStatus) {
        $this->lastStatus = $lastStatus;
    }


}