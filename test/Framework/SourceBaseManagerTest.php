<?php

namespace Kinikit\MVC\Framework;

include_once "autoloader.php";

/**
 * Test cases for the overloadable resource utility class.
 * This essentially looks in 2 seperate base locations for a given file path
 * and returns the first occurance it finds in the order defined.
 */
class SourceBaseManagerTest extends \PHPUnit\Framework\TestCase {

    public function tearDown() {
        SourceBaseManager::instance()->setSourceBases(array(".", __DIR__ . "/..", __DIR__ . "/../../WebServices"));
    }

    public function testPassingSingleSearchPathResolvesAllFilesToThisSearchPathRegardlessOfExistence() {

        // Set a single path initially
        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1"));

        // Check any files get returned correctly with search path prefix
        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("common.txt"));
        $this->assertEquals("Framework/resourcepath1/path1and2.txt", SourceBaseManager::resolvePath("path1and2.txt"));
        $this->assertEquals("Framework/resourcepath1/path2only.txt", SourceBaseManager::resolvePath("path2only.txt"));

        // Check a hypothetical nested path
        $this->assertEquals("Framework/resourcepath1/test/new/path", SourceBaseManager::resolvePath("test/new/path"));

    }

    public function testPassingTwoSearchPathsChecksFirstPathAndThenSecondPathReturningAccordingly() {

        // Set a dual path
        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1", "Framework/resourcepath2"));

        // Check files in search path 1 get correctly resolved to search path 1
        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("common.txt"));
        $this->assertEquals("Framework/resourcepath1/path1and2.txt", SourceBaseManager::resolvePath("path1and2.txt"));

        // Check files in search path2 get correctly resolved to search path 2
        $this->assertEquals("Framework/resourcepath2/path2only.txt", SourceBaseManager::resolvePath("path2only.txt"));

        // Check dodgy files get resolved to search path 1
        $this->assertEquals("Framework/resourcepath1/dodgy.txt", SourceBaseManager::resolvePath("dodgy.txt"));

    }

    public function testPassingThreeSearchPathsChecksAllPathsAndReturnsAccordingly() {

        // Set a triple path
        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath3", "Framework/resourcepath2", "Framework/resourcepath1"));

        // Check files in search path 3 get correctly resolved to search path 1
        $this->assertEquals("Framework/resourcepath3/common.txt", SourceBaseManager::resolvePath("common.txt"));
        $this->assertEquals("Framework/resourcepath3/path3only.txt", SourceBaseManager::resolvePath("path3only.txt"));

        // Check files in search path 2 get correctly resolved to search path 2
        $this->assertEquals("Framework/resourcepath2/path2only.txt", SourceBaseManager::resolvePath("path2only.txt"));
        $this->assertEquals("Framework/resourcepath2/path1and2.txt", SourceBaseManager::resolvePath("path1and2.txt"));

        // Check files in search path1 get correctly resolved to search path 1
        $this->assertEquals("Framework/resourcepath1/path1only.txt", SourceBaseManager::resolvePath("path1only.txt"));

        // Check dodgy files get resolved to search path 3
        $this->assertEquals("Framework/resourcepath3/dodgy.txt", SourceBaseManager::resolvePath("dodgy.txt"));

    }

    public function testExtraneousSlashesAreStrippedOut() {

        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1"));

        // Check extra slashes are ignored
        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("common.txt"));
        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("/common.txt"));

        // Now put slash in search path and check this makes no difference
        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1/"));

        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("common.txt"));
        $this->assertEquals("Framework/resourcepath1/common.txt", SourceBaseManager::resolvePath("/common.txt"));

    }

    public function testCanCheckIfPathHasBeenResolved() {

        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath1"));

        // Check first that none of the proposed paths have been resolved yet
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("common.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("path1only.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("dodgy.txt"));

        SourceBaseManager::resolvePath("common.txt");

        // Check only common has been resolved
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("common.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("path1only.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("dodgy.txt"));

        SourceBaseManager::resolvePath("path1only.txt");

        // Check two entries now resolved
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("common.txt"));
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("path1only.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("dodgy.txt"));

        SourceBaseManager::resolvePath("dodgy.txt");

        // Check all now resolved
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("common.txt"));
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("path1only.txt"));
        $this->assertTrue(SourceBaseManager::instance()->hasPathBeenResolved("dodgy.txt"));

        // Check that a new set search paths resets the resolved paths
        SourceBaseManager::instance()->setSourceBases(array("Framework/resourcepath2"));

        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("common.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("path1only.txt"));
        $this->assertFalse(SourceBaseManager::instance()->hasPathBeenResolved("dodgy.txt"));

    }

}

?>