<?php

namespace Kinikit\MVC\Response;

include_once "autoloader.php";


class HeadersTest extends \PHPUnit\Framework\TestCase {


    /**
     * @runInSeparateProcess
     */
    public function testCanGetAndSetResponseHeaders() {

        $headers = new Headers();
        $headers->set(Headers::HEADER_CONTENT_TYPE, "text/javascript");

        $this->assertStringContainsString("text/javascript", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(1, sizeof($headers->getAll()));

        $this->assertEquals(1, sizeof(xdebug_get_headers()));
        $this->assertStringContainsString("Content-type: text/javascript", xdebug_get_headers()[0]);


        $headers->set(Headers::HEADER_CONTENT_LENGTH, 300);
        $this->assertStringContainsString("300", $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals(2, sizeof($headers->getAll()));


        $this->assertEquals(2, sizeof(xdebug_get_headers()));
        $this->assertStringContainsString("Content-type: text/javascript", xdebug_get_headers()[0]);
        $this->assertStringContainsString("300", xdebug_get_headers()[1]);


        // Now try adding a later one
        $headers->set(Headers::HEADER_CONTENT_TYPE, "text/html");
        $this->assertEquals(2, sizeof(xdebug_get_headers()));
        $this->assertStringContainsString("300", xdebug_get_headers()[0]);
        $this->assertStringContainsString("Content-type: text/html", xdebug_get_headers()[1]);


    }


    /**
     * @runInSeparateProcess
     */
    public function testCanAddMultipleValuesWhereSupported() {

        $headers = new Headers();

        $headers->set(Headers::HEADER_SET_COOKIE, "MARK=test");
        $headers->set(Headers::HEADER_SET_COOKIE, "JOHN=bing");
        $headers->set(Headers::HEADER_SET_COOKIE, "CLARE=smile");

        $allHeaders = $headers->getAll();
        $this->assertEquals(1, sizeof($allHeaders));

        $cookies = $headers->get(Headers::HEADER_SET_COOKIE);
        $this->assertEquals(3, sizeof($cookies));
        $this->assertEquals("CLARE=smile", $cookies[0]);
        $this->assertEquals("JOHN=bing", $cookies[1]);
        $this->assertEquals("MARK=test", $cookies[2]);


    }

    /**
     * @runInSeparateProcess
     */
//    public function testCanRemoveHeaders() {
//
//        $headers = new Headers();
//        $headers->set(Headers::HEADER_SET_COOKIE, "MARK=test");
//        $headers->set(Headers::HEADER_SET_COOKIE, "JOHN=bing");
//        $headers->set(Headers::HEADER_SET_COOKIE, "CLARE=smile");
//        $headers->set(Headers::HEADER_CACHE_CONTROL, "public");
//
//        $allHeaders = $headers->getAll();
//        $this->assertEquals(2, sizeof($allHeaders));
//
//        // Remove cache control header
//        $headers->remove(Headers::HEADER_CACHE_CONTROL);
//
//        $allHeaders = $headers->getAll();
//        $this->assertEquals(1, sizeof($allHeaders));
//        $this->assertEquals(3, sizeof($headers->get(Headers::HEADER_SET_COOKIE)));
//
//        $headers->remove(Headers::HEADER_SET_COOKIE);
//
//        $allHeaders = $headers->getAll();
//        $this->assertEquals(0, sizeof($allHeaders));
//
//
//    }

}
