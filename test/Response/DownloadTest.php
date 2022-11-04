<?php


namespace Kinikit\MVC\Response;

use Kinikit\Core\Exception\WrongParametersException;
use Kinikit\MVC\ContentSource\StringContentSource;

include_once "autoloader.php";

class DownloadTest extends \PHPUnit\Framework\TestCase {


    /**
     * @runInSeparateProcess
     */
    public function testCanInitialiseAndSendSimpleResponseWithAString() {

        $simpleResponse = new Download("Hello World!", "myfile.txt");

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/html", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals('attachment; filename="myfile.txt"', $headers->get(Headers::HEADER_CONTENT_DISPOSITION));
        $this->assertEquals(200, http_response_code());

    }


    /**
     * @runInSeparateProcess
     */
    public function testCanInitialiseAndSendDownloadWithAContentSource() {

        $simpleResponse = new Download(new StringContentSource("Hello World!", "text/javascript"), "newone.js", 503);

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/javascript", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals('attachment; filename="newone.js"', $headers->get(Headers::HEADER_CONTENT_DISPOSITION));
        $this->assertEquals(503, http_response_code());

    }


    /**
     * @runInSeparateProcess
     */
    public function testCanSendCustomHeadersAsArrayOnInitialisationAndTheseArePassedOnSend() {

        $simpleResponse = new Download(new StringContentSource("Hello World!", "text/javascript"), "newone.js", 503, [
            Headers::HEADER_CACHE_CONTROL => "public, max-age=10",
            Headers::HEADER_LOCATION => "https://hello.world"
        ]);

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/javascript", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals('attachment; filename="newone.js"', $headers->get(Headers::HEADER_CONTENT_DISPOSITION));
        $this->assertEquals("public, max-age=10", $headers->get(Headers::HEADER_CACHE_CONTROL));
        $this->assertEquals("https://hello.world", $headers->get(Headers::HEADER_LOCATION));
    }

    public function testExceptionRaisedIfAttemptToCreateDownloadWithInvalidSource() {

        try {
            new Download([], "hello.world");
            $this->fail("Should have thrown here");
        } catch (WrongParametersException $e) {
            $this->assertTrue(true);
        }

    }

}
