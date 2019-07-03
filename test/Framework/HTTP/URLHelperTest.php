<?php

namespace Kinikit\MVC\Framework\HTTP;

include_once "autoloader.php";

/**
 * Test cases for the url handler
 *
 */
class URLHelperTest extends \PHPUnit\Framework\TestCase {
	
	public function testCanGetUrlSegments() {
		
		URLHelper::unsetTestURL ();
		
		$url1 = new URLHelper ( "http://testsite:456/3/the/helper" );
		$this->assertEquals ( "3", $url1->getSegment ( 0 ) );
		$this->assertEquals ( "the", $url1->getSegment ( 1 ) );
		$this->assertEquals ( "helper", $url1->getSegment ( 2 ) );
		$this->assertEquals ( "3", $url1->getFirstSegment () );
		$this->assertEquals ( "helper", $url1->getLastSegment () );
		$this->assertEquals ( 3, $url1->getSegmentCount () );
		
		$url2 = new URLHelper ( "/mysite/other/segment/" );
		$this->assertEquals ( "mysite", $url2->getSegment ( 0 ) );
		$this->assertEquals ( "other", $url2->getSegment ( 1 ) );
		$this->assertEquals ( "segment", $url2->getSegment ( 2 ) );
		$this->assertEquals ( "mysite", $url2->getFirstSegment () );
		$this->assertEquals ( "segment", $url2->getLastSegment () );
		$this->assertEquals ( 3, $url2->getSegmentCount () );
		
		$url3 = new URLHelper ( "http://thesite/my/dodgy?queryparam=4" );
		$this->assertEquals ( "my", $url3->getSegment ( 0 ) );
		$this->assertEquals ( "dodgy", $url3->getSegment ( 1 ) );
		$this->assertEquals ( "my", $url3->getFirstSegment () );
		$this->assertEquals ( "dodgy", $url3->getLastSegment () );
		$this->assertEquals ( 2, $url3->getSegmentCount () );
		
		$url4 = new URLHelper ( "/thesite/my/dodgy/good?queryparam=5" );
		$this->assertEquals ( "thesite", $url4->getSegment ( 0 ) );
		$this->assertEquals ( "my", $url4->getSegment ( 1 ) );
		$this->assertEquals ( "dodgy", $url4->getSegment ( 2 ) );
		$this->assertEquals ( "good", $url4->getSegment ( 3 ) );
		$this->assertEquals ( "thesite", $url4->getFirstSegment () );
		$this->assertEquals ( "good", $url4->getLastSegment () );
		$this->assertEquals ( 4, $url4->getSegmentCount () );
	
	}
	
	public function testCanGetInstanceForCurrentURL() {
		
		$_SERVER ['REQUEST_URI'] = "http://testsite:456/3/the/helper";
		
		$urlHelper = URLHelper::getCurrentURLInstance ();
		$this->assertEquals ( "3", $urlHelper->getSegment ( 0 ) );
		$this->assertEquals ( "the", $urlHelper->getSegment ( 1 ) );
		$this->assertEquals ( "helper", $urlHelper->getSegment ( 2 ) );
		$this->assertEquals ( "3", $urlHelper->getFirstSegment () );
		$this->assertEquals ( "helper", $urlHelper->getLastSegment () );
		$this->assertEquals ( 3, $urlHelper->getSegmentCount () );
	
	}
	
	public function testCanGetFullURL() {
		
		$url1 = new URLHelper ( "http://testsite:456/3/the/helper" );
		$this->assertEquals ( "http://testsite:456/3/the/helper", $url1->getURL () );
		
		$url2 = new URLHelper ( "/thesite/my/dodgy/good?queryparam=5" );
		$this->assertEquals ( "/thesite/my/dodgy/good?queryparam=5", $url2->getURL () );
	
	}
	
	public function testCanGetAllSegments() {
		
		$url = new URLHelper ( "http://localhost:123/test/this/outfit/out/please?hello=4" );
		$allSegments = $url->getAllSegments ();
		$this->assertEquals ( 5, sizeof ( $allSegments ) );
		$this->assertEquals ( "test", $allSegments [0] );
		$this->assertEquals ( "this", $allSegments [1] );
		$this->assertEquals ( "outfit", $allSegments [2] );
		$this->assertEquals ( "out", $allSegments [3] );
		$this->assertEquals ( "please", $allSegments [4] );
	
	}
	
	public function testCanGetPartialURLFromSegment() {
		
		$url1 = new URLHelper ( "http://testsite:456/3/the/helper" );
		
		$partialString = $url1->getPartialURLFromSegment ( 1 );
		$this->assertEquals ( "the/helper", $partialString );
		
		$partialString = $url1->getPartialURLFromSegment ( 2 );
		$this->assertEquals ( "helper", $partialString );
		

	}
	
	public function testCanGetQueryString() {
		
		$url1 = new URLHelper ( "http://www.google.com?mark=yes&luke=no" );
		$this->assertEquals ( "?mark=yes&luke=no", $url1->getQueryString () );
		
		$url2 = new URLHelper ( "http://www.google.com?ben=mad" );
		$this->assertEquals ( "?ben=mad", $url2->getQueryString () );
		
		$url3 = new URLHelper ( "http://www.google.com" );
		$this->assertEquals ( "", $url3->getQueryString () );
	
	}
	
	public function testCanGetQueryParametersArray() {
		$url1 = new URLHelper ( "http://www.google.com?mark=yes&luke=no" );
		$this->assertEquals ( array ("mark" => "yes", "luke" => "no" ), $url1->getQueryParametersArray () );
		
		$url2 = new URLHelper ( "http://www.google.com?ben=mad" );
		$this->assertEquals ( array ("ben" => "mad" ), $url2->getQueryParametersArray () );
		
		$url3 = new URLHelper ( "http://www.google.com" );
		$this->assertEquals ( array (), $url3->getQueryParametersArray () );
	}

}

?>
