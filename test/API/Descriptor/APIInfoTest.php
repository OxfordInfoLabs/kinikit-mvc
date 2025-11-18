<?php

namespace Kinikit\MVC\API\Descriptor;

include_once "autoloader.php";

/**
 * Class APIInfoTest
 * @package Kinikit\MVC\API\Descriptor
 *
 */
class APIInfoTest extends \PHPUnit\Framework\TestCase {

//
//    public function testCanGetControllerDataFromStandardConfiguration() {
//
//        $apiInfo = new APIInfo(new APIConfiguration(array()));
//
//        $controllerSummaries = $apiInfo->getAllAPIControllerSummaryInfo();
//        $this->assertEquals(1, sizeof($controllerSummaries));
////        $this->assertEquals(new APIControllerSummary("API/TestREST", "Kinikit\\MVC\\ClientAPI\\API", "TestREST", array("get", "list", "create", "update", "patch",
////            "delete", "nestedGet", "nestedCreate")), $controllerSummaries[0]);
//
//
//        $controller = $apiInfo->getAPIControllerByPath("TestREST");
//        $this->assertTrue($controller instanceof APIController);
//
//        $methods = array();
//        $methods[] = new APIMethod("get", "/**
//     * Get a test object by id.
//     * ", "GET", "\$id", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject", "", array(new APIParam("id", "integer", "", true, false, 0, null)));
//
//        $methods[] = new APIMethod("list", "/**
//     * List all test objects
//     *
//     * ", "GET", "", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject[]", "", array());
//
//
//        $methods[] = new APIMethod("create", "/**
//     * Create a test object
//     *
//     * ", "POST", "", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject", "", array(new APIParam("testObject", "\Kinikit\\MVC\\Objects\\TestRESTObject", "", false, true, 0, null)));
//
//        $methods[] = new APIMethod("update", "/**
//     * Update a test object
//     *
//     * ", "PUT", "\$objectId", "\Kinikit\\MVC\\Objects\\TestRESTObject", "", array(new APIParam("objectId", "integer", "", true, false, 0, null),
//            new APIParam("testObject", "\Kinikit\\MVC\\Objects\\TestRESTObject", false, true, 1, null)));
//
//
//        $methods[] = new APIMethod("patch", "/**
//     * Patch a test object with new values
//     *
//     * ", "PATCH", "\$objectId", "\Kinikit\\MVC\\Objects\\TestRESTObject", "", array(new APIParam("objectId", "integer", "", true, false, 0, null),
//            new APIParam("data", "mixed[]", false, true, 1, null)));
//
//
//        $methods[] = new APIMethod("delete", "/**
//     * Delete a test object by id
//     *
//     * ", "DELETE", "\$objectId", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject", "", array(new APIParam("objectId", "integer", "", true, false, 0, null)));
//
//
//        $methods[] = new APIMethod("nestedGet", "/**
//     * Get a test object by id using a nested url.
//     * ", "GET", "nested/\$id", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject", "", array(new APIParam("id", "integer", "", true, false, 0, null)));
//
//        $methods[] = new APIMethod("nestedCreate", "/**
//     * Create a test object
//     *
//     * ", "POST", "nested", "\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObject", "", array(new APIParam("testObject", "\Kinikit\\MVC\\Objects\\TestRESTObject", "", false, true, 0, null)));
//
//
//        $requiredObjects = array();
//
//        $this->assertEquals(new APIController("API/TestREST", "Kinikit\\MVC\\Controllers", "TestREST", "Test REST Service", "/**
//     * Test REST Service
//     *
//     * ", $methods, $requiredObjects), $controller);
//
//    }
//
//
//    public function testCanGetObjectDataFromStandardConfiguration() {
//
//
//        $apiInfo = new APIInfo(new APIConfiguration(array()));
//
//        $objects = $apiInfo->getAllAPIObjectSummaryInfo();
//        $this->assertEquals(array(new APIObjectSummary("Objects/TestRESTObjectComment", "TestRESTObjectComment"), new APIObjectSummary("Objects/TestRESTObject", "TestRESTObject")), $objects);
//
//        $info = $apiInfo->getAPIObjectByPath("Objects/TestRESTObject");
//
//        $properties = array();
//        $properties[] = new APIProperty("id", "integer", "/**
//     * Id
//     *
//     * ", false, 0);
//
//        $properties[] = new APIProperty("name", "string", "/**
//     * Name
//     *
//     * ", true, 1);
//
//        $properties[] = new APIProperty("email", "string", "/**
//     * Email
//     *
//     * ", true, 2, 1);
//
//        $properties[] = new APIProperty("lastStatus", "string", "/**
//     * Last Status
//     *
//     * ", true, 3, 2);
//
//        $properties[] = new APIProperty("comments", "\\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObjectComment[]", "/**
//     * Comments
//     *
//     * ", true, 4, 3);
//
//
//        $requiredObjects = array("\\Kinikit\\MVC\\ClientAPI\\Objects\\TestRESTObjectComment");
//
//        $this->assertEquals(new APIObject("Objects/TestRESTObject", "Kinikit\\MVC\\ClientAPI\\Objects", "TestRESTObject", $properties, $requiredObjects), $info);
//
//
//    }

//    public function testCanGetExceptionDataFromStandardConfiguration() {
//
//        $apiInfo = new APIInfo(new APIConfiguration());
//        $this->assertEquals(2, sizeof($apiInfo->getAllAPIExceptionSummaryInfo()));
//
//
//    }


    public function testTest() {
        $this->assertTrue(true);
    }

}
