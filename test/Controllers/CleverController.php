<?php

namespace Kinikit\MVC\Controllers;

use Kinikit\MVC\Framework\Controller;
use Kinikit\MVC\Framework\ModelAndView;

class CleverController extends Controller {
	
	public function defaultHandler($requestParameters) {


		$model = array ("var1" => "Billy", "var2" => "Benny", "var3" => "The Queen" );
		return new ModelAndView ( "properview", $model );
	
	}

}

?>
