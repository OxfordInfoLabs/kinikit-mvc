<?php

namespace Kinikit\MVC\API\Descriptor;

use Kinikit\Core\Util\Annotation\ClassAnnotationParser;
use Kinikit\Core\Util\Annotation\ClassAnnotations;
use Kinikit\Core\Util\Logging\Logger;
use Kinikit\Core\Util\ObjectArrayUtils;
use Kinikit\MVC\API\APIConfiguration;
use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\RateLimiter\RateLimiterEvaluator;
use Kinikit\MVC\Framework\SourceBaseManager;

/**
 * Descriptor for an API controller
 *
 * Class APIController
 * @package Kinikit\MVC\API\Descriptor
 */
class APIInfo {

    /**
     * @var APIController[string]
     */
    private $apiControllers = null;

    /**
     * @var APIObject[string]
     */
    private $apiObjects = array();

    /**
     * @var APIObject[string]
     */
    private $apiExceptions = array();


    /**
     * @var APIConfiguration
     */
    private $apiConfiguration;


    /**
     * Construct with configuration
     *
     * APIInfo constructor
     * @param $apiConfiguration APIConfiguration
     */
    public function __construct($apiConfiguration) {
        $this->apiConfiguration = $apiConfiguration;
    }


    /**
     * Get meta data object for this api info.
     *
     * @return APIMetaData
     */
    public function getMetaData() {
        $generatedClients = $this->apiConfiguration->getGeneratedClients();
        $availableClients = array();
        foreach ($generatedClients as $client => $path) {
            $availableClients[$client] = 1;
        }

        return $this->addLanguageSpecificProperties(new APIMetaData($this->apiConfiguration->getIdentifier(), $this->apiConfiguration->getTitle(), $this->getAllAPIControllerSummaryInfo(), $this->getAllAPIObjectSummaryInfo(), $this->getAllAPIExceptionSummaryInfo(), $this->apiConfiguration->getGlobalAPIParams(), $this->getClientNamespace(Configuration::readParameter("application.namespace")), $availableClients));
    }

    /**
     * Get a single controller by path
     *
     * @param $path
     * @return APIController
     */
    public function getAPIControllerByPath($path) {

        $this->loadAPIInfo();
        return isset($this->apiControllers[$path]) ? $this->apiControllers[$path] : null;
    }


    /**
     * Get summary info for all controllers
     *
     * @return APIControllerSummary[]
     */
    public function getAllAPIControllerSummaryInfo() {

        $this->loadAPIInfo();


        $summaries = array();
        foreach ($this->apiControllers as $path => $controller) {
            $methodNames = ObjectArrayUtils::getMemberValueArrayForObjects("name", $controller->getMethods());
            $summaries[] = $this->addLanguageSpecificProperties(new APIControllerSummary($path, $controller->getNamespace(), $controller->getClassName(), $controller->getTitle(), $methodNames, $controller->getClientNamespace()));
        }

        return $summaries;

    }


    /**
     * Get an API object by name.
     *
     * @param $name
     * @return APIObject
     */
    public function getAPIObjectByPath($path) {
        $this->loadAPIInfo();
        return isset($this->apiObjects[$path]) ? $this->apiObjects[$path] : null;
    }


    /**
     * Get all API Object Summmary info.
     *
     * @return APIObjectSummary[]
     */
    public function getAllAPIObjectSummaryInfo() {

        $this->loadAPIInfo();
        $summaries = array();
        foreach ($this->apiObjects as $path => $object) {
            $summaries[] = $this->addLanguageSpecificProperties(new APIObjectSummary($path, $object->getName()));
        }

        return $summaries;

    }


    /**
     * Get an API Exception by path
     *
     * @param $path
     * return APIException
     */
    public function getAPIExceptionByPath($path) {
        $this->loadAPIInfo();
        return isset($this->apiExceptions[$path]) ? $this->apiExceptions[$path] : null;
    }


    /**
     * Get all API Exception summary info.
     *
     * @return APIObjectSummary[]
     */
    public function getAllAPIExceptionSummaryInfo() {
        $this->loadAPIInfo();
        $summaries = array();
        foreach ($this->apiExceptions as $path => $object) {
            $summaries[] = $this->addLanguageSpecificProperties(new APIObjectSummary($path, $object->getName()));
        }

        return $summaries;
    }


