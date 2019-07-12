<?php


namespace Kinikit\MVC\Response;

use Kinikit\Core\Exception\WrongParametersException;
use Kinikit\MVC\ContentSource\StringContentSource;

include_once "autoloader.php";

class SimpleResponseTest extends \PHPUnit\Framework\TestCase {


    /**
     * @runInSeparateProcess
     */
    public function testCanInitialiseAndSendSimpleResponseWithAString() {

        $simpleResponse = new SimpleResponse("Hello World!");

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/html", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals(200, http_response_code());

    }


    /**
     * @runInSeparateProcess
     */
    public function testCanInitialiseAndSendSimpleResponseWithAStringAndOverriddenStatusCode() {

        $simpleResponse = new SimpleResponse("Hello World!", 503);

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/html", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals(503, http_response_code());

    }


    /**
     * @runInSeparateProcess
     */
    public function testCanInitialiseAndSendSimpleResponseWithAContentSource() {

        $simpleResponse = new SimpleResponse(new StringContentSource("Hello World!", "text/javascript"), 503);

        ob_start();
        $simpleResponse->send();
        $this->assertEquals("Hello World!", ob_get_contents());
        ob_end_clean();

        $headers = new Headers();
        $this->assertStringContainsString("text/javascript", $headers->get(Headers::HEADER_CONTENT_TYPE));
        $this->assertEquals(12, $headers->get(Headers::HEADER_CONTENT_LENGTH));
        $this->assertEquals(503, http_response_code());

    }

    public function testExceptionRaisedIfAttemptToCreateSimpleResponseWithInvalidSource() {

        try {
            new SimpleResponse([]);
            $this->fail("Should have thrown here");
        } catch (WrongParametersException $e) {
            $this->assertTrue(true);
        }

    }

}
