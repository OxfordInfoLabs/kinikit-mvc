<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;
use Kinikit\MVC\bespokecontroller\BespokeSimpleController;
use Kinikit\MVC\Controllers\SimpleController;
use Kinikit\MVC\Controllers\SimpleController\SimpleController2;
use Kinikit\MVC\Controllers\subcontroller\SimpleSubController;
use Kinikit\MVC\Controllers\subcontroller\subsubcontroller\SimpleSubSubController;
use Kinikit\MVC\Decorators\SimpleDecorator;
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
		$this->assertEquals ( new SimpleController (), ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject() );
	
	}
	
	public function testControllerCorrectlyResolvedIfFragmentsExistAfterController() {
		
		$url = "/SimpleController/extra/parameters";
		
		$this->assertEquals ( new SimpleController (), ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject() );
	
	}
	
	public function testControllerWithNestedFolderPathIsResolvedCorrectly() {
		
		$url = "/subcontroller/SimpleSubController";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject(), new SimpleSubController () );
		
		$url = "/subcontroller/SimpleSubController/extra/guff";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject(), new SimpleSubController () );
		
		$url = "/subcontroller/subsubcontroller/SimpleSubSubController/extra/guff";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject(), new SimpleSubSubController () );
	
	}
	
	public function testDecoratorsFolderIsCheckedForControllersAsWell() {
		$url = "/SimpleDecorator";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject(), new SimpleDecorator () );
	}
	
	public function testAdditionalFoldersAreCheckedIfAppendedAsSearchPaths() {
		ControllerResolver::instance ()->appendControllerFolder ( "bespokecontroller" );
		
		$url = "/BespokeSimpleController";
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url )->__getObject(), new BespokeSimpleController () );
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
		$this->assertEquals ( ControllerResolver::instance ()->resolveControllerForURL ( $url, 1 )->__getObject(), new SimpleController2 () );
	
	}
	

}

?>