    /**
     * Load the controllers
     *
     * Load the API controllers
     */
    private function loadAPIInfo() {
        if (!$this->apiControllers) {

            $this->apiControllers = array();
            $this->apiObjects = array();

            $applicationNamespaces = SourceBaseManager::instance()->getApplicationNamespaces();

            foreach ($applicationNamespaces as $applicationNamespace) {

                $apiControllerData = $this->readAPIControllerDataForDirectory("Controllers", $applicationNamespace);

                /**
                 * @var $controllerAnnotations ClassAnnotations
                 * @var $controllerReflectionClass \ReflectionClass
                 */
                foreach ($apiControllerData as $path => list($controllerAnnotations, $controllerReflectionClass)) {

                    // Get the short name.
                    $controllerClass = $controllerReflectionClass->getShortName();
                    $controllerPath = str_replace("Controllers/", "", $path);

                    $controllerApiNamespace = $controllerReflectionClass->getNamespaceName();

                    $controllerComment = $controllerAnnotations->getClassAnnotationForMatchingTag("comment") ?
                        $controllerAnnotations->getClassAnnotationForMatchingTag("comment")->getValue() : null;

                    $controllerTitle = $controllerAnnotations->getClassAnnotationForMatchingTag("title") ? $controllerAnnotations->getClassAnnotationForMatchingTag("title")->getValue() : null;


                    $requiredObjects = array();

                    /**
                     * Loop through the methods for the controller
                     *
                     * @var $reflectionMethod \ReflectionMethod
                     *
                     */
                    $controllerMethods = array();
                    foreach ($controllerReflectionClass->getMethods() as $reflectionMethod) {

                        if (!$reflectionMethod->isPublic() || $reflectionMethod->getDeclaringClass() != $controllerReflectionClass || $reflectionMethod->getName() == "__construct")
                            continue;

                        $annotations = $controllerAnnotations->getMethodAnnotations()[$reflectionMethod->getName()];

                        $comment = isset($annotations["comment"]) ? $annotations["comment"][0]->getValue() : null;
                        $http = isset($annotations["http"]) ? $annotations["http"][0]->getValue() : null;
                        $segmentParams = array();
                        $payloadIndex = -1;
                        if ($http) {
                            $exploded = explode(" ", $http);
                            $httpMethod = trim($exploded[0]);

                            if (sizeof($exploded) > 1) {
                                preg_match_all("/\\$[a-zA-Z0-9_]+/", $exploded[1], $matchedParams);
                                foreach ($matchedParams[0] as $segmentParam) {
                                    $segmentParams[ltrim($segmentParam, "$")] = 1;
                                }

                                $requestPath = trim($exploded[1], "/ ");
                                $requestPath = preg_replace("/\\$([a-zA-Z0-9_]+)/", "{" . "$1" . "}", $requestPath);
                            } else {
                                $requestPath = "";
                            }

                            if ($httpMethod != "GET") {
                                $payloadIndex = sizeof($segmentParams);
                            }


                        } else {
                            $httpMethod = "POST";
                            $requestPath = $reflectionMethod->getName();
                        }


                        $params = array();
                        $extraParamIndex = 0;
                        if (isset($annotations["param"])) {
                            foreach ($annotations["param"] as $paramIndex => $parameterAnnotation) {

                                $reflectionParam = $reflectionMethod->getParameters()[$paramIndex];

                                $parameterLine = $parameterAnnotation->getValue();
                                $parameterWords = explode(" ", $parameterLine);
                                $paramName = ltrim(is_numeric(strpos($parameterWords[0], "$")) ? $parameterWords[0] : (sizeof($parameterWords) > 1 ? $parameterWords[1] : null), "$");
                                $paramType = is_numeric(strpos($parameterWords[0], "$")) ? (sizeof($parameterWords) > 1 ? $parameterWords[1] : null) : $parameterWords[0];

                                $paramDescription = sizeof($parameterWords) > 2 ? join(" ", array_slice($parameterWords, 2)) : "";

                                if (is_numeric(strpos($paramType, "\\"))) {
                                    $paramType = $this->processAPIObject($paramType, $this->apiObjects);
                                    $requiredObjects[$this->stripArrayExtension($this->getClientNamespace($paramType))] = 1;
                                }


                                $params[] = $this->addLanguageSpecificProperties(new APIParam($paramName, $paramType, $paramDescription, isset($segmentParams[$paramName]), $paramIndex == $payloadIndex, $paramIndex, $extraParamIndex, $reflectionParam->isOptional() ? $reflectionParam->getDefaultValue() : "_UNSET", $this->getClientNamespace($paramType)));

                                if (!isset($segmentParams[$paramName]) && ($paramIndex != $payloadIndex)) {
                                    $extraParamIndex++;
                                }


                            }

                        }


                        $returnType = null;
                        if (isset($annotations["return"])) {
                            $returnType = $annotations["return"][0]->getValue();

                            $returnTypeWords = explode(" ", $returnType);
                            $returnType = $returnTypeWords[0];
                            $returnDescription = sizeof($returnTypeWords) > 1 ? join(" ", array_slice($returnTypeWords, 1)) : "";


                            if (is_numeric(strpos($returnType, "\\"))) {
                                $returnType = $this->processAPIObject($returnType, $this->apiObjects);
                                $requiredObjects[$this->stripArrayExtension($this->getClientNamespace($returnType))] = 1;
                            }

                        }


                        $exceptions = array();

                        if (isset($annotations["throws"])) {

                            foreach ($annotations["throws"] as $exceptionAnnotation) {

                                $exceptionClass = trim($exceptionAnnotation->getValue());
                                $exceptionClass = $this->processAPIObject($exceptionClass, $this->apiExceptions);
                                $requiredObjects[$this->stripArrayExtension($this->getClientNamespace($exceptionClass))] = 1;

                                $exceptions[] = $this->addLanguageSpecificProperties(new APIMethodException($exceptionClass, $this->getClientNamespace($exceptionClass)));
                            }
                        }


                        list($limit, $limitMultiplier, $limitPeriod) = RateLimiterEvaluator::instance()->getRateLimitsForControllerMethod($controllerReflectionClass->getName(), $reflectionMethod->getName(), $controllerAnnotations);

                        if ($limitPeriod !== null) {
                            $exceptionClass = $this->processAPIObject("\Kinikit\MVC\Exception\RateLimitExceededException", $this->apiExceptions);
                            $requiredObjects[$this->stripArrayExtension($this->getClientNamespace($exceptionClass))] = 1;
                            $exceptions[] = $this->addLanguageSpecificProperties(new APIMethodException($exceptionClass, $this->getClientNamespace($exceptionClass)));
                        }


                        $controllerMethods[] = $this->addLanguageSpecificProperties(new APIMethod($reflectionMethod->getName(), $comment, $httpMethod, $requestPath, $returnType, isset($returnDescription) ? $returnDescription : null, $params, $this->getClientNamespace($returnType), $exceptions, $limit, $limitMultiplier, $limitPeriod));

                    }


                    // Add the api controller to the stack
                    $this->apiControllers[$controllerPath] = $this->addLanguageSpecificProperties(new APIController($controllerPath, $controllerApiNamespace, $controllerClass, $controllerTitle, $controllerComment, $controllerMethods, array_keys($requiredObjects), $this->getClientNamespace($controllerApiNamespace)));
                }

            }
        }
    }


