<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 19/10/2018
 * Time: 12:24
 */

namespace Kinikit\MVC\Framework\API\Languages\NodeJS;

use KiniBook\ClientAPI\APIProvider;
use Kinikit\Core\Object\DynamicSerialisableObject;
use Kinikit\MVC\Framework\API\APIConfiguration;
use Kinikit\MVC\Framework\API\ClientLanguageConfiguration;
use Kinikit\MVC\Framework\API\Descriptor\APIController;
use Kinikit\MVC\Framework\API\Descriptor\APIMetaData;
use Kinikit\MVC\Framework\API\Descriptor\APIObject;

class NodeJSLanguageConfiguration extends ClientLanguageConfiguration {

    public function __construct($outputPath = null) {
        parent::__construct(APIConfiguration::CLIENT_NODEJS, ".ts", $outputPath);
    }

    /**
     * Flatten the output path removing namespaces.
     *
     * @param $outputPath
     * @param $model
     * @return mixed|void
     */
    public function rewriteFileOutputPath($outputPath, $model) {

        // If we can read a client source base path from the model, strip it.
        if ($model instanceof APIMetaData) {
            $outputPath = str_replace("/" . $model->getClientSourceBasePath(), "", $outputPath);
        } else if ($model instanceof APIController) {
            $outputPath = str_replace("/" . str_replace("\\", "/", $model->getRootNamespace()), "", $outputPath);
        }

        return $outputPath;

    }

    /**
     * Add language properties to API descriptor object
     *
     * @param string $objectClass
     * @param $object
     */
    public function addLanguagePropertiesToAPIDescriptorObject($objectClass, $object) {

        if ($objectClass == "APIController" || $objectClass == "APIControllerSummary") {
            $explodedNamespace = explode($object->getRootNamespace(), $object->getClientNamespace());
            $path = trim(str_replace("\\", "/", $explodedNamespace[1]), "/");
            $object->setJavascriptPathFromSource($path);

            $pathBackToSource = preg_replace("/[a-zA-Z0-9_]+/", "..", $path);
            $object->setJavascriptPathBackToSource($pathBackToSource);

        }

    }


}