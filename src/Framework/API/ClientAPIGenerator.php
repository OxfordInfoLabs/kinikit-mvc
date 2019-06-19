<?php

namespace Kinikit\MVC\Framework\API;

use Kinikit\Core\Configuration;
use Kinikit\Core\Init;
use Kinikit\Core\Object\SerialisableObject;
use Kinikit\Core\Template\Parser\MustacheTemplateParser;
use Kinikit\Core\Util\Annotation\ClassAnnotationParser;
use Kinikit\Core\Util\FileUtils;
use Kinikit\MVC\Framework\API\Descriptor\APIInfo;
use Kinikit\MVC\Framework\API\Languages\Java\JavaAPIGenerator;
use Kinikit\MVC\Framework\API\Languages\PHP\PHPAPIGenerator;


if (file_exists("../vendor"))
    include_once "../vendor/autoload.php";

/**
 * Client API generator - generates PHP and Java API skeletons for client API access
 *
 * Class APIGenerator
 */
class ClientAPIGenerator {

    private $templateParser;

    public function __construct() {
        $this->templateParser = new MustacheTemplateParser();
    }


    // Run the client API generator from composer.
    public static function runFromComposer($event) {

        new Init();

        $sourceDirectory = $event && isset($event->getComposer()->getPackage()->getConfig()["source-directory"]) ?
            $event->getComposer()->getPackage()->getConfig()["source-directory"] : ".";

        chdir($sourceDirectory);

        $generator = new ClientAPIGenerator();
        $generator->generate();
    }


    /**
     * Main entry point.  Loops through configurations generating as appropriate
     */
    public function generate() {

        // Loop through all defined API configurations and make Client APIs where applicable
        $configurations = APIConfiguration::getAPIConfigs();


        foreach ($configurations as $configuration) {

            if ($configuration->getGeneratedClients()) {
                $this->generateAPI($configuration);
            }

        }

    }

    /**
     * @param $configuration APIConfiguration
     */
    private function generateAPI($configuration) {

        // Grab the info object
        $apiInfo = new APIInfo($configuration);


        // Now loop through each client language we are building for
        foreach ($configuration->getGeneratedClientConfiguration() as $clientLanguageConfiguration) {

            $outputPath = $clientLanguageConfiguration->getOutputPath();
            $language = $clientLanguageConfiguration->getLanguage();
            $fileExtension = $clientLanguageConfiguration->getFileExtension();

            // Clean the source tree
            if (file_exists("$outputPath/src"))
                FileUtils::deleteDirectory("$outputPath/src");


            $basePath = $outputPath . "/src";

            // Generate each controller
            foreach ($apiInfo->getAllAPIControllerSummaryInfo() as $info) {

                $controller = $apiInfo->getAPIControllerByPath($info->getPath());
                $this->processTemplate(__DIR__ . "/Languages/$language/templates/APIClass.txt", $controller, $basePath . "/" . $controller->getClientPath() . $fileExtension, $clientLanguageConfiguration);

            }

            // Generate each object
            foreach ($apiInfo->getAllAPIObjectSummaryInfo() as $info) {

                $object = $apiInfo->getAPIObjectByPath($info->getPath());


                $this->processTemplate(__DIR__ . "/Languages/$language/templates/APIObject.txt", $object, $basePath . "/" . $object->getClientPath() . $fileExtension, $clientLanguageConfiguration);
            }


            foreach ($apiInfo->getAllAPIExceptionSummaryInfo() as $info) {

                $exception = $apiInfo->getAPIExceptionByPath($info->getPath());

                $this->processTemplate(__DIR__ . "/Languages/$language/templates/APIException.txt", $exception, $basePath . "/" . $exception->getClientPath() . $fileExtension, $clientLanguageConfiguration);
            }


            // Generate the API Wrapper
            $sourceBase = "$basePath/" . $apiInfo->getMetaData()->getClientSourceBasePath();

            $this->processTemplate(__DIR__ . "/Languages/$language/templates/APIProvider.txt", $apiInfo->getMetaData(), $sourceBase . "/APIProvider$fileExtension", $clientLanguageConfiguration);


            // Process any extra src templates
            if (file_exists(__DIR__ . "/Languages/$language/templates/src")) {
                $this->processTemplatesRecursively(__DIR__ . "/Languages/$language/templates/src", $sourceBase, $apiInfo->getMetaData(), $fileExtension, $clientLanguageConfiguration);
            }


            // Process any extra root templates
            if (file_exists(__DIR__ . "/Languages/$language/templates/root")) {
                $this->processTemplatesRecursively(__DIR__ . "/Languages/$language/templates/root", $outputPath, $apiInfo->getMetaData(), $fileExtension, $clientLanguageConfiguration);
            }


            // Now copy any static files straight to base path
            if (file_exists(__DIR__ . "/Languages/$language/static/root")) {
                FileUtils::copy(__DIR__ . "/Languages/$language/static/root", $outputPath);
            }

            if (file_exists(__DIR__ . "/Languages/$language/static/src")) {
                FileUtils::copy(__DIR__ . "/Languages/$language/static/src", $sourceBase);
            }

            if (file_exists(__DIR__ . "/Languages/$language/example")) {
                FileUtils::copy(__DIR__ . "/Languages/$language/example", $outputPath, false);
            }

        }


    }


    // Process the templates in a directory recursively, following the same tree down in the output directory
    private function processTemplatesRecursively($directory, $outputDirectory, $model, $fileExtension, $clientLanguageConfiguration) {

        $dir = opendir($directory);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($directory . '/' . $file)) {
                    $this->processTemplatesRecursively($directory . "/" . $file, $outputDirectory . "/" . $file, $model, $fileExtension, $clientLanguageConfiguration);
                } else {
                    $outputFile = str_replace(".txt", $fileExtension, $file);
                    $this->processTemplate($directory . "/" . $file, $model, $outputDirectory . "/" . $outputFile, $clientLanguageConfiguration);
                }
            }
        }

    }

    // Process a template using Mustache and write it out to a file.
    private function processTemplate($templatePath, $model, $outputPath, $clientLanguageConfiguration) {


        if ($model instanceof SerialisableObject) {
            $passedModel = $model->__getSerialisablePropertyMap();
        }

        $templateText = file_get_contents($templatePath);
        $output = $this->templateParser->parseTemplateText($templateText, $passedModel);

        $outputPath = $clientLanguageConfiguration->rewriteFileOutputPath($outputPath, $model);


        if (!file_exists(dirname($outputPath))) mkdir(dirname($outputPath), 0777, true);
        file_put_contents($outputPath, $output);
    }


}




