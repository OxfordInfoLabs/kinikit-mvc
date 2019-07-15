<?php


namespace Kinikit\MVC\Response;


use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Exception\FileNotFoundException;
use Kinikit\Core\Template\TemplateParser;
use Kinikit\MVC\ContentSource\FileContentSource;

/**
 * Load a view template (searching the View sub directories in the installed source bases)
 * and process it using the installed Template Parser using a model.
 *
 * @package Kinikit\MVC\Response
 */
class View extends Response {


    /**
     * @var FileContentSource
     */
    private $viewContentSource;

    private $model;
    private $evaluatedContent;


    /**
     * Construct with a view name, an optional model and response code.
     *
     * @param $viewName
     * @param array $model
     * @param int $responseCode
     */
    public function __construct($viewName, $model = [], $responseCode = 200) {
        parent::__construct($responseCode);

        // Wrap any file not founds for views as custom exception
        try {
            $this->viewContentSource = new FileContentSource("Views/" . $viewName . ".php");
        } catch (FileNotFoundException $e) {
            throw new ViewNotFoundException($viewName);
        }

        $this->model = $model;
    }


    /**
     * Return the content type. This is obtained by looking directly at the view file.
     *
     * @return string
     */
    public function getContentType() {
        return $this->viewContentSource->getContentType();
    }

    /**
     * Return the content length.  This must be implemented by all Responses but can return null
     * if no length header is to be added.
     *
     * @return integer
     */
    public function getContentLength() {
        return strlen($this->evaluateContent());
    }

    /**
     * Stream the view having first parsed it using the installed template parser.
     *
     * @return null
     */
    public function streamContent() {
        echo $this->evaluateContent();
    }


    // Evaluate and return the content for this view.
    private function evaluateContent() {
        if (!$this->evaluatedContent) {

            // If view location add it.
            $viewContent = file_get_contents($this->viewContentSource->getFilePath());

            // Get installed template parser
            $templateParser = Container::instance()->get(TemplateParser::class);

            // Evaluate template text using model.
            $this->evaluatedContent = $templateParser->parseTemplateText($viewContent, $this->model);

        }
        return $this->evaluatedContent;
    }

}
