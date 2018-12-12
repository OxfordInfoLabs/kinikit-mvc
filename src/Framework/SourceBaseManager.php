<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;

/**
 * Essentially static class which permits an array of search filesystem paths to be programmed in once.
 * Subsequent calls to resolve() will search all base paths and return the first matching path where a file exists.
 *
 */
class SourceBaseManager {

    private $applicationNamespaces = array();
    private $sourceBases;
    private $resolvedPaths = array();

    private static $instance;

    // Private constructor to prevent direct instantiation.
    private function __construct() {
        $this->sourceBases = array(".", __DIR__ . "/..", __DIR__ . "/../../WebServices");
    }

    /**
     * Main static instance method.  Returns singleton instance.
     *
     * @return SourceBaseManager
     */
    public static function instance() {
        if (!SourceBaseManager::$instance) {
            SourceBaseManager::$instance = new SourceBaseManager ();
        }

        return SourceBaseManager::$instance;
    }

    /**
     * Main method, called to resolve the path for a particular resource.  This searches all search paths as
     * base filesystem paths looking for the relative file path below each search path in turn.  As soon as
     * a match is found it is returned, otherwise if no match is found the first search path is assumed and is
     * returned.
     *
     */
    public static function resolvePath($relativeFilePath) {

        $instance = SourceBaseManager::instance();

        // Assume that the first path will be used if none other found
        $targetPath = $instance->makeFullFilePathFromBaseAndFilePath($instance->sourceBases [0], $relativeFilePath);

        // Try each search path in turn.  If the file can be found
        // within this search path reset the target path
        foreach ($instance->sourceBases as $sourceBase) {

            $proposedPath = $instance->makeFullFilePathFromBaseAndFilePath($sourceBase, $relativeFilePath);
            if (file_exists($proposedPath)) {
                $targetPath = $proposedPath;
                break;
            }
        }

        // Make sure we log this entry in the history
        $instance->resolvedPaths [$relativeFilePath] = true;

        return $targetPath;
    }

    /**
     * Initialise this class statically with the search paths
     *
     * @param array $searchPaths
     */
    public function setSourceBases($sourceBases) {
        $this->sourceBases = $sourceBases;

        // Also reset the resolved paths array at this point.
        $this->resolvedPaths = array();
    }


    /**
     * Prepend another search path.
     *
     * @param $sourceBase
     */
    public function prependSourceBase($sourceBase) {
        array_unshift($this->sourceBases, $sourceBase);
    }

    /**
     * Inject another search path (useful for products)
     *
     * @param string $searchPath
     */
    public function appendSourceBase($sourceBase) {
        $this->sourceBases [] = $sourceBase;
    }

    /**
     * Get the current search paths array
     *
     * @return array
     */
    public function getSourceBases() {
        return $this->sourceBases;
    }


    /**
     * Add an application namespace
     *
     * @param $namespace
     */
    public function addApplicationNamespace($namespace) {
        $this->applicationNamespaces[] = $namespace;
    }

    /**
     * Get application namespaces
     *
     * @return array
     */
    public function getApplicationNamespaces() {
        $namespaces = $this->applicationNamespaces;
        if ($namespace = Configuration::instance()->getParameter("application.namespace")) {
            $namespaces[] = $namespace;
        }
        $namespaces[] = "Kinikit\\MVC";

        return $namespaces;
    }


    /**
     * A history of all resolved paths is kept which this boolean function verifies.
     *
     */
    public function hasPathBeenResolved($path) {
        return array_key_exists($path, $this->resolvedPaths);
    }

    // Helper function to sort out extraneous slashes to handle all variants of input.
    private function makeFullFilePathFromBaseAndFilePath($basePath, $relativeFilePath) {

        // Strip slashes from back of base path and front of relative file path if required
        if (substr($basePath, strlen($basePath) - 1) == '/') {
            $basePath = substr($basePath, 0, strlen($basePath) - 1);
        }

        if (substr($relativeFilePath, 0, 1) == '/') {
            $relativeFilePath = substr($relativeFilePath, 1);
        }

        return $basePath . "/" . $relativeFilePath;

    }

}

?>