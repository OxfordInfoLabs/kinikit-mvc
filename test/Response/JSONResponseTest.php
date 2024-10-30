<?php

namespace Kinikit\MVC\Response;

use PHPUnit\Framework\TestCase;

include_once "autoloader.php";

class JSONResponseTest extends TestCase {

    public function testPrimitiveValuesAreStreamedCorrectly() {


        $jsonResponse = new JSONResponse(12);

        ob_start();
        $jsonResponse->streamContent();
        $this->assertEquals(12, ob_get_contents());
        ob_end_clean();


        $jsonResponse = new JSONResponse(true);

        ob_start();
        $jsonResponse->streamContent();
        $this->assertEquals("true", ob_get_contents());
        ob_end_clean();


        $jsonResponse = new JSONResponse(12.4);

        ob_start();
        $jsonResponse->streamContent();
        $this->assertEquals(12.4, ob_get_contents());
        ob_end_clean();


        $jsonResponse = new JSONResponse("Hello world of fun and games");

        ob_start();
        $jsonResponse->streamContent();
        $this->assertEquals('"Hello world of fun and games"', ob_get_contents());
        ob_end_clean();

    }


    public function testObjectValuesAreStreamedCorrectly() {

        $testJSONObject = new TestJSONObject("Markus", "3 The Lane", "07777 898989");

        $jsonResponse = new JSONResponse($testJSONObject);

        ob_start();
        $jsonResponse->streamContent();
        $this->assertEquals('{"name":"Markus","address":"3 The Lane","phone":"07777 898989"}', ob_get_contents());
        ob_end_clean();

    }

    /**
     * @runInSeparateProcess
     */
    public function testCustomHeadersAreAddedCorrectlyIfSupplied() {

        $testJSONObject = new TestJSONObject("Markus", "3 The Lane", "07777 898989");

        $jsonResponse = new JSONResponse($testJSONObject, 200, "application/json", [
            Headers::HEADER_CACHE_CONTROL => "public, max-age=10",
            Headers::HEADER_LOCATION => "https://hello.world"
        ]);

        ob_start();
        $jsonResponse->send();
        $this->assertEquals('{"name":"Markus","address":"3 The Lane","phone":"07777 898989"}', ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("application/json", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(63, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals("public, max-age=10", $headers->get(Headers::HEADER_CACHE_CONTROL));
        $this->assertEquals("https://hello.world", $headers->get(Headers::HEADER_LOCATION));

    }

}
