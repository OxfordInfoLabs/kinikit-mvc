<?php

namespace Kinikit\MVC\API;


use Kinikit\Core\Object\DynamicSerialisableObject;
use Kinikit\Core\Object\SerialisableObject;

/**
 * Language data for a client
 *
 *
 */
class ClientLanguageConfiguration extends SerialisableObject {

    /**
     * @var string
     */
    protected $language;


    /**
     * @var string
     */
    protected $fileExtension;


    /**
     * @var string
     */
    protected $outputPath;


    /**
     * Construct
     *
     * ClientLanguageConfiguration constructor.
     * @param string $language
     * @param string $fileExtension
     * @param string $outputPath
     */
    public function __construct($language = null, $fileExtension = null, $outputPath = null) {
        $this->language = $language;
        $this->fileExtension = $fileExtension;
        $this->outputPath = $outputPath;

    }


    /**
     * @return string
     */
    public function getLanguage() {
        return $this->language;
    }

    /**
     * @return string
     */
    public function getFileExtension() {
        return $this->fileExtension;
    }


    /**
     * @return string
     */
    public function getOutputPath() {
        return $this->outputPath;
    }


    /**
     * Overridable method for rewriting the output path if required (e.g. lowercasing in Java).
     *
     * @param $outputPath
     * @param $model
     * @return mixed
     */
    public function rewriteFileOutputPath($outputPath, $model) {
        return $outputPath;
    }


    /**
     * Add language specific properties to an api descriptor object.  This is run as part of the APIInfo
     * routine and allows language specific markup to be added dynamically to objects as required.
     *
     * The objectClass is added as convenience to allow for switch statements etc.
     *
     * The default implementation is blank
     *
     * @param $objectClass string
     * @param $apiObject DynamicSerialisableObject
     */
    public function addLanguagePropertiesToAPIDescriptorObject($objectClass, $object) {
    }


}
