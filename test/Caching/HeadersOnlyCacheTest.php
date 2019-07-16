<?php


namespace Kinikit\MVC\Caching;


use Kinikit\Core\DependencyInjection\Container;
use Kinikit\MVC\Response\Headers;

class HeadersOnlyCacheTest extends \PHPUnit\Framework\TestCase {


    /**
     * @runInSeparateProcess
     */
    public function testCorrectHeadersAreWrittenByHeadersOnlyCacheWhenObjectCached() {

        /**
         * @var HeadersOnlyCache $headersOnlyCache
         */
        $headersOnlyCache = Container::instance()->get(HeadersOnlyCache::class);

        /**
         * @var Headers $responseHeaders
         */
        $responseHeaders = Container::instance()->get(Headers::class);


        $this->assertEquals(null, $responseHeaders->get(Headers::HEADER_CACHE_CONTROL));
        $this->assertEquals(null, $responseHeaders->get(Headers::HEADER_EXPIRES));
        $this->assertEquals(null, $responseHeaders->get(Headers::HEADER_LAST_MODIFIED));
        $this->assertEquals(null, $responseHeaders->get(Headers::HEADER_ETAG));

        $headersOnlyCache->cacheResult("http://www.google.com", 25, "Bingo");


        $this->assertEquals("public, max-age=1500, must-revalidate", $responseHeaders->get(Headers::HEADER_CACHE_CONTROL));

        $now = gmdate("D, d M Y", time());
        $expires = gmdate("D, d M Y", time() + 1500);

        $this->assertStringContainsString($expires, $responseHeaders->get(Headers::HEADER_EXPIRES));
        $this->assertStringContainsString($now, $responseHeaders->get(Headers::HEADER_LAST_MODIFIED));
        $this->assertNotNull($responseHeaders->get(Headers::HEADER_ETAG));


    }

}
