<?php

namespace Kinikit\MVC\Session;

include_once "autoloader.php";

/**
 * Session test class.   Tests session capabilities
 *
 */
class PHPSessionTest extends \PHPUnit\Framework\TestCase {


    private $session;

    public function setUp(): void {
        $this->session = new PHPSession();
    }

    public function testSettingAValueActuallySetsTheSessionValue() {
        $_SESSION = array();
        $this->assertFalse(isset ($_SESSION ["mark"]));

        $this->session->setValue("mark", "monkey");
        $this->assertEquals("monkey", $this->session->getValue("mark"));
        $this->assertEquals("monkey", $_SESSION ["mark"]);

    }

    public function testGettingANonExistentValueReturnsNull() {
        $this->assertNull($this->session->getValue("joebloggs"));
    }

    public function testCanClearWholeSession() {
        $this->session->setValue("markus", "test");
        $this->session->setValue("pookie", "boo");
        $this->session->setValue("jumper", "school");

        $this->assertEquals("test", $this->session->getValue("markus"));
        $this->assertEquals("boo", $this->session->getValue("pookie"));
        $this->assertEquals("school", $this->session->getValue("jumper"));

        // Clear all session variables
        $this->session->clearAll();

        $this->assertNull($this->session->getValue("markus"));
        $this->assertNull($this->session->getValue("pookie"));
        $this->assertNull($this->session->getValue("jumper"));

    }

}

?>
