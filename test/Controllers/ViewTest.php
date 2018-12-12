<?php

use Kinikit\Core\Util\HTTP\URLHelper;

include_once "autoloader.php";

include_once __DIR__ . "/../../src/Controllers/View.php";


/**
 * Test cases for the View Controller
 *
 * @author mark
 *
 */
class ViewTest extends \PHPUnit\Framework\TestCase
{

    public function testViewOnlyModelAndViewReturnedAccordingUsingLastURLFragment()
    {

        $view = new View ();

        URLHelper::setTestURL("/View/myview");
        $modelAndView = $view->handleRequest(array());

        $this->assertEquals(array("request" => array(), "session" => array()), $modelAndView->getModel());
        $this->assertEquals("myview", $modelAndView->getViewName());

        URLHelper::setTestURL("/View/arg/otherarg/badview");
        $modelAndView = $view->handleRequest(array());

        $this->assertEquals(array("request" => array(), "session" => array()), $modelAndView->getModel());
        $this->assertEquals("arg/otherarg/badview", $modelAndView->getViewName());

        URLHelper::setTestURL("/View/new/one/over/the/tree/bonzo");
        $modelAndView = $view->handleRequest(array());

        $this->assertEquals(array("request" => array(), "session" => array()), $modelAndView->getModel());
        $this->assertEquals("new/one/over/the/tree/bonzo", $modelAndView->getViewName());

    }

}

?>