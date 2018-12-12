<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;
use Kinikit\MVC\Exception\ControllerVetoedException;
use Kinikit\MVC\Exception\NoControllerSuppliedException;

include_once "autoloader.php";

include_once __DIR__ . "/../Controllers/SimpleController.php";
include_once __DIR__ . "/../Controllers/AdvancedController.php";
/**
 * Test cases for the controller resolver.
 * 
 * @author mark
 *
 */
class ControllerResolverTest extends \PHPUnit\Framework\TestCase {
	
	public function testNullReturnedIfNoControllerCanBeFoundForURL() {
		
		$this->assertNull ( ControllerResolver::instance ()->resolveControllerForURL ( "/BadBoy" ) );
		$this->assertNull ( ControllerResolver::instance ()->resolveControllerForURL ( "/BadBoy/nochance" ) );
		$this->assertNull ( ControllerResolver::instance ()->resolveControllerForURL ( "/somewhere/Else" ) );
	
	}
	
	public function testCanGetValidControllerInstanceFromSimpleControllerURL() {
		
		$url = "/SimpleController";
		$this->assertEquals ( new \SimpleController (), ControllerResolver::instance ()->resolveControllerForURL ( $url ) );
	
	}
	
	public function testControllerCorrectlyResolvedIfFragmentsExistAfterController() {
		
		$url = "/SimpleController/extra/parameters";
		
		$this->assertEquals ( new \SimpleController (), ControllerResolver::instance ()->resolveControllerForURL ( $url ) );
	
	}
	
	public function testControllerWithNestedFolderPathIsResolvedCorrectly() {
		
		$url = "/subcontroller/SimpleSubController";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \SimpleSubController () );
		
		$url = "/subcontroller/SimpleSubController/extra/guff";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \SimpleSubController () );
		
		$url = "/subcontroller/subsubcontroller/SimpleSubSubController/extra/guff";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \SimpleSubSubController () );
	
	}
	
	public function testDecoratorsFolderIsCheckedForControllersAsWell() {
		$url = "/SimpleDecorator";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \SimpleDecorator () );
	}
	
	public function testAdditionalFoldersAreCheckedIfAppendedAsSearchPaths() {
		ControllerResolver::instance ()->appendControllerFolder ( "bespokecontroller" );
		
		$url = "/BespokeSimpleController";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \BespokeSimpleController () );
	}
	
	public function testNoControllerSuppliedExceptionIsRaisedIfNoControllerFragmentPassed() {
		$url = "/";
        self::assertTrue(true);
		try {
			ControllerResolver::instance ()->resolveControllerForURL ( "/" );
			$this->fail ( "Should have thrown here." );
		} catch ( NoControllerSuppliedException $e ) {
			// Success
		}
	}
	
	public function testIfForcedFolderPrefixPassedAsSecondArgumentToResolverPrecedingFragmentsAreTreatedAsFolders() {
		$url = "/SimpleController/SimpleController2";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url, 1 ), new \SimpleController2 () );
	
	}
	
	public function testIfAttemptToAccessControllerWithDefinedSuccessfulInterceptorFromConfigFileTheInterceptorIsCalled() {
		
		TestControllerInterceptor1::$interceptorRuns = array ();
		
		$url = "/CleverController";
		
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \CleverController () );
		$this->assertEquals ( array ("TestControllerInterceptor1" ), TestControllerInterceptor1::$interceptorRuns );
	
	}
	
	public function testIfAttemptToAccessControllersWithDefinedBooleanFailingInterceptorsFromConfigFileAnExceptionIsRaised() {
        self::assertTrue(true);
		TestControllerInterceptor1::$interceptorRuns = array ();
		
		$url = "/InterceptedController1";
		
		try {
			ControllerResolver::instance ()->resolveControllerForURL ( $url );
			$this->fail ( "Should have thrown here" );
		} catch ( ControllerVetoedException $e ) {
			// Success
		}
	
	}
	
	public function testIfAttemptToAccessControllersWithDefinedBooleanFailingInterceptorsFromConfigFileWithVeotedControllerConfigParameterSetTheVetoedControllerIsCreatedAndReturned() {
		
		Configuration::instance ()->addParameter ( "vetoed.controller", "SimpleController" );
		
		TestControllerInterceptor1::$interceptorRuns = array ();
		
		$url = "/InterceptedController1";
		
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \SimpleController () );
		$this->assertEquals ( array ("TestControllerInterceptor1", "TestControllerInterceptor2" ), TestControllerInterceptor1::$interceptorRuns );
	
	}
	
	public function testIfAttemptToAccessControllerWithDefinedInterceptorWhichReturnsAControllerThisControllerIsReturnedFromResolver() {
		TestControllerInterceptor1::$interceptorRuns = array ();
		
		$url = "/InterceptedController2";
		
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url ), new \AdvancedController () );
		$this->assertEquals ( array ("TestControllerInterceptor2" ), TestControllerInterceptor1::$interceptorRuns );
	}

}

?>