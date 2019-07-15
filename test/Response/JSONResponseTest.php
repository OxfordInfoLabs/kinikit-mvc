<?php

namespace Kinikit\MVC\Response;

use PHPUnit\Framework\TestCase;

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

}
