<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 30/10/2018
 * Time: 16:59
 */

namespace Kinikit\MVC\Framework\API\Descriptor;


use Kinikit\Core\Object\DynamicSerialisableObject;


class APIMethodException extends DynamicSerialisableObject {

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $clientType;


    /**
     * Construct with a type and a client type
     *
     * APIMethodException constructor.
     * @param null $type
     * @param $clientType
     */
    public function __construct($type = null, $clientType = null) {

        parent::__construct(false);

        $this->type = $type;
        $this->clientType = $clientType;
    }


    /**
     * @return string
     */
    public function getType() {
        return str_replace("\\", "\\\\", $this->type);
    }


    /**
     * Get the path
     *
     * @return string
     */
    public function getPath() {
        return str_replace("\\", "/", $this->type);
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
    public function getShortType() {
        $explodedType = explode("\\", $this->type);
        return array_pop($explodedType);
    }


}