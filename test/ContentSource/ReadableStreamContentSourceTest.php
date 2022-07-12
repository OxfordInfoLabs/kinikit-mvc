<?php


namespace Kinikit\MVC\ContentSource;


use Kinikit\Core\Stream\String\ReadOnlyStringStream;

class ReadableStreamContentSourceTest extends \PHPUnit\Framework\TestCase {

    public function testCanCreateReadableStreamContentSource() {

        $stream = new ReadOnlyStringStream("HELLO WORLD OF FUN AND GAMING");

        $contentSource = new ReadableStreamContentSource($stream, "text/html");
        $this->assertEquals("text/html", $contentSource->getContentType());
        $this->assertEquals(-1, $contentSource->getContentLength());

        // Check content.
        ob_start();
        $contentSource->streamContent();
        $this->assertEquals("HELLO WORLD OF FUN AND GAMING", ob_get_contents());
        ob_end_clean();

    }

}