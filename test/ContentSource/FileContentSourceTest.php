<?php

namespace Kinikit\MVC\ContentSource;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\Configuration\FileResolver;
use Kinikit\Core\Exception\FileNotFoundException;

include_once "autoloader.php";

class FileContentSourceTest extends \PHPUnit\Framework\TestCase {


    public function setUp(): void {

        // Add mixture of configured paths
        Configuration::instance()->addParameter("search.paths", "../src;../vendor/symfony");

    }

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

    public function testFileResolverUsedToResolveFilesNotSuppliedWithAbsolutePaths() {


        $source = new FileContentSource("polyfill-ctype/bootstrap.php");
        $this->assertEquals("text/x-php", $source->getContentType());
        $this->assertTrue($source->getContentLength() > 0);

        // Check content.
        ob_start();
        $source->streamContent();
        $this->assertEquals(file_get_contents("../vendor/symfony/polyfill-ctype/bootstrap.php"), ob_get_contents());
        ob_end_clean();
    }

}
