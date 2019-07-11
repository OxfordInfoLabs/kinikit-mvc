<?php

namespace Kinikit\MVC\Framework;

use Kinikit\MVC\Exception\NoViewSuppliedException;
use Kinikit\MVC\Exception\ViewNotFoundException;

include_once "autoloader.php";

/**
 * Static test suite.
 */
class ModelAndViewTest extends \PHPUnit\Framework\TestCase {

    public function testNoViewSuppliedExceptionThrownIfAttemptToConstructAModelAndViewWithoutViewName() {
        self::assertTrue(true);
        try {
            $modelAndView = new ModelAndView ("");
            $this->fail("Should have thrown here");
        } catch (NoViewSuppliedException $e) {
            // Success
        }

        try {
            $modelAndView = new ModelAndView (null);
            $this->fail("Should have thrown here");
        } catch (NoViewSuppliedException $e) {
            // Success
        }

    }

    public function testCanEvaluateModelAndViewForViewOnlyModelAndViewResolvedInTopLevelViewsDirectory() {

        $modelAndView = new ModelAndView ("myview");
        $this->assertEquals("<h1>Bobbing up and down</h1>", $modelAndView->evaluate());
    }

    public function testCanEvaluateModelAndViewWithDoubleHashSubstitutedModelParametersAndTheseAreEvaluatedCorrectly() {

        $modelAndView = new ModelAndView ("properview", array("var1" => "Bodgett", "var2" => "Scarper", "var3" => "Miss Independent"));
        $this->assertEquals("Hello Bodgett and Scarper, You are all waiting for Miss Independent.  Too bad Bodgett and Scarper:  You will never meet Miss Independent.", $modelAndView->evaluate());

    }

    public function testCanEvaluateModelAndViewWithPHPSubstitedModelParametersAndTheseAreEvaluatedCorrectly() {

        $modelAndView = new ModelAndView ("phpview", array("var1" => "Bodgett", "var2" => "Scarper", "var3" => "Miss Independent"));
        $this->assertEquals("Hello Bodgett and Scarper, You are all waiting for Miss Independent.  Too bad Bodgett and Scarper:  You will never meet Miss Independent.", $modelAndView->evaluate());

    }

    public function testCanSupplyAlternativeViewDirectoryToFindTheViewToEvaluate() {
        $modelAndView = new ModelAndView ("otherview");
        $this->assertEquals("I am a view located elsewhere.", $modelAndView->evaluate("Framework"));
    }

    public function testCanInjectAdditionalModelParametersIntoAnExistingModelAndViewObject() {

        $modelAndView = new ModelAndView ("phpview", array());
        $modelAndView->injectAdditionalModel(array("var1" => "Bodgett", "var2" => "Scarper", "var3" => "Miss Independent"));
        $this->assertEquals("Hello Bodgett and Scarper, You are all waiting for Miss Independent.  Too bad Bodgett and Scarper:  You will never meet Miss Independent.", $modelAndView->evaluate());

    }

    public function testViewNotFoundExceptionRaisedIfInvalidViewFilenamePassed() {
        self::assertTrue(true);
        $modelAndView = new ModelAndView ("badview");
        try {
            $modelAndView->evaluate();
            $this->fail("Should have thrown here");
        } catch (ViewNotFoundException $e) {
            // Success
        }

        try {
            $modelAndView->evaluate("Framework");
            $this->fail("Should have thrown here");
        } catch (ViewNotFoundException $e) {
            // Success
        }

    }


}

