<?php

use Kinikit\MVC\Framework\Controller;

class InterceptedController2 extends Controller {
    /**
     * @param $requestParameters
     * @return ModelAndView
     */
	public function defaultHandler($requestParameters) {
		return null;
	}
}

?>