<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 15/10/2018
 * Time: 11:21
 */

namespace Kinikit\MVC\Objects;


class TestRESTObject {

    /**
     * Id
     *
     * @var integer
     */
    protected $id;

    /**
     * Name
     *
     * @var string
     */
    private $name;

    /**
     * Email
     *
     * @var string
     */
    private $email;

    /**
     * Last Status
     *
     * @var string
     */
    private $lastStatus;

    /**
     * Comments
     *
     * @var TestRESTObjectComment[]
     */
    private $comments;


    public function __construct($name = null, $email = null, $lastStatus = null) {
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

    /**
     * @return TestRESTObjectComment[]
     */
    public function getComments() {
        return $this->comments;
    }

    /**
     * @param TestRESTObjectComment[] $comments
     */
    public function setComments($comments) {
        $this->comments = $comments;
    }


}
