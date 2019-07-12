<?php

namespace Kinikit\MVC\ContentSource;


use Kinikit\Core\Exception\FileNotFoundException;

class FileContentSourceTest extends \PHPUnit\Framework\TestCase {


    public function testCanCreateFileContentSource() {

        $source = new FileContentSource(__DIR__ . "/test-png.png");
        $this->assertEquals("image/png", $source->getContentType());
        $this->assertEquals(8055, $source->getContentLength());

        // Check content.
        ob_start();
        $source->streamContent();
        $this->assertEquals(file_get_contents(__DIR__ . "/test-png.png"), ob_get_contents());
        ob_end_clean();

    }

    public function testFileNotFoundExceptionRaisedIfAttemptToConstructFileContentSourceWithNonExistentFile() {

        try {
            new FileContentSource(__DIR__ . "/idontexist.pdf");
            $this->fail("Should have thrown here");
        } catch (FileNotFoundException $e) {
            // Success
            $this->assertTrue(true);
        }

    }

}
