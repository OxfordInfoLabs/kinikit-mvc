<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 17/10/2018
 * Time: 12:28
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;


class APIMethod extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $comment;

    /**
     * @var string
     */
    protected $httpMethod;

    /**
     * @var string
     */
    protected $requestPath;

    /**
     * @var string
     */
    protected $returnType;


    /**
     * @var string
     */
    protected $returnDescription;

    /**
     * @var \Kinikit\MVC\Framework\API\Descriptor\APIParam[]
     */
    protected $params = array();


    /**
     * @var string
     */
    protected $clientReturnType;


    /**
     * @var \Kinikit\MVC\Framework\API\Descriptor\APIMethodException[]
     */
    protected $exceptions;


    /**
     * @var integer
     */
    protected $rateLimit;

    /**
     * @var float
     */
    protected $rateLimitMultiplier;


    /**
     * @var integer
     */
    protected $rateLimitPeriod;

    /**
     * APIMethod constructor.
     * @param $name
     * @param $comment
     * @param $httpMethod
     * @param $requestPath
     * @param $returnType
     * @param null $returnDescription
     * @param array $params
     */
    public function __construct($name = null, $comment = null, $httpMethod = null, $requestPath = null, $returnType = null, $returnDescription = null, $params = array(), $clientReturnType = null, $exceptions = null, $rateLimit = null, $rateLimitMultiplier = null, $rateLimitPeriod = null) {

        parent::__construct(false);

        $this->name = $name;
        $this->comment = $comment;
        $this->httpMethod = $httpMethod;
        $this->requestPath = $requestPath;
        $this->returnType = $returnType;
        $this->params = $params;
        $this->returnDescription = $returnDescription;
        $this->clientReturnType = $clientReturnType;
        $this->exceptions = $exceptions;
        $this->rateLimit = $rateLimit;
        $this->rateLimitMultiplier = $rateLimitMultiplier;
        $this->rateLimitPeriod = $rateLimitPeriod;
    }


    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
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
     * @return mixed
     */
    public function getHttpMethod() {
        return $this->httpMethod;
    }

    /**
     * @return mixed
     */
    public function getRequestPath() {
        return $this->requestPath;
    }

    /**
     * @return mixed
     */
    public function getReturnType() {
        return $this->returnType;
    }


    public function getShortReturnType() {
        $explodedType = explode("\\", $this->returnType);
        return array_pop($explodedType);
    }

    /**
     * Get the type path if a class
     */
    public function getReturnTypePath() {
        $type = preg_replace("/\[.*?\]/", "", $this->returnType);
        $pathRewrite = str_replace("\\", "/", $type);
        return $pathRewrite != $type ? "/" . ltrim($pathRewrite, "/") : null;
    }

    /**
     * Get the relative return type path with / removed from front
     *
     * @return string
     */
    public function getRelativeReturnTypePath() {
        return trim($this->getReturnTypePath(), "/");
    }


    /**
     * @return string
     */
    public function getClientReturnType() {
        return $this->clientReturnType;
    }


    /**
     * Return indicator as to whether or not the return type is primitive.
     *
     * @return bool
     */
    public function getIsReturnTypePrimitive() {
        $primitives = array("integer", "int", "boolean", "bool", "float", "mixed", "string");
        return in_array($this->returnType, $primitives);
    }

    /**
     * @return string
     */
    public function getReturnDescription() {
        return $this->returnDescription;
    }


    /**
     * @return array
     */
    public function getParams() {
        return $this->params;
    }


    /**
     * Get the payload param if one defined
     */
    public function getPayloadParam() {
        if ($this->params) {
            foreach ($this->params as $param) {
                if ($param->getPayloadParam()) {
                    return $param;
                }
            }
        }
        return null;
    }

    /**
     * @return APIMethodException[]
     */
    public function getExceptions() {
        return $this->exceptions;
    }

    /**
     * @return string
     */
    public function getRateLimit() {
        return $this->rateLimit;
    }

    /**
     * @return float
     */
    public function getRateLimitMultiplier() {
        return $this->rateLimitMultiplier;
    }

    public function getRateLimitMultiplierIsOne() {
        return $this->rateLimitMultiplier === 1;
    }

    /**
     * @return int
     */
    public function getRateLimitPeriod() {
        return $this->rateLimitPeriod;
    }


}