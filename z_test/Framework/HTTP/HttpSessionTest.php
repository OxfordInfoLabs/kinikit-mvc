<?php

namespace Kinikit\MVC\Framework\HTTP;

include_once "autoloader.php";

/**
 * Session test class.   Tests session capabilities
 *
 */
class HttpSessionTest extends \PHPUnit\Framework\TestCase {

    public function testSettingAValueActuallySetsTheSessionValue() {
        $_SESSION = array();
        $this->assertFalse(isset ($_SESSION ["mark"]));

        HttpSession::instance()->setValue("mark", "monkey");
        $this->assertEquals("monkey", HttpSession::instance()->getValue("mark"));
        $this->assertEquals("monkey", $_SESSION ["mark"]);

    }

    public function testGettingANonExistentValueReturnsNull() {
        $this->assertNull(HttpSession::instance()->getValue("joebloggs"));
    }

    public function testCanClearWholeSession() {
        HttpSession::instance()->setValue("markus", "test");
        HttpSession::instance()->setValue("pookie", "boo");
        HttpSession::instance()->setValue("jumper", "school");

        $this->assertEquals("test", HttpSession::instance()->getValue("markus"));
        $this->assertEquals("boo", HttpSession::instance()->getValue("pookie"));
        $this->assertEquals("school", HttpSession::instance()->getValue("jumper"));

        // Clear all session variables
        HttpSession::instance()->clearAll();

        $this->assertNull(HttpSession::instance()->getValue("markus"));
        $this->assertNull(HttpSession::instance()->getValue("pookie"));
        $this->assertNull(HttpSession::instance()->getValue("jumper"));

    }

}

?>
