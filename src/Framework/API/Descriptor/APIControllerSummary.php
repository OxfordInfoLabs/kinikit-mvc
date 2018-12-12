<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:22
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;

class APIControllerSummary extends DynamicSerialisableObject {

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
     * @var string[]
     */
    protected $methodNames;


    /**
     * Client namespace
     *
     * @var string
     */
    protected $clientNamespace;


    /**
     * APIControllerSummary constructor.
     * @param string $path
     * @param string $namespace
     * @param string $className
     * @param null $title
     * @param string[] $methodNames
     */
    public function __construct($path = null, $namespace = null, $className = null, $title = null, $methodNames = null, $clientNamespace = null) {

        parent::__construct(false);

        $this->path = $path;
        $this->namespace = $namespace;
        $this->className = $className;
        $this->methodNames = $methodNames;
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
     * @return string
     */
    public function getClassName() {
        return $this->className;
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }


    /**
     * @return string[]
     */
    public function getMethodNames() {
        return $this->methodNames;
    }


    public function getRequestPath() {
        return str_replace("API/", "", $this->path);
    }

}