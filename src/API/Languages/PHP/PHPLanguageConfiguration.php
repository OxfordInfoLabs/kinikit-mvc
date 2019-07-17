<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 19/10/2018
 * Time: 12:24
 */

namespace Kinikit\MVC\API\Languages\PHP;

use Kinikit\MVC\API\APIConfiguration;
use Kinikit\MVC\API\ClientLanguageConfiguration;

class PHPLanguageConfiguration extends ClientLanguageConfiguration {

    public function __construct($outputPath = null) {
        parent::__construct(APIConfiguration::CLIENT_PHP, ".php", $outputPath);
    }

    /**
     * Add php specific language properties
     *
     * @param string $objectClass
     * @param $object
     */
    public function addLanguagePropertiesToAPIDescriptorObject($objectClass, $object) {

        if ($objectClass == "APIMethod") {

            // Convert the request path to php version
            $phpRequestPath = preg_replace("/\\{([a-zA-Z0-9_]+)}/", '$' . '$1', $object->getRequestPath());
            $object->setPHPRequestPath($phpRequestPath);
        }

    }

}
