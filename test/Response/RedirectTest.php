<?php

namespace Kinikit\MVC\Response;

include_once "autoloader.php";

class RedirectTest extends \PHPUnit\Framework\TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testCanSendPermanentRedirect() {

        $redirect = new Redirect("http://www.google.com");

        ob_start();
        $redirect->send();
        $this->assertEquals("", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertNull($headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertNull($headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals("http://www.google.com", $headers->get(Headers::HEADER_LOCATION));
        $this->assertEquals(Response::RESPONSE_REDIRECT_PERMANENT, http_response_code());

    }


    /**
     * @runInSeparateProcess
     */
    public function testCanSendTemporaryRedirect() {

        $redirect = new Redirect("https://myspace.com", false);

        ob_start();
        $redirect->send();
        $this->assertEquals("", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertNull($headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertNull($headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals("https://myspace.com", $headers->get(Headers::HEADER_LOCATION));
        $this->assertEquals(Response::RESPONSE_REDIRECT_TEMPORARY, http_response_code());

    }

}
