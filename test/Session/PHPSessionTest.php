<?php

namespace Kinikit\MVC\Session;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Testing\MockObjectProvider;

include_once "autoloader.php";

/**
 * Session test class.   Tests session capabilities
 *
 */
class PHPSessionTest extends \PHPUnit\Framework\TestCase {


    private $session;

    public function setUp(): void {
        $this->session = Container::instance()->get(PHPSession::class);
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


    /**
     * @runInSeparateProcess
     */
    public function testStartingSessionSetsCookieValuesCorrectlyForDefaultValues() {


        // Check default values first
        $_SERVER["HTTP_HOST"] = "tester.com";

        /**
         * @var MockObjectProvider $mockObjectProvider
         */
        $mockObjectProvider = Container::instance()->get(MockObjectProvider::class);
        $mockCookieHandler = $mockObjectProvider->getMockInstance(SessionCookieHandler::class);

        $session = new PHPSession($mockCookieHandler);
        $session->getAllValues();


        $this->assertTrue($mockCookieHandler->methodWasCalled("setCookieParameters", [
            PHPSession::DEFAULT_COOKIE_LIFETIME, PHPSession::DEFAULT_COOKIE_PATH, "tester.com", PHPSession::DEFAULT_COOKIE_SECURE,
            PHPSession::DEFAULT_COOKIE_HTTP_ONLY, PHPSession::DEFAULT_COOKIE_SAME_SITE
        ]));


    }

    /**
     * @runInSeparateProcess
     */
    public function testStartingSessionSetsWildcardDomainsCorrectly() {


        // Check default values first
        $_SERVER["HTTP_HOST"] = "readytogo.tester.com";

        Configuration::instance()->addParameter("session.cookie.domain", "WILDCARD");

        /**
         * @var MockObjectProvider $mockObjectProvider
         */
        $mockObjectProvider = Container::instance()->get(MockObjectProvider::class);
        $mockCookieHandler = $mockObjectProvider->getMockInstance(SessionCookieHandler::class);

        $session = new PHPSession($mockCookieHandler);
        $session->getAllValues();

        $this->assertTrue($mockCookieHandler->methodWasCalled("setCookieParameters", [
            PHPSession::DEFAULT_COOKIE_LIFETIME, PHPSession::DEFAULT_COOKIE_PATH, ".tester.com", PHPSession::DEFAULT_COOKIE_SECURE,
            PHPSession::DEFAULT_COOKIE_HTTP_ONLY, PHPSession::DEFAULT_COOKIE_SAME_SITE
        ]));


    }

    /**
     * @runInSeparateProcess
     */
    public function testStartingSessionUsesConfigurationParamsInsteadOfDefaultsIfSet() {


        // Check default values first
        $_SERVER["HTTP_HOST"] = "readytogo.tester.com";

        Configuration::instance()->addParameter("session.cookie.domain", "WILDCARD");
        Configuration::instance()->addParameter("session.cookie.lifetime", 3600);
        Configuration::instance()->addParameter("session.cookie.path", "/test");
        Configuration::instance()->addParameter("session.cookie.secure", false);
        Configuration::instance()->addParameter("session.cookie.httponly", false);
        Configuration::instance()->addParameter("session.cookie.samesite", "Test");


        /**
         * @var MockObjectProvider $mockObjectProvider
         */
        $mockObjectProvider = Container::instance()->get(MockObjectProvider::class);
        $mockCookieHandler = $mockObjectProvider->getMockInstance(SessionCookieHandler::class);

        $session = new PHPSession($mockCookieHandler);
        $session->getAllValues();

        $this->assertTrue($mockCookieHandler->methodWasCalled("setCookieParameters", [
            3600, "/test", ".tester.com", false,
            false, "Test"
        ]));


    }



}

?>
