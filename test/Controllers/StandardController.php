<?php

use Kinikit\MVC\Framework\Controller;

class StandardController extends Controller {
	/**
	 * @return ModelAndView
	 */
	public function defaultHandler($requestParameters = null) {
		return new ModelAndView ( "myview" );
	}

}

?>