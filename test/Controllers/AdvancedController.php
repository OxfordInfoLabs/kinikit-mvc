<?php

namespace Kinikit\MVC\Controllers;

use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\ModelAndView;

class AdvancedController extends Controller {


    public function defaultHandler($requestParameters)
    {
        return new ModelAndView ( "advancedview", array ("var1" => "Joe" ) );
    }
}

?>
