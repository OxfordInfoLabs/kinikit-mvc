<?php

namespace Kinikit\MVC\Session;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\DependencyInjection\Container;
use Kinikit\Core\Testing\MockObjectProvider;
use Kinikit\MVC\Objects\TestRESTObject;

include_once "autoloader.php";

/**
 * Session test class.   Tests session capabilities
 *
 */
class PHPSessionTest extends \PHPUnit\Framework\TestCase {


    private $session;


    private $previousSessionId;

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
        $mockConfigHandler = $mockObjectProvider->getMockInstance(SessionConfigHandler::class);

        $session = new PHPSession($mockConfigHandler);
        $session->getAllValues();


        $this->assertTrue($mockConfigHandler->methodWasCalled("setCookieParameters", [
            PHPSession::DEFAULT_COOKIE_LIFETIME, PHPSession::DEFAULT_COOKIE_PATH, NULL, PHPSession::DEFAULT_COOKIE_SECURE,
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
        $mockConfigHandler = $mockObjectProvider->getMockInstance(SessionConfigHandler::class);

        $session = new PHPSession($mockConfigHandler);
        $session->getAllValues();

        $this->assertTrue($mockConfigHandler->methodWasCalled("setCookieParameters", [
            PHPSession::DEFAULT_COOKIE_LIFETIME, PHPSession::DEFAULT_COOKIE_PATH, ".tester.com", PHPSession::DEFAULT_COOKIE_SECURE,
            PHPSession::DEFAULT_COOKIE_HTTP_ONLY, PHPSession::DEFAULT_COOKIE_SAME_SITE
        ]));


    }


    /**
     * @runInSeparateProcess
     */
    public function testStartingSessionSetsReferrerDomainsCorrectly() {


        // Check default values first
        $_SERVER["HTTP_REFERER"] = "http://mytestreferer.interaction.com/myhelper";

        Configuration::instance()->addParameter("session.cookie.domain", "REFERRER");

        /**
         * @var MockObjectProvider $mockObjectProvider
         */
        $mockObjectProvider = Container::instance()->get(MockObjectProvider::class);
        $mockConfigHandler = $mockObjectProvider->getMockInstance(SessionConfigHandler::class);

        $session = new PHPSession($mockConfigHandler);
        $session->getAllValues();

        $this->assertTrue($mockConfigHandler->methodWasCalled("setCookieParameters", [
            PHPSession::DEFAULT_COOKIE_LIFETIME, PHPSession::DEFAULT_COOKIE_PATH, "mytestreferer.interaction.com", PHPSession::DEFAULT_COOKIE_SECURE,
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
        $mockConfigHandler = $mockObjectProvider->getMockInstance(SessionConfigHandler::class);

        $session = new PHPSession($mockConfigHandler);
        $session->getAllValues();

        $this->assertTrue($mockConfigHandler->methodWasCalled("setCookieParameters", [
            3600, "/test", ".tester.com", false,
            false, "Test"
        ]));


    }


    /**
     * @runInSeparateProcess
     */
    public function testCanJoinExistingSessionById() {

        $mockConfigHandler = MockObjectProvider::instance()->getMockInstance(SessionConfigHandler::class);

        // Create a new session
        $session = new PHPSession($mockConfigHandler);
        $session->setValue("test", "Bingo");

        $firstSessionId = $session->getId();

        // Make new session
        $session = new PHPSession($mockConfigHandler);
        $this->assertNotEquals($session->getId(), $firstSessionId);

        // Join original session
        $session->join($firstSessionId);

        // check match
        $this->assertEquals($session->getId(), $firstSessionId);


    }

    /**
     * @runInSeparateProcess
     */
    public function testSettingCustomSessionSavePathInConfigSetsIniCorrectly() {

        Configuration::instance()->addParameter("session.save.path", __DIR__);

        $mockConfigHandler = MockObjectProvider::instance()->getMockInstance(SessionConfigHandler::class);

        // Create a new session and set a value
        $session = new PHPSession($mockConfigHandler);
        $session->setValue("test", "Bingo");

        $this->assertEquals(__DIR__, ini_get("session.save_path"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSettingCustomSessionSaveHandlerSetsIniCorrectly(){
        Configuration::instance()->addParameter("session.save.handler", "files");

        $mockConfigHandler = MockObjectProvider::instance()->getMockInstance(SessionConfigHandler::class);

        // Create a new session and set a value
        $session = new PHPSession($mockConfigHandler);
        $session->setValue("test", "Bingo");

        $this->assertEquals("files", ini_get("session.save_handler"));
    }

    /**
     * @runInSeparateProcess
     */
    public function testSettingCustomSessionSaveHandlerClassInConfigSetsSaveHandlerCorrectlyWithNewInstance() {

        Configuration::instance()->addParameter("session.save.handler.class", TestRESTObject::class);

        $mockConfigHandler = MockObjectProvider::instance()->getMockInstance(SessionConfigHandler::class);

        // Create a new session and set a value
        $session = new PHPSession($mockConfigHandler);
        $session->setValue("test", "Bingo");

        $this->assertTrue($mockConfigHandler->methodWasCalled("setSaveHandler", [
            Container::instance()->get(TestRESTObject::class), true
        ]));

    }


}

?>
