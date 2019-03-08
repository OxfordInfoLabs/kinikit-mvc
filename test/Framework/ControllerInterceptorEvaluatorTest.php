<?php

namespace Kinikit\MVC\Framework;

use Kinikit\MVC\Exception\InvalidControllerInterceptorException;

include_once "autoloader.php";

include_once __DIR__ . "/../Controllers/SimpleController.php";
include_once __DIR__ . "/../Controllers/AdvancedController.php";

/**
 * Test cases for the controller interceptor evaluator.
 *
 * @author mark
 *
 */
class ControllerInterceptorEvaluatorTest extends \PHPUnit\Framework\TestCase {

    public function testIfNoDefinedInterceptorsTheEvaluatorReturnsTrueForAnyPassedController() {

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();
        $this->assertTrue($interceptorEvaluator->evaluateBeforeMethodInterceptors(new \SimpleController(), "defaultHandler"));
        $this->assertTrue($interceptorEvaluator->evaluateBeforeMethodInterceptors(new \AdvancedController(), "defaultHandler"));

    }

    public function testIfInvalidInterceptorsPassedToSetInterceptorsAnExceptionIsRaised() {
        $interceptorEvaluator = new ControllerInterceptorEvaluator ();
        self::assertTrue(true);
        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", true);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", true);
        $testInterceptorBad = new ModelAndView ("bob");

