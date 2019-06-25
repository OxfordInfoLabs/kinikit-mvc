<?php
/**
 * Created by PhpStorm.
 * User: markrobertshaw
 * Date: 15/10/2018
 * Time: 11:19
 */

namespace Kinikit\MVC\Framework\Controller;

use Kinikit\Core\Util\HTTP\HttpRequest;
use Kinikit\Core\Util\HTTP\URLHelper;
use Kinikit\Core\Util\Serialisation\JSON\ObjectToJSONConverter;

include_once "autoloader.php";

class RESTServiceTest extends \PHPUnit\Framework\TestCase {


    private $converter;


    public function setUp(): void {

        $this->converter = new ObjectToJSONConverter();
        parent::setUp();
    }

    public function testCanCallSingleGetMethod() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/3");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals($this->converter->convert(new TestRESTObject("3", "TEST 3", "test3@test.com", "GET SINGLE")), $result);

    }


    public function testCanCallSingleGetMethodWithRepeatedPattern() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/TestREST");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals($this->converter->convert(new TestRESTObject("TestREST", "TEST TestREST", "testTestREST@test.com", "GET SINGLE")), $result);

    }


    public function testCanCallOtherGetMethodWithOverlappingPattern() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/count");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals(50, $result);

    }

    public function testCanCallListGetMethod() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $expectedList = array();
        for ($i = 0; $i < 10; $i++) {
            $expectedList[] = new TestRESTObject("$i", "TEST $i", "test$i@test.com");
        }

        $this->assertEquals($this->converter->convert($expectedList), $result);


    }


    public function testCanCallPostMethod() {

        $_SERVER["REQUEST_METHOD"] = "POST";
        URLHelper::setTestURL("/api/TestREST");

        $service = new TestREST();
        $requestParameters = HttpRequest::instance(true)->getAllValues();
        $requestParameters["payload"] = array("id" => 11, "name" => "TEST 11", "email" => "test11@test.com");

        $result = $service->handleRequest($requestParameters);

        $this->assertEquals($this->converter->convert(new TestRESTObject(11, "TEST 11", "test11@test.com", "POSTED")), $result);


    }


    public function testCanCallPutMethod() {

        $_SERVER["REQUEST_METHOD"] = "PUT";
        URLHelper::setTestURL("/api/TestREST/11");

        $service = new TestREST();
        $requestParameters = HttpRequest::instance(true)->getAllValues();
        $requestParameters["payload"] = array("id" => 11, "name" => "NEWTEST 11", "email" => "newtest11@test.com");

        $result = $service->handleRequest($requestParameters);

        $this->assertEquals($this->converter->convert(new TestRESTObject(11, "NEWTEST 11", "newtest11@test.com", "PUT 11")), $result);


    }


    public function testCanCallPatchMethod() {

        $_SERVER["REQUEST_METHOD"] = "PATCH";
        URLHelper::setTestURL("/api/TestREST/11");

        $service = new TestREST();
        $requestParameters = HttpRequest::instance(true)->getAllValues();
        $requestParameters["payload"] = array("name" => "PATCHEDTEST 11", "email" => "patchedtest11@test.com");

        $result = $service->handleRequest($requestParameters);

        $this->assertEquals($this->converter->convert(new TestRESTObject("11", "PATCHEDTEST 11", "patchedtest11@test.com", "PATCHED 11")), $result);

    }


    public function testCanCallDeleteMethod() {

        $_SERVER["REQUEST_METHOD"] = "DELETE";
        URLHelper::setTestURL("/api/TestREST/5");

        $service = new TestREST();

        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals($this->converter->convert(new TestRESTObject("5", "TEST 5", "test5@test.com", "DELETED 5")), $result);


    }


    public function testCanCallMethodsWithNestedPaths() {

        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/nested/3");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals($this->converter->convert(new TestRESTObject("3", "TEST 3", "test3@test.com", "GET NESTED SINGLE")), $result);


        $_SERVER["REQUEST_METHOD"] = "POST";
        URLHelper::setTestURL("/api/TestREST/nested");

        $service = new TestREST();
        $requestParameters = HttpRequest::instance(true)->getAllValues();
        $requestParameters["payload"] = array("id" => 11, "name" => "TEST 11", "email" => "test11@test.com");

        $result = $service->handleRequest($requestParameters);

        $this->assertEquals($this->converter->convert(new TestRESTObject(11, "TEST 11", "test11@test.com", "NESTED POSTED")), $result);


        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/nested/count");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals(100, $result);


    }

    public function testCanCallMethodsWithMultipleVariablePaths() {


        $_SERVER["REQUEST_METHOD"] = "GET";
        URLHelper::setTestURL("/api/TestREST/nested/3/mark");

        $service = new TestREST();
        $result = $service->handleRequest(HttpRequest::instance(true)->getAllValues());

        $this->assertEquals($this->converter->convert(new TestRESTObject("3", "mark", "test3@test.com", "GET NESTED VARIABLE SINGLE")), $result);


    }


}