    // Process an API object from it's php class name.
    private function processAPIObject($objectClassName, &$targetContainer) {

        preg_match("/\[.*]/", $objectClassName, $matches);

        $extension = "";
        if (sizeof($matches) > 0) {
            $extension = $matches[0];
        }
        $trimmedClassName = str_replace($extension, "", $objectClassName);

        if (isset($targetContainer[$trimmedClassName]))
            return;


        $annotations = ClassAnnotationParser::instance()->parse($trimmedClassName);

        $classComment = $annotations->getClassAnnotationForMatchingTag("comment") ? $annotations->getClassAnnotationForMatchingTag("comment")->getValue() : null;

        $varAnnotations = $annotations->getFieldAnnotationsForMatchingTag("var");
        $commentAnnotations = $annotations->getFieldAnnotationsForMatchingTag("comment");
        $validationAnnotations = $annotations->getFieldAnnotationsForMatchingTag("validation");

        $reflectionClass = new \ReflectionClass($trimmedClassName);
        $namespace = $reflectionClass->getNamespaceName();
        $path = trim(str_replace("\\", "/", $trimmedClassName), "/");

        $properties = array();
        $imports = array();
        $index = 0;
        $updatableIndex = 0;


        // Set up ordered properties arrays
        $propertiesByClass = array();
        $hierarchyClass = $reflectionClass;
        while ($hierarchyClass && !$hierarchyClass->isInternal()) {
            $propertiesByClass[$hierarchyClass->getName()] = array();
            $hierarchyClass = $hierarchyClass->getParentClass();
        }

        // Firstly order all properties according to the class hierarchy.
        foreach ($reflectionClass->getProperties() as $property) {
            $propertiesByClass[$property->getDeclaringClass()->getName()][] = $property;
        }

        $orderedProperties = array();
        foreach ($propertiesByClass as $classProperties) {
            $orderedProperties = array_merge($classProperties, $orderedProperties);
        }


        foreach ($orderedProperties as $property) {
            if ($property->isStatic())
                continue;


            /**
             * If we have at least a getter or we are public, we need the propery
             */
            if ($property->isPublic() || $reflectionClass->hasMethod("get" . $property->getName())) {

                $varType = isset($varAnnotations[$property->getName()]) ? $varAnnotations[$property->getName()][0]->getValue() : "string";

                $explodedVarType = explode(" ", $varType);
                $varType = $explodedVarType[0];
                $description = sizeof($explodedVarType) > 1 ? join(" ", array_slice($explodedVarType, 1)) : "";


                preg_match("/[A-Za-z0-9\\\]*\\\[A-Za-z0-9\\\\[\]]*/", $varType, $matches);
                if (sizeof($matches) > 0) {

                    if (preg_replace("/\[.*]/", "", $matches[0]) != $trimmedClassName) {
                        $varType = $this->processAPIObject($matches[0], $this->apiObjects);
                        $imports[$this->stripArrayExtension($this->getClientNamespace($varType))] = 1;
                    }
                }

                $updatable = $reflectionClass->hasMethod("set" . $property->getName());
                $comment = isset($commentAnnotations[$property->getName()]) ? $commentAnnotations[$property->getName()][0]->getValue() : null;

                $inherited = $property->getDeclaringClass() != $reflectionClass;

                $validations = isset($validationAnnotations[$property->getName()]) ? $validationAnnotations[$property->getName()][0]->getValues() : array();
                $required = in_array("required", $validations);

                $defaultValue = $reflectionClass->getDefaultProperties()[$property->getName()];

                $properties[] = $this->addLanguageSpecificProperties(new APIProperty($property->getName(), $varType, $description, $comment, $updatable, $index, $updatableIndex, $required, $this->getClientNamespace($varType), $inherited, $defaultValue));

                if ($updatable) $updatableIndex++;

                $index++;

            }

        }

        $targetContainer[$path] = $this->addLanguageSpecificProperties(new APIObject($path, $namespace, $reflectionClass->getShortName(), $classComment, $properties, array_keys($imports), $this->getClientNamespace($namespace)));

        return $trimmedClassName . $extension;

    }


