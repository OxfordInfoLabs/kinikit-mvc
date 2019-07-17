<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:34
 */

namespace Kinikit\MVC\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;

class APIParam extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $name;


    /**
     * @var string
     */
    protected $type;


    /**
     * Description of the parameter
     *
     * @var string
     */
    protected $description;


    /**
     * @var boolean
     */
    protected $segmentParam;


    /**
     * @var boolean
     */
    protected $payloadParam;


    /**
     * @var integer
     */
    protected $index;


    /**
     * @var integer
     */
    protected $extraParamIndex;

    /**
     * @var mixed
     */
    protected $defaultValue;


    /**
     * Get the client type for this parameter
     *
     * @var string
     */
    protected $clientType;

    /**
     * APIParam constructor.
     * @param string $name
     * @param string $type
     * @param null $description
     * @param null $segmentParam
     * @param null $payloadParam
     * @param int $index
     * @param mixed $defaultValue
     */
    public function __construct($name = null, $type = null, $description = null, $segmentParam = null, $payloadParam = null, $index = null, $extraParamIndex = null, $defaultValue = null, $clientType = null) {
        parent::__construct(false);

        $this->name = $name;
        $this->type = $type;
        $this->segmentParam = $segmentParam;
        $this->payloadParam = $payloadParam;
        $this->index = $index;
        $this->extraParamIndex = $extraParamIndex;
        $this->defaultValue = $defaultValue;
        $this->description = $description;
        $this->clientType = $clientType;
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


    /**
     * Get the client type (rewritten namespaces)
     */
    public function getClientType() {
        return $this->clientType;
    }


    public function getShortType() {
        $explodedType = explode("\\", $this->type);
        return array_pop($explodedType);
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
    public function getDescription() {
        return $this->description;
    }


    /**
     * @return bool
     */
    public function getSegmentParam() {
        return $this->segmentParam;
    }

    /**
     * @return bool
     */
    public function getPayloadParam() {
        return $this->payloadParam;
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
    public function getExtraParamIndex() {
        return $this->extraParamIndex;
    }


    /**
     * @return mixed
     */
    public function getDefaultValue() {
        return $this->defaultValue;
    }

    /**
     * Return a boolean as to whether or not this param is optional
     */
    public function getIsOptional() {
        return $this->defaultValue !== "_UNSET";
    }


    /**
     * Return a boolean as to whether or not this is a string param.
     *
     * @return bool
     */
    public function getIsString() {
        return $this->type == "string";
    }


    /**
     * Return an indicator as to whether or not this is a primitive field
     *
     * @return bool
     */
    public function getIsPrimitive() {
        $primitives = array("integer", "int", "boolean", "bool", "float", "mixed", "string");
        return in_array($this->type, $primitives);
    }

}
