<?php

namespace Kinikit\MVC\Framework\Controller;

use Kinikit\Core\Util\HTTP\HttpSession;
use Kinikit\MVC\Controllers\SimpleController;
use Kinikit\MVC\Controllers\TestDecorator;
use Kinikit\MVC\Exception\ControllerNotFoundException;
use Kinikit\MVC\Framework\HTTP\HttpRequest;
use Kinikit\MVC\Framework\HTTP\URLHelper;
use Kinikit\MVC\Framework\ModelAndView;

include_once "autoloader.php";
include_once "Controllers/TestDecorator.php";
include_once "Controllers/SimpleController.php";

/**
 * Test cases for the decorator controller.
 *
 * @author mark
 *
 */
class DecoratorTest extends \PHPUnit\Framework\TestCase {

    public function testHandleDecoratorRequestIsCalledAndItsModelAndViewIsReturnedOnHandleRequestIfDecoratorOnlyFragmentPassed() {
        URLHelper::setTestURL("/TestDecorator");

        $decorator = new TestDecorator ();
        $modelAndView = $decorator->handleRequest(new HttpRequest());

        $this->assertEquals(new ModelAndView ("banana", array("test" => "Bodger", "test2" => "Badger", "request" => array(), "session" => array())), $modelAndView);
    }

    public function testHandleRequestOnDecoratorThrowsAnExceptionIfInvalidControllerSuppliedAsSecondFragment() {
        self::assertTrue(true);
        URLHelper::setTestURL("/TestDecorator/MyMonkey");

        $decorator = new TestDecorator ();

        try {
            $decorator->handleRequest(new HttpRequest());
            $this->fail("Should have thrown here");
        } catch (ControllerNotFoundException $e) {
            // Success
        }

        URLHelper::setTestURL("/TestDecorator/NoSuchController");

        $decorator = new TestDecorator ();

        try {
            $decorator->handleRequest(new HttpRequest());
            $this->fail("Should have thrown here");
        } catch (ControllerNotFoundException $e) {
            // Success
        }
    }

    public function testIfValidContentControllerIsPassedItIsInvokedDuringMainDecoratorHandleRequest() {

        SimpleController::$executed = false;

        URLHelper::setTestURL("/TestDecorator/SimpleController");

        $decorator = new TestDecorator ();

        $decorator->handleRequest(new HttpRequest());

        $this->assertTrue(SimpleController::$executed);

    }

//    public function testContentControllerModelIsMergedIntoDecoratorModelAlongWithSpecialContentParameter() {
////        self::assertTrue(true);
////        HttpSession::instance()->setValue("user", "Bobbery");
////        HttpSession::instance()->setValue("count", 1000);
////        $_REQUEST ["baby"] = "Google";
////        $_REQUEST ["page"] = "Page";
////
////        URLHelper::setTestURL("/TestDecorator/AdvancedController");
////
////        $decorator = new \TestDecorator ();
////
////        $modelAndView = $decorator->handleRequest(array());
////
////        $content = "Hello Joe, The current user is Bobbery and the passed request parameter was Google.  I wonder if 1000 times is quite enough for the current Page.";
////
////        $prospectiveMatch = array("test" => "Bodger", "test2" => "Badger", "var1" => "Joe", "request_baby" => "Google", "request_page" => "Page",
////            "session_user" => "Bobbery", "session_count" => 1000,
////            "content" => $content);
//
//
//
//    }

//    public function testDecoratorModelIsPassedAsAnOptionalParameterToTheContentControllerForConvenience() {
//
//        URLHelper::setTestURL("/TestDecorator/DecoratedController");
//
//        $decoratorModelAndView = new \TestDecorator ();
//        $modelAndView = $decoratorModelAndView->handleRequest(array());
//
//        $this->assertEquals(new ModelAndView ("banana", array("test" => "Bodger", "test2" => "Badger")), \DecoratedController::$decoratorModelAndView);
//
//    }

}

?>