    // Read API controllers for directory
    private function readAPIControllerDataForDirectory($directory, $applicationNamespace) {

        $controllerData = array();

        $currentNamespace = $applicationNamespace . "\\" . str_replace("/", "\\", $directory);
        $parser = ClassAnnotationParser::instance();

        $iterator = new \DirectoryIterator($directory);
        foreach ($iterator as $file) {
            if ($file->isDot())
                continue;

            if ($file->isDir()) {
                $controllerData = array_merge($controllerData, $this->readAPIControllerDataForDirectory($directory . "/" . $file->getFilename(), $applicationNamespace));
                continue;
            }

            $explodedFilename = explode(".", $file->getFilename());
            $className = array_shift($explodedFilename);


            // Attempt to construct the class annotations for this controller
            if (class_exists($currentNamespace . "\\" . $className)) {

                $reflectionClass = new \ReflectionClass($currentNamespace . "\\" . $className);
                $annotations = $parser->parse($currentNamespace . "\\" . $className);

                $selectorTag = $this->apiConfiguration->getAnnotationSelector();
                $explodedSelector = explode(" ", $selectorTag);
                $tag = trim($explodedSelector[0], " @");
                $value = isset($explodedSelector[1]) ? trim($explodedSelector[1]) : null;

                if ($annotations->getClassAnnotationForMatchingTag($tag) && (!$value || $annotations->getClassAnnotationForMatchingTag($tag)->getValue() == $value)) {
                    $controllerData[$directory . "/" . $className] = array($annotations, $reflectionClass);
                }
            }


        }

        return $controllerData;


    }


    // Rewrite the namespace for a source namespace to the client version
    private function getClientNamespace($namespace) {
        $clientNamespaceRewrites = $this->apiConfiguration->getClientNamespaceMappings();
        return str_replace(array_keys($clientNamespaceRewrites), array_values($clientNamespaceRewrites), $namespace);
    }


    // Remove an array extension from a passed identifier.
    private function stripArrayExtension($identifier) {
        return preg_replace("/\[.*]/", "", $identifier);
    }


    // Add Language specific properties to a mapped object for all installed languages
    private function addLanguageSpecificProperties($object) {

        if (sizeof($this->apiConfiguration->getGeneratedClientConfiguration()) > 0) {
            $rootNamespace = $this->getClientNamespace(Configuration::readParameter("application.namespace"));
            $object->setRootNamespace($rootNamespace);

            // Add language specific properties for all installed languages.
            foreach ($this->apiConfiguration->getGeneratedClientConfiguration() as $configuration) {
                $objectClass = explode("\\", get_class($object));
                $objectClass = array_pop($objectClass);
                $configuration->addLanguagePropertiesToAPIDescriptorObject($objectClass, $object);
            }

        }

        return $object;

    }


}