        try {
            $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptorBad));
            $this->fail("Should have thrown an exception here");
        } catch (InvalidControllerInterceptorException $e) {
            // Success
        }

    }

    public function testIfAllDefinedInterceptorsForAPassedControllerReturnTrueTheEvaluatorReturnsTrue() {

        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", true);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", true);
        $testInterceptor3 = new TestControllerInterceptor2 ("ANOTHERONE", false);
        $testInterceptor4 = new TestControllerInterceptor2 ("GoodOne", false);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();

        $interceptorEvaluator->setInterceptors(array($testInterceptor1));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptor3, $testInterceptor4));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
    }

    public function testIfAnyOfTheDefinedInterceptorsReturnFalseTheEvaluatorReturnsFalse() {

        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", false);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", true);
        $testInterceptor3 = new TestControllerInterceptor2 ("ANOTHERONE", false);
        $testInterceptor4 = new TestControllerInterceptor2 ("GoodOne", false);
        $testInterceptor5 = new TestControllerInterceptor2 ("GoodOne", true);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();

        $interceptorEvaluator->setInterceptors(array($testInterceptor1));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor2, $testInterceptor1));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor3));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptor3, $testInterceptor4, $testInterceptor5));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

    }

    public function testIfAnyOfTheDefinedInterceptorsReturnsAControllerObjectTheControllerIsReturnedFromTheEvaluationMethod() {

        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", true);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", new \SimpleController ());
        $testInterceptor3 = new TestControllerInterceptor2 ("ANOTHERONE", false);
        $testInterceptor4 = new TestControllerInterceptor2 ("GoodOne", new \AdvancedController ());
        $testInterceptor5 = new TestControllerInterceptor2 ("GoodOne", false);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();

        $interceptorEvaluator->setInterceptors(array($testInterceptor1));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2));
        $this->assertEquals(new \SimpleController (), $interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor3));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertTrue($interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptor3, $testInterceptor4, $testInterceptor5));
        $this->assertEquals(new \SimpleController (), $interceptorEvaluator->evaluateInterceptorsForController("BadBoy"));
        $this->assertFalse($interceptorEvaluator->evaluateInterceptorsForController("ANOTHERONE"));
        $this->assertEquals(new \AdvancedController (), $interceptorEvaluator->evaluateInterceptorsForController("GoodOne"));

    }

    public function testInterceptorsAreRunForAControllerInTheOrderSpecified() {

        TestControllerInterceptor1::$interceptorRuns = array();

        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", true);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", true);
        $testInterceptor3 = new TestControllerInterceptor2 ("ANOTHERONE", false);
        $testInterceptor4 = new TestControllerInterceptor2 ("GoodOne", true);
        $testInterceptor5 = new TestControllerInterceptor1 ("GoodOne", true);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();
        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptor3, $testInterceptor4, $testInterceptor5));

        $interceptorEvaluator->evaluateInterceptorsForController("BadBoy");
        $this->assertEquals(array("TestControllerInterceptor1", "TestControllerInterceptor2"), TestControllerInterceptor1::$interceptorRuns);

        TestControllerInterceptor1::$interceptorRuns = array();

        $interceptorEvaluator->evaluateInterceptorsForController("GoodOne");
        $this->assertEquals(array("TestControllerInterceptor2", "TestControllerInterceptor1"), TestControllerInterceptor1::$interceptorRuns);

    }

    public function testNoSubsequentInterceptorsAreRunForAControllerIfOneFails() {

        TestControllerInterceptor1::$interceptorRuns = array();

        $testInterceptor1 = new TestControllerInterceptor1 ("BadBoy", false);
        $testInterceptor2 = new TestControllerInterceptor2 ("BadBoy", true);
        $testInterceptor3 = new TestControllerInterceptor2 ("ANOTHERONE", false);
        $testInterceptor4 = new TestControllerInterceptor2 ("GoodOne", false);
        $testInterceptor5 = new TestControllerInterceptor1 ("GoodOne", true);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();
        $interceptorEvaluator->setInterceptors(array($testInterceptor1, $testInterceptor2, $testInterceptor3, $testInterceptor4, $testInterceptor5));

        $interceptorEvaluator->evaluateInterceptorsForController("BadBoy");
        $this->assertEquals(array("TestControllerInterceptor1"), TestControllerInterceptor1::$interceptorRuns);

        TestControllerInterceptor1::$interceptorRuns = array();

        $interceptorEvaluator->evaluateInterceptorsForController("GoodOne");
        $this->assertEquals(array("TestControllerInterceptor2"), TestControllerInterceptor1::$interceptorRuns);

    }

    public function testBlankEvaluatorReturnedForGetDefaultInstanceIfNoConfigFileExists() {
        rename("Config/controller-interceptors.xml", "Config/controller-interceptors-missing.xml");

        $evaluator = ControllerInterceptorEvaluator::getInstance(true);
        $this->assertEquals(new ControllerInterceptorEvaluator (), $evaluator);

        rename("config/controller-interceptors-missing.xml", "config/controller-interceptors.xml");

    }

    public function testCanGetDefaultInstanceConfiguredUsingXMLFile() {

        $evaluator = ControllerInterceptorEvaluator::getInstance(true);
        $interceptors = $evaluator->getInterceptors();

        $this->assertEquals(4, sizeof($interceptors));
        $this->assertEquals(new TestControllerInterceptor1 ("InterceptedController1", true), $interceptors [0]);
        $this->assertEquals(new TestControllerInterceptor2 ("InterceptedController1", false), $interceptors [1]);
        $this->assertEquals(new TestControllerInterceptor1 ("CleverController", true), $interceptors [2]);
        $this->assertEquals(new TestControllerInterceptor2 ("InterceptedController2", false, "AdvancedController"), $interceptors [3]);

    }

    public function testIfDefaultLocationSuppliedInConfigFileAllObjectsWithinLocationAreIncluded() {
        $evaluator = ControllerInterceptorEvaluator::getInstance(true);

        $this->assertTrue(class_exists("TestIAmIncluded"));
        $this->assertTrue(class_exists("TestIAmAlsoIncluded"));
        $this->assertTrue(class_exists("TestGuessWhatIAmAlsoIncluded"));

    }

    public function testWildcardedControllerInterceptorIsFiredOnAllControllerRequests() {

        TestControllerInterceptor1::$interceptorRuns = array();

        $wildcardInterceptor = new TestControllerInterceptor1 ("*", true);

        $interceptorEvaluator = new ControllerInterceptorEvaluator ();
        $interceptorEvaluator->setInterceptors(array($wildcardInterceptor));

        $interceptorEvaluator->evaluateInterceptorsForController("BadBoy");
        $this->assertEquals(array("TestControllerInterceptor1"), TestControllerInterceptor1::$interceptorRuns);

        TestControllerInterceptor1::$interceptorRuns = array();

        $interceptorEvaluator->evaluateInterceptorsForController("GoodOne");
        $this->assertEquals(array("TestControllerInterceptor1"), TestControllerInterceptor1::$interceptorRuns);

    }

}

?>