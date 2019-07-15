<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 18/10/2018
 * Time: 10:39
 */

namespace Kinikit\MVC\Objects;


/**
 * Object comment
 *
 * Class TestRESTObjectComment
 */
class TestRESTObjectComment {

    /**
     * @var integer
     */
    protected $id;

    /**
     * @var integer
     */
    protected $objectId;

    /**
     * @var string
     */
    private $comment;

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getObjectId() {
        return $this->objectId;
    }

    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment) {
        $this->comment = $comment;
    }


}
