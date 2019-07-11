<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 18/10/2018
 * Time: 10:52
 */

namespace Kinikit\MVC\Framework\API;

use Kinikit\Core\Configuration;
use Kinikit\Core\Object\KeyValue;
use Kinikit\Core\Object\SerialisableObject;
use Kinikit\Core\Util\ObjectArrayUtils;
use Kinikit\Core\Util\Serialisation\JSON\JSONToObjectConverter;
use Kinikit\MVC\Framework\API\Languages\Java\JavaLanguageConfiguration;
use Kinikit\MVC\Framework\API\Languages\NodeJS\NodeJSLanguageConfiguration;
use Kinikit\MVC\Framework\API\Languages\PHP\PHPLanguageConfiguration;

class APIConfiguration extends SerialisableObject {

    /**
     * Identifier for selecting this API
     *
     * @var string
     */
    protected $identifier = "default";


    /**
     * Title for this API
     *
     * @var string
     */
    protected $title;


    /**
     * Visibility for this API
     *
     * @var string
     */
    protected $visibility = "*";

    /**
     * Selector for inclusion in this api
     *
     * @var string
     */
    protected $annotationSelector = "@api";


    /**
     * Client Namespace Mappings.  This should be an associative array of
     * source => client mappings.  This defaults to a single mapping of the application namespace to a
     * sub namespace with \\ClientAPI suffix.  Other paths are left intact by default.
     *
     * @var string[string]
     */
    protected $clientNamespaceMappings = array();

    /**
     * Clients generated for this API
     *
     * @var string[string]
     */
    protected $generatedClients = array(self::CLIENT_PHP => "../clientapi/php", self::CLIENT_JAVA => "../clientapi/java",
        self::CLIENT_NODEJS => "../clientapi/nodejs");


    /**
     * Array of api parameters which will be collected on construction of the API provider.
     *
     * @var \Kinikit\MVC\Framework\API\APIGlobalParam[]
     */
    protected $globalAPIParams = array();


    private static $apiConfigs = array();


    const CLIENT_PHP = "PHP";
    const CLIENT_JAVA = "Java";
    const CLIENT_NODEJS = "NodeJS";

    /**
     * APIConfiguration constructor.
     */
    public function __construct($generatedClients = null) {
        $this->clientNamespaceMappings = array(Configuration::readParameter("application.namespace") => Configuration::readParameter("application.namespace") . "\\ClientAPI");
        $this->title = Configuration::readParameter("application.name") . " API";
        $this->globalAPIParams[] = new APIGlobalParam("apiKey", "Your authentication key for accessing the API");

        if (is_array($generatedClients)) {
            $this->generatedClients = $generatedClients;
        }
    }


    /**
     * @return string
     */
    public function getIdentifier() {
        return $this->identifier;
    }


    /**
     * Title
     *
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getClientNamespaceMappings() {
        return $this->clientNamespaceMappings;
    }


    /**
     * @return string
     */
    public function getVisibility() {
        return $this->visibility;
    }

    /**
     * @return string
     */
    public function getAnnotationSelector() {
        return $this->annotationSelector;
    }

    /**
     * @return array
     */
    public function getGeneratedClients() {
        return $this->generatedClients;
    }

    /**
     * @return \Kinikit\MVC\Framework\API\APIGlobalParam[]
     */
    public function getGlobalAPIParams() {

        if (is_array($this->globalAPIParams)) {
            foreach ($this->globalAPIParams as $index => $param) {
                $this->globalAPIParams[$index]->setIndex($index);
            }
        }

        return $this->globalAPIParams;
    }


    /**
     * Get generated client configuration for applicable clients
     *
     * @return ClientLanguageConfiguration[]
     */
    public function getGeneratedClientConfiguration() {

        $config = array();
        foreach ($this->generatedClients as $language => $path) {
            switch ($language) {
                case self::CLIENT_PHP:
                    $config[] = new PHPLanguageConfiguration($path);
                    break;
                case self::CLIENT_JAVA:
                    $config[] = new JavaLanguageConfiguration($path);
                    break;
                case self::CLIENT_NODEJS:
                    $config[] = new NodeJSLanguageConfiguration($path);
            }
        }

        return $config;

    }

    /**
     * Get the API Configs statically
     *
     * @return APIConfiguration[]
     */
    public static function getAPIConfigs() {
        if (!self::$apiConfigs) {
            if (file_exists("Config/api.json")) {
                $converter = new JSONToObjectConverter();
                $configs = $converter->convert(file_get_contents("Config/api.json"), "\Kinikit\MVC\Framework\API\APIConfiguration[]");
                self::$apiConfigs = ObjectArrayUtils::indexArrayOfObjectsByMember("identifier", $configs);
            }

            if (!self::$apiConfigs) {
                self::$apiConfigs = array("default" => new APIConfiguration());
            }
        }
        return self::$apiConfigs;

    }

}
