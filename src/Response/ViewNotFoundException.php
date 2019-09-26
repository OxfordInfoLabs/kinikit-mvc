<?php
/**
 * Created by PhpStorm.
 * User: mark
 * Date: 26/09/2019
 * Time: 14:27
 */

namespace Kinikit\MVC\Response;


use Kinikit\Core\Exception\ItemNotFoundException;

class ViewNotFoundException extends ItemNotFoundException {

    public function __construct($viewPath) {
        parent::__construct("The view with path $viewPath could not be found");
    }

}