<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 19/10/2018
 * Time: 12:24
 */

namespace Kinikit\MVC\API\Languages\NodeJS;

use KiniBook\ClientAPI\APIProvider;
use Kinikit\Core\Object\DynamicSerialisableObject;
use Kinikit\MVC\API\APIConfiguration;
use Kinikit\MVC\API\ClientLanguageConfiguration;
use Kinikit\MVC\API\Descriptor\APIController;
use Kinikit\MVC\API\Descriptor\APIMetaData;

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

        if ($objectClass == "APIController" || $objectClass == "APIControllerSummary" || $objectClass == "APIObject") {

            if ($objectClass != "APIObject") {
                $explodedNamespace = explode($object->getRootNamespace(), $object->getClientNamespace());
                $remainingNamespace = isset($explodedNamespace[1]) ? $explodedNamespace[1] : $explodedNamespace[0];
            } else {
                $remainingNamespace = $object->getClientNamespace();
            }

            $path = trim(str_replace("\\", "/", $remainingNamespace), "/");
            $object->setJavascriptPathFromSource($path);

            $pathBackToSource = preg_replace("/[a-zA-Z0-9_]+/", "..", $path);
            $object->setJavascriptPathBackToSource($pathBackToSource);

            $javascriptImports = array();
            if ($object->getRequiredObjects()) {
                foreach ($object->getRequiredObjects() as $requiredObject) {
                    $explodedObject = explode("\\", $requiredObject);
                    $className = array_pop($explodedObject);
                    if (!strpos($className, "Exception"))
                        $javascriptImports[] = "import { $className } from \"$pathBackToSource" . join("/", $explodedObject) . "/" . $className . '"';
                }
            }

            $object->setJavascriptImports($javascriptImports);


        }
        if ($objectClass == "APIParam" || $objectClass == "APIProperty") {
            $object->setJavascriptType($this->convertToJavascriptType($object->getShortType()));
        }

        if ($objectClass == "APIMethod") {

            // Convert the request path to java version
            $javascriptRequestPath = preg_replace("/\\{([a-zA-Z0-9_]+)}/", '" + $1 + "', $object->getRequestPath());
            $object->setJavascriptRequestPath($javascriptRequestPath);

            $object->setJavascriptReturnType($this->convertToJavascriptType($object->getShortReturnType()));
            $object->setJavascriptReturnTypeClass($this->convertToJavascriptType($object->getShortReturnType(), true));
        }

    }


    public function convertToJavascriptType($phpType, $stripArrays = false) {


        switch ($phpType) {
            case "integer":
            case "int":
            case "float":
                $javascriptType = "number";
                break;
            case "bool":
                $javascriptType = "boolean";
                break;
            case "mixed":
                $javascriptType = "any";
                break;
            default:
                $javascriptType = $phpType;
        }

        if ($stripArrays)
            return str_replace(array("[", "]"), array("", ""), $javascriptType);

        else
            return $javascriptType;

    }


}
