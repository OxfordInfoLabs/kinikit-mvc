<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;
use Kinikit\Core\Util\HTTP\HttpRequest;
use Kinikit\Core\Util\HTTP\HttpSession;
use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\MVC\Exception\ControllerNotFoundException;
use Kinikit\MVC\Exception\NoControllerSuppliedException;

include_once "autoloader.php";

include_once __DIR__ . "/../Controllers/SimpleController.php";
include_once __DIR__ . "/../Controllers/AdvancedController.php";

/**
 * Test cases for the MVC dispatcher class.  This provides the main entry point for an MVC application.
 *
 * @author mark
 *
 */
class DispatcherTest extends \PHPUnit\Framework\TestCase {

    public function testNoControllerSuppliedExceptionRaisedIfNoControllerSuppliedForDispatch() {
        URLHelper::setTestURL("/");
        Configuration::instance()->addParameter("welcome.path", "");
        self::assertTrue(true);
        $dispatcher = new Dispatcher ();

        try {
            $dispatcher->dispatch();
            $this->fail("Should have thrown here");
        } catch (NoControllerSuppliedException $e) {
            // Success
        }
    }

    public function testControllerNotFoundExceptionRaisedIfNoControllerCanBeFoundMatchingFirstURLFragment() {
        URLHelper::setTestURL("/ImaginaryController");
        self::assertTrue(true);
        $dispatcher = new Dispatcher ();

        try {
            $dispatcher->dispatch();
            $this->fail("Should have thrown here");
        } catch (ControllerNotFoundException $e) {
            // Success
        }
    }

    public function testControllerIsInvokedFromControllersDirectoryIfSimpleURLPassedThrough() {

        URLHelper::setTestURL("/SimpleController");
        \SimpleController::$executed = false;
        $this->assertFalse(\SimpleController::$executed);

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(\SimpleController::$executed);
    }

    public function testIfModelAndViewReturnedFromControllerOutputFromViewIsPrintedToStdOut() {
        URLHelper::setTestURL("/CleverController");

        $dispatcher = new Dispatcher ();

        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("Hello Billy and Benny, You are all waiting for The Queen.  Too bad Billy and Benny:  You will never meet The Queen.", $output);
    }

    public function testWelcomePathIsEvaluatedIfPresentIfNoControllerSupplied() {
        URLHelper::setTestURL("/");
        \SimpleController::$executed = false;
        Configuration::instance()->addParameter("welcome.path", "/SimpleController");

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(\SimpleController::$executed);
    }

    public function testUnknownPathIsEvaluatedIfPresentIfUnknownControllerAccessedAnd404HeaderIsSet() {

        URLHelper::setTestURL("/ImaginaryPath");
        \SimpleController::$executed = false;
        Configuration::instance()->addParameter("unknown.path", "/CleverController");

        $dispatcher = new Dispatcher ();

        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("Hello Billy and Benny, You are all waiting for The Queen.  Too bad Billy and Benny:  You will never meet The Queen.", $output);

    }

    public function testControllerNotFoundExceptionRaisedIfUnknownPathIsUnknown() {
        self::assertTrue(true);
        URLHelper::setTestURL("/ImaginaryPath");
        \SimpleController::$executed = false;
        Configuration::instance()->addParameter("unknown.path", "/DodgyUnknown");

        $dispatcher = new Dispatcher ();

        try {
            $dispatcher->dispatch();
            $this->fail("Should have thrown here");
        } catch (ControllerNotFoundException $e) {
            // Success
        }

    }

    /*
     *
     * NONE CLI FUNCTION
     *
	public function testIfNoContentTypeHasBeenSpecifiedByTheControllerTheDefaultTextHtmlTypeIsSet() {
		header_remove ( "Content-Type" );
		
		URLHelper::setTestURL ( "/StandardController" );
		$dispatcher = new Dispatcher ();
		ob_start ();
		$dispatcher->dispatch ();
		$output = ob_get_contents ();
		ob_end_clean ();


		// Check we get the default....
		$this->assertTrue ( is_numeric ( array_search ( "Content-Type: text/html", headers_list () ) ) );


		header_remove ( "Content-Type" );
		
		URLHelper::setTestURL ( "/CleverController" );
		
		$dispatcher = new Dispatcher ();
		
		ob_start ();
		$dispatcher->dispatch ();
		$output = ob_get_contents ();
		ob_end_clean ();
		
		// Check we get the controller one.
		$this->assertTrue ( is_numeric ( array_search ( "Content-Type: text/xml", headers_list () ) ) );
	
	} */

    public function testCanDispatchToControllersAndViewsDefinedInMultipleSourceBasesIfSourceBaseManagerIsConfigured() {

        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1", "Framework/resourcepath2"));

        URLHelper::setTestURL("/Path1Controller");
        $dispatcher = new Dispatcher ();
        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("Path 1", $output);

        URLHelper::setTestURL("/Path2Controller");
        $dispatcher = new Dispatcher ();
        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("PATH 2", $output);

        SourceBaseManager::instance()->setSourceBases(array("."));

    }

    public function testAllSessionAndRequestParametersAreInjectedIntoModelAndViewUsingNamespacesBeforeEvaluation() {

        URLHelper::setTestURL("/AdvancedController");
        $dispatcher = new Dispatcher ();

        HttpSession::instance()->setValue("user", "Marko");
        HttpSession::instance()->setValue("count", 55);

        $_REQUEST ["baby"] = "Show me";
        $_REQUEST ["page"] = "URL Combination";

        HttpRequest::instance(true);

        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals("Hello Joe, The current user is Marko and the passed request parameter was Show me.  I wonder if 55 times is quite enough for the current URL Combination.", $output);

    }

    public function testApplicationAnnouncementIsIncludedAndExecutedIfFoundInTopLevelOfProject() {

        URLHelper::setTestURL("/AdvancedController");
        $dispatcher = new Dispatcher ();

        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertFalse(class_exists("ApplicationAnnouncement"));

        rename("ApplicationAnnouncementOff.php", "ApplicationAnnouncement.php");

        ob_start();
        $dispatcher->dispatch();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertTrue(class_exists("ApplicationAnnouncement"));
        $this->assertTrue(\ApplicationAnnouncement::$run);

        rename("ApplicationAnnouncement.php", "ApplicationAnnouncementOff.php");

    }

    public function testWelcomePathControllerIsEvaluatedIfNoControllerFragmentPassed() {

        URLHelper::setTestURL("/");
        Configuration::instance()->addParameter("welcome.path", "/SimpleController");

        \SimpleController::$executed = false;
        $this->assertFalse(\SimpleController::$executed);

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(\SimpleController::$executed);
    }

}

?>