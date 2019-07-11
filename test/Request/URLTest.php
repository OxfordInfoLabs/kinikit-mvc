<?php

namespace Kinikit\MVC\Request;

include_once "autoloader.php";


class URLTest extends \PHPUnit\Framework\TestCase {

    public function testCanGetProtocolHostPortAndSecureIndicatorForFullURL() {

        $url = new URL("http://www.google.com/home/test?myshopping=Hello");

        $this->assertEquals(URL::PROTOCOL_HTTP, $url->getProtocol());
        $this->assertEquals("www.google.com", $url->getHost());
        $this->assertEquals(80, $url->getPort());
        $this->assertFalse($url->isSecure());


        $url = new URL("https://www.google.com");

        $this->assertEquals(URL::PROTOCOL_HTTPS, $url->getProtocol());
        $this->assertEquals("www.google.com", $url->getHost());
        $this->assertEquals(443, $url->getPort());
        $this->assertTrue($url->isSecure());


        $url = new URL("http://localhost:8080");

        $this->assertEquals(URL::PROTOCOL_HTTP, $url->getProtocol());
        $this->assertEquals("localhost", $url->getHost());
        $this->assertEquals(8080, $url->getPort());
        $this->assertFalse($url->isSecure());


        $url = new URL("https://microsoft.com:700");
        $this->assertEquals("microsoft.com", $url->getHost());
        $this->assertEquals(URL::PROTOCOL_HTTPS, $url->getProtocol());
        $this->assertEquals(700, $url->getPort());
        $this->assertTrue($url->isSecure());


    }


    public function testCanGetPathSegmentsEitherAsAnArrayOrByIndex() {

        $url = new URL("http://www.google.com/home/mark/test?myshopping=Hello");
        $this->assertEquals(3, $url->getPathSegmentCount());
        $this->assertEquals(["home", "mark", "test"], $url->getPathSegments());
        $this->assertEquals("home", $url->getFirstPathSegment());
        $this->assertEquals("test", $url->getLastPathSegment());
        $this->assertEquals("home", $url->getPathSegment(0));
        $this->assertEquals("mark", $url->getPathSegment(1));
        $this->assertEquals("test", $url->getPathSegment(2));

    }


    public function testCanGetQueryParameters() {

        $url = new URL("http://www.google.com/home/mark/test?myshopping=Hello&myPlunger=bing1234");

        $this->assertEquals(2, sizeof($url->getQueryParameters()));
        $this->assertEquals(array("myshopping" => "Hello", "myPlunger" => "bing1234"), $url->getQueryParameters());
        $this->assertEquals("Hello", $url->getQueryParameter("myshopping"));
        $this->assertEquals("bing1234", $url->getQueryParameter("myPlunger"));

    }

}
