<?php


namespace Kinikit\MVC\Response;

use Kinikit\Core\Binding\ObjectBinder;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Serialisation\JSON\ObjectToJSONConverter;
use Kinikit\MVC\ContentSource\StringContentSource;

/**
 * Extension of a simple response which quite simply converts a JSON object into a
 * string before returning it with appropriate content types etc.
 *
 * Class JSONResponse
 * @package Kinikit\MVC\Response
 */
class JSONResponse extends SimpleResponse {

    private $object;

    /**
     * Create a json response - this takes a mixed object to convert to JSON format and an optional
     * response code and content type if required for overload.
     *
     * JSONResponse constructor.
     *
     * @param mixed $jsonObject
     * @param int $responseCode
     * @param string $contentType
     */
    public function __construct($jsonObject, $responseCode = 200, $contentType = "application/json") {
        $this->object = $jsonObject;
        $converter = Container::instance()->get(ObjectToJSONConverter::class);
        parent::__construct(new StringContentSource($converter->convert($jsonObject), $contentType), $responseCode);
    }

    /**
     * Get the object used to construct this
     *
     * @return mixed
     */
    public function getObject() {
        return $this->object;
    }


}
