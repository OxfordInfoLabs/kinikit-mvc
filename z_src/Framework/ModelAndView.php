<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Template\Parser\PHPTemplateParser;
use Kinikit\Core\Template\TemplateParser;
use Kinikit\MVC\Exception\NoViewSuppliedException;
use Kinikit\MVC\Exception\ViewNotFoundException;


;

/**
 * Model and View object forming the basis of MVC behaviour.
 *
 * @author mark
 *
 */
class ModelAndView {

    private $viewName;
    private $model;


    /**
     * Construct a model and view with a model and view name.
     */
    public function __construct($viewName, $model = array()) {

        if (!$viewName)
            throw new NoViewSuppliedException ();

        $this->viewName = $viewName;
        $this->model = $model;
    }

    /**
     * Evaluate this model and view, returning the evaluation as a string.
     */
    /**
     *
     * @return the $viewName
     */
    public function getViewName() {
        return $this->viewName;
    }

    /**
     *
     * @return the $model
     */
    public function getModel() {
        return $this->model;
    }

    /**
     * Convenience mechanism for getting a model value from the model.
     *
     * @param $key string
     * @return mixed
     */
    public function getModelValue($key) {
        return $this->model [$key];
    }

    /**
     * Augment the constructed model by injecting additional values into the
     * model.
     *
     * @param $additionalModel array
     */
    public function injectAdditionalModel($additionalModel, $overwriteValues = true) {
        if (!is_array($this->model)) {
            $this->model = array();
        }
        if (!is_array($additionalModel)) {
            $additionalModel = array();
        }

        if ($overwriteValues) {
            $this->model = array_merge($this->model, $additionalModel);
        } else {
            $this->model = array_merge($additionalModel, $this->model);
        }
    }

    public function evaluate($viewDirectory = null) {

        if ($viewDirectory) {
            $filename = $viewDirectory . "/" . $this->viewName . ".php";
        } else {
            $filename = SourceBaseManager::resolvePath("Views/" . $this->viewName . ".php");
        }


        if (file_exists($filename)) {

            // Parse the file directly as an include using PHP
            $phpViewParser = new PHPTemplateParser();
            $viewContents = $phpViewParser->parseTemplateText($filename, $this->model);

            // Parse the results using the configured view parser.
            $configuredViewParser = TemplateParser::getConfiguredParser();
            $viewContents = $configuredViewParser->parseTemplateText($viewContents, $this->model);


            return $viewContents;

        } else {
            throw new ViewNotFoundException ($this->viewName, $viewDirectory);
        }
    }


}

?>