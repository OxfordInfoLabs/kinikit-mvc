<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:22
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;


class APIController extends DynamicSerialisableObject {

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
    protected $className;


    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var APIMethod[]
     */
    protected $methods;


    /**
     * @var string[]
     */
    protected $requiredObjects;


    /**
     * @var string
     */
    protected $clientNamespace;


    /**
     * APIController constructor.
     * @param string $path
     * @param string $namespace
     * @param string $className
     * @param $title
     * @param null $comment
     * @param Kinikit\MVC\Framework\API\Descriptor\APIMethod[] $methods
     * @param string[] $requiredObjects
     */
    public function __construct($path = null, $namespace = null, $className = null, $title = null, $comment = null, $methods = array(), $requiredObjects = array(), $clientNamespace = null) {
        parent::__construct(false);

        $this->path = $path;
        $this->namespace = $namespace;
        $this->className = $className;
        $this->comment = $comment;
        $this->methods = $methods;
        $this->requiredObjects = $requiredObjects;
        $this->title = $title;
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
    public function getClassName() {
        return $this->className;
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
        return str_replace("\\", "/", $this->clientNamespace). "/" . $this->getClassName();
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title ? $this->title : $this->className;
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
     * @return APIMethod[]
     */
    public function getMethods() {
        return $this->methods;
    }

    /**
     * Get a method by name or return null
     *
     * @param string $name
     * @return APIMethod
     */
    public function getMethod($name) {
        foreach ($this->methods as $method) {
            if ($method->getName() == $name) {
                return $method;
            }
        }
        return null;
    }


    /**
     * @return string[]
     */
    public function getRequiredObjects() {
        return $this->requiredObjects;
    }


    public function getRequestPath() {
        return str_replace("/API", "", $this->path);
    }


}