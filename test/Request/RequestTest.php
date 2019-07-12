<?php


namespace Kinikit\MVC\Request;

include_once "autoloader.php";

class RequestTest extends \PHPUnit\Framework\TestCase {


    public function testURLIsCorrectlyPopulatedInRequestForCurrentURL() {

        $_SERVER["HTTPS"] = 1;
        $_SERVER["SERVER_PORT"] = 443;
        $_SERVER['HTTP_HOST'] = "www.myspace.com";
        $_SERVER['REQUEST_URI'] = "/home/myshop";
        $_SERVER['QUERY_STRING'] = "hello=mark&test=11";

        $request = new Request(new Headers());

        $this->assertEquals(new URL("https://www.myspace.com/home/myshop?hello=mark&test=11"), $request->getUrl());


        unset($_SERVER["HTTPS"]);
        $_SERVER["SERVER_PORT"] = 8080;
        $_SERVER['HTTP_HOST'] = "www.myspace.com";
        $_SERVER['REQUEST_URI'] = "/";
        unset($_SERVER['QUERY_STRING']);

        $request = new Request(new Headers());

        $this->assertEquals(new URL("http://www.myspace.com:8080/"), $request->getUrl());


    }


    public function testSimpleRequestDataAndHeadersArePopulatedInRequest() {

        $_SERVER["REQUEST_METHOD"] = "PUT";
        unset($_SERVER["HTTP_X_FORWARDED_FOR"]);
        $_SERVER["REMOTE_ADDR"] = "33.55.77.65";
        $_SERVER["HTTP_REFERER"] = "http://myshopping.org?hello=2";

        $_SERVER['HTTP_ACCEPT'] = "javascript/json";
        $_SERVER["HTTP_ACCEPT_CHARSET"] = "utf-8";
        $_SERVER["HTTP_ACCEPT_ENCODING"] = "gzip";
        $_SERVER["HTTP_ACCEPT_LANGUAGE"] = "en";
        $_SERVER["HTTP_CONNECTION"] = "Keep-Alive";
        $_SERVER["HTTP_USER_AGENT"] = "Mozilla/4.5";

        $request = new Request(new Headers());

        $this->assertEquals("PUT", $request->getRequestMethod());
        $this->assertEquals("33.55.77.65", $request->getRemoteIPAddress());
        $this->assertEquals(new URL("http://myshopping.org?hello=2"), $request->getReferringURL());


        $headers = $request->getHeaders();
        $this->assertEquals("javascript/json", $headers->getAcceptContentType());
        $this->assertEquals("utf-8", $headers->getAcceptCharset());
        $this->assertEquals("gzip", $headers->getAcceptEncoding());
        $this->assertEquals("en", $headers->getAcceptLanguage());
        $this->assertEquals("Keep-Alive", $headers->getConnection());
        $this->assertEquals("Mozilla/4.5", $headers->getUserAgent());


        // Now try one with x forwarded for
        $_SERVER["HTTP_X_FORWARDED_FOR"] = "22.11.33.44";
        unset($_SERVER["REMOTE_ADDR"]);

        $request = new Request(new Headers());
        $this->assertEquals("22.11.33.44", $request->getRemoteIPAddress());
    }


    public function testCanGetParametersInSimpleGetTypeRequestSituation() {

        $_SERVER["REQUEST_METHOD"] = "GET";

        $_GET = array("mark" => "Hello monkey", "jane" => "Big boy");

        $request = new Request(new Headers());
        $this->assertEquals(array("mark" => "Hello monkey", "jane" => "Big boy"), $request->getParameters());
        $this->assertNull($request->getPayload());


        $_GET = array("mark" => "Hello%20monkey", "jane" => "Big%20boy");

        $request = new Request(new Headers());
        $this->assertEquals(array("mark" => "Hello monkey", "jane" => "Big boy"), $request->getParameters());
        $this->assertNull($request->getPayload());


    }


    public function testDataReadFromPHPInputIfNoneGetScenarioAndPayloadSupplied() {

        $_SERVER["REQUEST_METHOD"] = "PUT";

        $_GET = array("mark" => "Hello monkey", "jane" => "Big boy");

        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", "PAYLOAD");

        $request = new Request(new Headers());

        // Check payload and data.
        $this->assertEquals("PAYLOAD", $request->getPayload());
        $this->assertEquals(array("mark" => "Hello monkey", "jane" => "Big boy"), $request->getParameters());

    }

    public function testDataReadFromPHPInputIfNoneGetScenarioAndNormalFormStyleParamsPassed() {

        $_SERVER["REQUEST_METHOD"] = "PUT";

        $_GET = array("mark" => "Hello monkey", "jane" => "Big boy");

        stream_wrapper_unregister("php");
        stream_wrapper_register("php", "Kinikit\MVC\Request\MockPHPInputStream");
        file_put_contents("php://input", "james=XXX%203&paul=112");

        $request = new Request(new Headers());

        $this->assertNull($request->getPayload());

        // Check Get still gets added to params.
        $this->assertEquals(array("mark" => "Hello monkey", "jane" => "Big boy", "james" => "XXX 3", "paul" => 112), $request->getParameters());

    }


    public function testFileUploadsGetCorrectlyAddedAsFileUploadObjectsToRequest() {
        $_FILES = [
            "test1" => ["name" => "mytest.pdf", "type" => "application/pdf", "size" => 32344, "tmp_name" => "/tmp/bingo.test", "error" => 0],
            "test2" => ["name" => "mytest2.pdf", "type" => "application/pdf", "size" => 45, "tmp_name" => "/tmp/bingo.test", "error" => UPLOAD_ERR_PARTIAL]
        ];

        $request = new Request(new Headers());
        $this->assertEquals(2, sizeof($request->getFileUploads()));
        $fileupload1 = $request->getFileUpload("test1");
        $fileupload2 = $request->getFileUpload("test2");
        $this->assertEquals(["test1" => $fileupload1, "test2" => $fileupload2], $request->getFileUploads());

        $this->assertEquals("test1", $fileupload1->getParameterName());
        $this->assertEquals("mytest.pdf", $fileupload1->getClientFilename());
        $this->assertEquals("application/pdf", $fileupload1->getMimeType());
        $this->assertEquals(32344, $fileupload1->getSize());
        $this->assertEquals("/tmp/bingo.test", $fileupload1->getTemporaryFilePath());
        $this->assertEquals(FileUpload::STATUS_SUCCESS, $fileupload1->getStatus());
        $this->assertNull($fileupload1->getFailureReason());

        $this->assertEquals("test2", $fileupload2->getParameterName());
        $this->assertEquals("mytest2.pdf", $fileupload2->getClientFilename());
        $this->assertEquals("application/pdf", $fileupload2->getMimeType());
        $this->assertEquals(45, $fileupload2->getSize());
        $this->assertEquals("/tmp/bingo.test", $fileupload2->getTemporaryFilePath());
        $this->assertEquals(FileUpload::STATUS_FAILURE, $fileupload2->getStatus());
        $this->assertEquals(FileUpload::FAILED_PARTIALLY_UPLOADED, $fileupload2->getFailureReason());


    }


}
