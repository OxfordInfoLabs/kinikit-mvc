<?php

namespace Kinikit\MVC\Framework;

use Kinikit\Core\Configuration;
;
use Kinikit\MVC\Controllers\SimpleController;
use Kinikit\MVC\Exception\ControllerNotFoundException;
use Kinikit\MVC\Exception\NoControllerSuppliedException;
use Kinikit\MVC\Framework\HTTP\HttpRequest;
use Kinikit\MVC\Framework\HTTP\HttpSession;
use Kinikit\MVC\Framework\HTTP\URLHelper;

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
        SimpleController::$executed = false;
        $this->assertFalse(SimpleController::$executed);

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(SimpleController::$executed);
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
        SimpleController::$executed = false;
        Configuration::instance()->addParameter("welcome.path", "/SimpleController");

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(SimpleController::$executed);
    }

    public function testUnknownPathIsEvaluatedIfPresentIfUnknownControllerAccessedAnd404HeaderIsSet() {

        URLHelper::setTestURL("/ImaginaryPath");
        SimpleController::$executed = false;
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
        SimpleController::$executed = false;
        Configuration::instance()->addParameter("unknown.path", "/DodgyUnknown");

        $dispatcher = new Dispatcher ();

        try {
            $dispatcher->dispatch();
            $this->fail("Should have thrown here");
        } catch (ControllerNotFoundException $e) {
            // Success
        }

    }


    public function testAllSessionAndRequestParametersAreInjectedIntoModelAndViewUsingNamespacesBeforeEvaluation() {

        URLHelper::setTestURL("/AdvancedController");
        $dispatcher = new Dispatcher ();

        HttpSession::instance()->setValue("user", "Marko");
        HttpSession::instance()->setValue("count", 55);

        $_GET ["baby"] = "Show me";
        $_GET ["page"] = "URL Combination";

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

        SimpleController::$executed = false;
        $this->assertFalse(SimpleController::$executed);

        $dispatcher = new Dispatcher ();
        $dispatcher->dispatch();

        $this->assertTrue(SimpleController::$executed);
    }

}

?>
