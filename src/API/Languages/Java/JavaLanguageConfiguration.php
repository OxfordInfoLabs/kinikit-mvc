<?php

namespace Kinikit\MVC\API\Languages\Java;

use Kinikit\MVC\API\APIConfiguration;
use Kinikit\MVC\API\ClientLanguageConfiguration;

/**
 * Java specific language configuration.
 *
 * Class JavaLanguageConfiguration
 * @package Kinikit\MVC\API\Languages\Java
 */
class JavaLanguageConfiguration extends ClientLanguageConfiguration {

    public function __construct($outputPath = null) {
        parent::__construct(APIConfiguration::CLIENT_JAVA, ".java", $outputPath);
    }

    public function rewriteFileOutputPath($outputPath, $model) {
        return str_replace(dirname($outputPath), strtolower(dirname($outputPath)), $outputPath);
    }


    /**
     * Add java specific language properties
     *
     * @param string $objectClass
     * @param $object
     */
    public function addLanguagePropertiesToAPIDescriptorObject($objectClass, $object) {

        if ($objectClass == "APIParam" || $objectClass == "APIProperty") {
            $object->setJavaType($this->convertToJavaType($object->getClientType()));
            $object->setJavaHTMLType(str_replace(array("<", ">"), array("&lt;", "&gt;"), $object->getJavaType()));
        }

        if ($objectClass == "APIMethod") {

            if ($object->getReturnType()) {
                $object->setReturnJavaType($this->convertToJavaType($object->getClientReturnType()));
                $object->setReturnJavaHTMLType(str_replace(array("<", ">"), array("&lt;", "&gt;"), $object->getReturnJavaType()));
                $object->setReturnJavaGenericType(preg_replace("/<.*>/", "", $object->getReturnJavaType()));
            }


            // Convert the request path to java version
            $javaRequestPath = preg_replace("/\\{([a-zA-Z0-9_]+)}/", '" + $1 + "', $object->getRequestPath());
            $object->setJavaRequestPath($javaRequestPath);
        }

        if ($objectClass == "APIMethodException") {

            $explodedClientType = explode("\\", $object->getClientType());
            $className = array_pop($explodedClientType);
            $package = join("\\", $explodedClientType);

            $object->setJavaClientType(strtolower($this->namespaceToPackage($package)) . "." . $className);

        }


        if ($objectClass == "APIObject" || $objectClass == "APIController") {

            $requiredObjects = $object->getRequiredObjects();
            if ($requiredObjects) {
                $imports = array();
                foreach ($requiredObjects as $requiredObject) {
                    $package = explode(".", $this->namespaceToPackage($requiredObject));
                    $className = array_pop($package);
                    $imports[] = strtolower(join(".", $package)) . "." . $className;
                }
                $object->setJavaImports($imports);
            }

            $object->setRootJavaPackage(strtolower($this->namespaceToPackage($object->getRootNamespace())));

        }

        if ($object->getClientNamespace()) {
            $object->setJavaPackage(strtolower($this->namespaceToPackage($object->getClientNamespace())));
        }


    }


    // Convert a namespace to a package.
    private function namespaceToPackage($namespace) {
        return str_replace("\\", ".", trim($namespace, "\ "));
    }

    // Convert a php type to java type
    private function convertToJavaType($phpType) {

        $map = false;
        $array = false;

        $singleType = $phpType;

        preg_match_all("/\[.*?\]/", $phpType, $matches);
        $arraysRemovedType = preg_replace("/\[.*\]/", "", $phpType);
        $matches = $matches[0];
        if (sizeof($matches) > 0) {
            $trimmedMatch = trim($matches[0], "[]");
            if ($trimmedMatch) {
                $map = true;
                $mapKeyType = $this->convertToJavaType($trimmedMatch);
            } else {
                $array = true;
            }

            array_shift($matches);
            $singleType = $this->convertToJavaType($arraysRemovedType . join("", $matches));

        }

        $javaType = null;

        switch ($singleType) {
            case "integer":
            case "int":
                $javaType = "Integer";
                break;
            case "boolean":
                $javaType = "Boolean";
                break;
            case "float":
                $javaType = "Float";
                break;
            case "mixed":
                $javaType = "Object";
                break;
            case "string":
                $javaType = "String";
                break;
            default:
                $javaType = explode("\\", $singleType);
                $javaType = array_pop($javaType);
        }

        if ($map) {
            return "Map<$mapKeyType,$javaType>";
        } else if ($array) {
            return $javaType . "[]";
        } else {
            return $javaType;
        }


    }


}
