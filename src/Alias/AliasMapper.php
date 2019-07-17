<?php


namespace Kinikit\MVC\Alias;


use Kinikit\Core\Configuration\ConfigFile;
use Kinikit\MVC\Response\Redirect;
use Kinikit\MVC\Response\Response;

/**
 * @noProxy
 *
 * Class AliasMapper
 * @package Kinikit\MVC\Alias
 */
class AliasMapper {

    private $aliases;

    /**
     * Map a passed URL using aliases if present.
     *
     * @param string $url
     */
    public function mapURL($url) {

        $aliases = $this->getAliases();

        // Loop through each alias and check if we match
        foreach ($aliases as $alias => $target) {

            // Make the alias regexp suitable.
            $alias = str_replace("/", "\\/", $alias);
            $alias = preg_replace("/\\$[0-9]/", "(.*?)", $alias);

            $explodedTarget = explode(" ", $target);

            // If we have a match, resolve it.
            $replaced = preg_replace("/^" . $alias . "$/", $explodedTarget[0], $url);
            if ($replaced != $url) {

                $redirect = null;
                if (strpos($explodedTarget[0], "://")) {
                    $redirect = isset($explodedTarget[1]) ? $explodedTarget[1] == Response::RESPONSE_REDIRECT_PERMANENT : true;
                } else if (sizeof($explodedTarget) > 1) {
                    $redirect = $explodedTarget[1] == Response::RESPONSE_REDIRECT_PERMANENT;
                }

                if ($redirect !== null) {
                    return new Redirect($explodedTarget[0], $redirect);
                }
                return $replaced;
            }
        }

        return $url;

    }

    /**
     * Get aliases, lazy load them if required.
     */
    private function getAliases() {

        if (!$this->aliases) {
            $configFile = new ConfigFile("Config/aliases.txt");
            $this->aliases = $configFile->getAllParameters();
        }

        return $this->aliases;

    }
}
