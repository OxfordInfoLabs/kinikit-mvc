<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:37
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;

class APIProperty extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $type;


    /**
     * @var string
     */
    protected $comment;


    /**
     * Inline description for this variable
     *
     * @var string
     */
    protected $description;

    /**
     * @var boolean
     */
    protected $updatable;

    /**
     * @var integer
     */
    protected $index;

    /**
     * @var integer
     */
    protected $updatableIndex;


    /**
     * @var boolean
     */
    protected $required;

    /**
     * @var string
     */
    protected $clientType;

    /**
     * @var boolean
     */
    private $inherited;


    /**
     * @var mixed
     */
    private $defaultValue;

    /**
     * APIProperty constructor.
     * @param string $name
     * @param string $type
     * @param null $description
     * @param string $comment
     * @param boolean $updatable
     * @param int $index
     * @param int $updatableIndex
     * @param null $required
     * @param null $clientType
     * @param boolean $inherited
     */
    public function __construct($name = null, $type = null, $description = null, $comment = null, $updatable = null, $index = null, $updatableIndex = null, $required = null, $clientType = null, $inherited = null, $defaultValue = null) {
        parent::__construct(false);

        $this->name = $name;
        $this->type = $type;
        $this->description = $description;
        $this->comment = $comment;
        $this->updatable = $updatable;
        $this->index = $index;
        $this->updatableIndex = $updatableIndex;
        $this->clientType = $clientType;
        $this->required = $required;
        $this->inherited = $inherited;
        $this->defaultValue = $defaultValue;
    }


    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    public function getShortType() {
        $explodedType = explode("\\", $this->type);
        return array_pop($explodedType);
    }


    /**
     * Indicator as to whether this type represents a string value
     *
     * @return bool
     */
    public function getIsString() {
        return $this->getType() == "string";
    }

    /**
     * Indicator as to whether or not this type is an object
     *
     * @return bool
     */
    public function getIsObject() {
        $primitives = array("boolean", "integer", "int", "string", "float");
        return !is_numeric(array_search($this->getType(), $primitives));
    }

    /**
     * Indicator as to whether or not this property is numeric
     *
     * @return bool
     */
    public function getIsNumeric() {
        $numerics = array("float", "integer", "int");
        return is_numeric(array_search($this->getType(), $numerics));
    }

    /**
     * Indicator as to whether this type is an array type.
     */
    public function getIsArray() {
        return strpos($this->getType(), "[");
    }


    /**
     * If this an array type, return the underlying single type without []
     */
    public function getArrayShortType() {
        return preg_replace("/\[.*?]/", "", $this->getShortType());
    }


    /**
     * If there is an associative array key
     *
     * @return mixed
     */
    public function getArrayAssociativeKey() {
        preg_match("/\[(.*?)]/", $this->getShortType(), $matches);
        if (sizeof($matches) > 1) {
            return $matches[1];
        }
    }

    /**
     * Get the type path if a class
     */
    public function getTypePath() {
        $type = preg_replace("/\[.*?\]/", "", $this->type);
        $pathRewrite = str_replace("\\", "/", $type);
        return $pathRewrite != $type ? "/" . ltrim($pathRewrite, "/") : null;
    }


    public function getRelativeTypePath() {
        return trim($this->getTypePath(), "/");
    }

    /**
     * @return string
     */
    public function getClientType() {
        return $this->clientType;
    }


    /**
     * @return string
     */
    public function getDescription() {
        return $this->description ? $this->description : $this->getCommentHTML();
    }


    /**
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Get an HTML formatted version of the comment
     *
     * @return mixed
     */
    public function getCommentHTML() {
        return str_replace("\n", "<br />", trim(str_replace(array("/*", "*"), array("", ""), $this->getComment())));
    }

    /**
     * @return bool
     */
    public function getUpdatable() {
        return $this->updatable;
    }

    /**
     * @return int
     */
    public function getIndex() {
        return $this->index;
    }

    /**
     * @return int
     */
    public function getUpdatableIndex() {
        return $this->updatableIndex;
    }


    /**
     * Get a capitalised version of the property name.
     *
     * @return string
     */
    public function getUcaseName() {
        return ucfirst($this->name);
    }

    /**
     * @return bool
     */
    public function isRequired() {
        return $this->required;
    }

    /**
     * @return null
     */
    public function getInherited() {
        return $this->inherited;
    }

    /**
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }


}
