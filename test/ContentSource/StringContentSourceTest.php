<?php

namespace Kinikit\MVC\ContentSource;


class StringContentSourceTest extends \PHPUnit\Framework\TestCase {


    public function testCanCreateStringContentSource() {

        $source = new StringContentSource("Hello wonderful people");
        $this->assertEquals("text/html", $source->getContentType());
        $this->assertEquals(22, $source->getContentLength());

        // Check content.
        ob_start();
        $source->streamContent();
        $this->assertEquals("Hello wonderful people", ob_get_contents());
        ob_end_clean();


        $source = new StringContentSource("var test;", "text/javascript");
        $this->assertEquals("text/javascript", $source->getContentType());
        $this->assertEquals(9, $source->getContentLength());

        // Check content.
        ob_start();
        $source->streamContent();
        $this->assertEquals("var test;", ob_get_contents());
        ob_end_clean();


    }

}
