<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:22
 */

namespace Kinikit\MVC\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;


class APIObject extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $namespace;

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $comment;

    /**
     * @var Kinikit\MVC\API\Descriptor\APIProperty[]
     */
    protected $properties = array();


    /**
     * @var string[]
     */
    protected $requiredObjects;


    /**
     * The client namespace.
     *
     * @var string
     */
    protected $clientNamespace;


    /**
     * APIObject constructor.
     * @param null $path
     * @param string $namespace
     * @param string $name
     * @param $comment
     * @param Kinikit\MVC\API\Descriptor\APIProperty[] $properties
     * @param string[] $requiredObjects
     * @param null $clientNamespace
     */
    public function __construct($path = null, $namespace = null, $name = null, $comment = null, $properties = array(), $requiredObjects = array(), $clientNamespace = null) {

        parent::__construct(false);

        $this->path = $path;
        $this->namespace = $namespace;
        $this->name = $name;
        $this->properties = $properties;
        $this->requiredObjects = $requiredObjects;
        $this->comment = $comment;
        $this->clientNamespace = $clientNamespace;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }


    /**
     * @return string
     */
    public function getNamespace() {
        return $this->namespace;
    }

    /**
     * @return string
     */
    public function getClientNamespace() {
        return $this->clientNamespace;
    }


    /**
     * Get the client path
     */
    public function getClientPath() {
        return str_replace("\\", "/", $this->clientNamespace) . "/" . $this->getName();
    }


    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     *
     * Alias of get name for use in sub loops.
     *
     * @return string
     */
    public function getClassName() {
        return $this->name;
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
        return str_replace("\n", "<br />", trim(str_replace(array("/*", "*/", "*"), array("", "", ""), $this->getComment())));
    }

    /**
     * @return Kinikit\MVC\API\Descriptor\APIProperty[]
     */
    public function getProperties() {
        return $this->properties;
    }


    /**
     * Get the number of updatable properties
     */
    public function getNumberOfUpdatableProperties() {
        $updatable = 0;
        if ($this->properties) {
            foreach ($this->properties as $property) {
                if ($property->getUpdatable())
                    $updatable++;
            }
        }
        return $updatable;
    }

    /**
     * @return string[]
     */
    public function getRequiredObjects() {
        return $this->requiredObjects;
    }


}
