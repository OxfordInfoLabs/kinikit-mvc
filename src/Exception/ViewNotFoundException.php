<?php

namespace Kinikit\MVC\Exception;

class ViewNotFoundException extends \Exception {

    /**
     * View not found exception
     *
     * @param string $viewName
     */
    public function __construct($viewName, $directory = null) {
        parent::__construct("The view '" . $viewName . "' cannot be found" . ($directory ? " in the directory '" . $directory . "'" : ""));
    }

}

?>