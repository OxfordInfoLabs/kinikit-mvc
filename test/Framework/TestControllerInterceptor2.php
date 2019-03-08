<?php


namespace Kinikit\MVC\Framework;


class TestControllerInterceptor2 extends ControllerInterceptor {
	
	private $succeed;
	private $failController;
	
	public function __construct($controllerName = null, $succeed = null, $failController = null) {
		$this->succeed = $succeed;
		$this->failController = $failController;
	}
	
	/**
	 * 
	 */
	public function beforeHandleRequest($controllerName) {
		TestControllerInterceptor1::$interceptorRuns [] = "TestControllerInterceptor2";
		return $this->failController ? new $this->failController () : $this->succeed;
	}
	/**
	 * @return the $succeed
	 */
	public function getSucceed() {
		return $this->succeed;
	}
	
	/**
	 * @param $succeed the $succeed to set
	 */
	public function setSucceed($succeed) {
		$this->succeed = $succeed;
	}
	/**
	 * @return the $failController
	 */
	public function getFailController() {
		return $this->failController;
	}
	
	/**
	 * @param $failController the $failController to set
	 */
	public function setFailController($failController) {
		$this->failController = $failController;
	}

}

?>