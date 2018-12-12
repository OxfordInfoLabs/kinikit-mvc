<?php

use Kinikit\MVC\Framework\Controller;

class InterceptedController1 extends Controller {
    /**
     * @param $requestParameters
     * @return ModelAndView
     */
	public function defaultHandler($requestParameters) {
		return null;
	}

}

?>