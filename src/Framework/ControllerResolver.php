<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;
use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\MVC\Controllers\view;
use Kinikit\MVC\Exception\ControllerVetoedException;
use Kinikit\MVC\Exception\NoControllerSuppliedException;


/**
 * Special resolver class for resolving controllers using a supplied URL.  The set of search folders statically defined are searched
 * in sequence to try and locate the controller across all defined source bases using the source base manager.
 *
 * @author mark
 *
 */
class ControllerResolver {

    private static $instance;
    private $controllerFolders;

    // Don't allow direct construction.
    private function __construct() {
        $this->controllerFolders = array("Controllers", "Decorators");
    }

    /**
     * Return single instance of this class for use when resolving controllers.
     *
     * @return ControllerResolver
     */
    public static function instance() {
        if (!ControllerResolver::$instance) {
            ControllerResolver::$instance = new ControllerResolver ();
        }

        return ControllerResolver::$instance;
    }

    /**
     * Add a top level controllers folder (default are controllers and decorators)
     */
    public function appendControllerFolder($controllerFolder) {
        array_push($this->controllerFolders, $controllerFolder);
    }

    /**
     * Resolve a controller for a passed URL.  If the forced folder segments is set, all segments up to the value
     * are force treated as folders and not checked for controllers directly.
     *
     * @param string $url
     */
    public function resolveControllerForURL($url, $forcedFolderSegments = 0) {

        // Construct the controller name from the last fragment of the URL
        $urlHelper = new URLHelper ($url);

        // If no controller name supplied by this stage, throw an exception...
        if (!trim($urlHelper->getFirstSegment())) {
            throw new NoControllerSuppliedException ();
        }

        // Check each segment in turn.
        $cumulative = "";
        for ($i = 0; $i < $urlHelper->getSegmentCount(); $i++) {

            $segment = $urlHelper->getSegment($i);
            $cumulative .= ($cumulative ? "/" : "") . $segment;

            // If we are in a forced folder segment, continue without checking for controller.
            if ($forcedFolderSegments > $i)
                continue;


            $matchFound = false;
            $controller = null;

            foreach ($this->controllerFolders as $folder) {

                if ($matchFound)
                    continue;

                foreach (SourceBaseManager::instance()->getApplicationNamespaces() as $applicationNamespace) {
                    if (class_exists($applicationNamespace . "\\" . $folder . "\\" . str_replace("/", "\\", $cumulative), true)) {
                        $className = $applicationNamespace . "\\" . $folder . "\\" . str_replace("/", "\\", $cumulative);
                        $controller = new $className();
                        $matchFound = true;
                        break;
                    }
                }

            }

            if (!$matchFound) {

                foreach ($this->controllerFolders as $folder) {

                    $filename = SourceBaseManager::resolvePath($folder . "/" . $cumulative . ".php");
                    if (file_exists($filename)) {

                        include_once($filename);
                        $controller = new $segment ();
                        $matchFound = true;
                        break;

                    }
                }
            }

            if (!$matchFound) {
                // If this doesn't work, attempt a view directly
                $view = SourceBaseManager::resolvePath("Views/" . $cumulative . ".php");
                if (file_exists($view)) {
                    $controller = new view();
                    $segments = $urlHelper->getAllSegments();
                    array_splice($segments, $forcedFolderSegments, 0, array("View"));
                    URLHelper::setTestURL("/" . join("/", $segments));
                    $matchFound = true;
                }

            }

            // Check interceptors to ensure that we can proceed and not being vetoed.
            if ($matchFound) {
                return $controller;
            }

        }

        return null;

    }

}

?>