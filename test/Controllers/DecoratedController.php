<?php

use Kinikit\MVC\Framework\Controller;

class DecoratedController extends Controller {
	
	public static $decoratorModelAndView;

    /**
     * @param $requestParameters
     * @return ModelAndView
     */
	public function defaultHandler($requestParameters, $decoratorModelAndView = null) {
		DecoratedController::$decoratorModelAndView = $decoratorModelAndView;
		
		return null;
	}

}

?>