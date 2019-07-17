<?php


namespace Kinikit\MVC\Alias;

use Kinikit\MVC\Response\Redirect;

include_once "autoloader.php";

class AliasMapperTest extends \PHPUnit\Framework\TestCase {


    public function testNoneMatchesAreReturnedIntactFromMapURL() {

        $aliasMapper = new AliasMapper();

        $this->assertEquals("/bespoke/sub/nestedsimple", $aliasMapper->mapURL("/bespoke/sub/nestedsimple"));
        $this->assertEquals("/unknown", $aliasMapper->mapURL("/unknown"));


    }

    public function testMapURLReturnsMappedStringWhereAliasMatchesSimplePattern() {

        $aliasMapper = new AliasMapper();
        $mapping = $aliasMapper->mapURL("/magic");
        $this->assertEquals("/bespoke/sub/nestedsimple", $mapping);

        $mapping = $aliasMapper->mapURL("/test");
        $this->assertEquals("/rest", $mapping);


    }


    public function testMapURLReturnsMappedStringWhereSubstitutionLogicHasBeenUsed() {

        $aliasMapper = new AliasMapper();
        $mapping = $aliasMapper->mapURL("/wizard/Google");
        $this->assertEquals("/zone/simple/get?title=Google", $mapping);

        $mapping = $aliasMapper->mapURL("/wildcard/other/fragments?mynameisjohn");
        $this->assertEquals("/rest/other/fragments?mynameisjohn", $mapping);

    }


    /**
     * @runInSeparateProcess
     */
    public function testRedirectResponseIsReturnedIfStatusCodeSuppliedForInternalRequest() {

        $aliasMapper = new AliasMapper();
        $mapping = $aliasMapper->mapURL("/mark");
        $this->assertEquals(new Redirect("/zone/simple", false), $mapping);

        $mapping = $aliasMapper->mapURL("/john");
        $this->assertEquals(new Redirect("/zone/simple", true), $mapping);

    }

    /**
     * @runInSeparateProcess
     */
    public function testRedirectResponseIsReturnedIfExternalRedirectionSupplied() {

        $aliasMapper = new AliasMapper();
        $mapping = $aliasMapper->mapURL("/external");
        $this->assertEquals(new Redirect("https://www.google.com", true), $mapping);

        $mapping = $aliasMapper->mapURL("/temp");
        $this->assertEquals(new Redirect("http://www.microsoft.com", false), $mapping);

    }
}
