<?php

namespace Kinikit\MVC\Session;

use Kinikit\Core\Configuration\Configuration;
use Kinikit\Core\DependencyInjection\Container;
use PSpell\Config;


/**
 * Convenient static class for accessing the http session.  Adds built in methods for the core stuff like getting
 * logged in user as well as a generic get / set property for user use.
 *
 * @noProxy
 */
class PHPSession implements Session {

    private $sessionData = null;

    private $sessionId = null;

    /**
     * @var SessionConfigHandler
     */
    private $sessionConfigHandler;

    // 8 hrs max length
    const DEFAULT_COOKIE_LIFETIME = 28800;
    const DEFAULT_COOKIE_PATH = "/";
    const DEFAULT_COOKIE_SECURE = true;
    const DEFAULT_COOKIE_HTTP_ONLY = true;
    const DEFAULT_COOKIE_SAME_SITE = "Strict";


    /**
     * PHPSession constructor.
     *
     * @param SessionConfigHandler $sessionConfigHandler
     */
    public function __construct($sessionConfigHandler) {
        $this->sessionConfigHandler = $sessionConfigHandler;
    }

    /**
     * Set a session value by key and invalidate the session data
     *
     * @param string $key
     * @param mixed $value
     */
    public function setValue($key, $value) {
        $this->startSession();
        $_SESSION [$key] = $value;
        $this->sessionData = null;
        session_write_close();
    }

    /**
     * Get a session value by key
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key) {
        $allValues = $this->getAllValues();
        if (isset($allValues[$key])) {
            return $allValues[$key];
        } else {
            return null;
        }
    }

    /**
     * Get all values - return as array and close session to prevent threading locks.
     */
    public function getAllValues() {

        if (!$this->sessionData) {
            $this->startSession();
            $this->sessionData = isset($_SESSION) ? $_SESSION : array();
            session_write_close();
        }

        return $this->sessionData;
    }


    /**
     * Clear the session of all values
     *
     */
    public function clearAll() {
        $this->startSession();
        $_SESSION = array();
        $this->sessionData = null;
        session_write_close();
    }


    /**
     * Force a reload of the session
     */
    public function reload() {
        $this->sessionData = null;
        $this->getAllValues();
    }


    /**
     * Regenerate a session - generally called in authentication
     * scenarios to prevent session fixation
     *
     * @return mixed
     */
    public function regenerate() {
        $this->startSession();
        try {
            session_regenerate_id(true);
        } catch (\ErrorException $e) {
            // OK as this will occur in testing
        }

        $this->sessionId = session_id();
        session_write_close();

        return $this->sessionId;
    }


    /**
     * Join an existing session by id.
     *
     * @param $id
     * @return mixed|void
     */
    public function join($id) {
        session_id($id);
        $this->startSession();
        session_write_close();
    }


    /**
     * Get the current session id
     *
     * @return mixed
     */
    public function getId() {
        return $this->sessionId;
    }


    /**
     * Check if a session is active
     *
     * @param $id
     * @return bool|void
     */
    public function isActive($id) {


        try {
            // Attempt to start the session in strict mode
            session_id($id);
            session_start(["use_strict_mode" => 1]);
            $active = session_id() == $id;
            session_write_close();

            // Reset to current session
            session_id($this->sessionId);
        } catch (\ErrorException $e) {
            $active = false;
        }

        return $active;
    }

    /**
     * Destroy a session by id
     *
     * @param $id
     * @return mixed
     */
    public function destroy($id) {
        session_id($id);
        session_start(["use_strict_mode" => 1]);
        if (session_id() == $id) {
            session_destroy();
        }

        // Reset to current session
        if ($this->sessionId == $id) {
            $this->sessionId = null;
        } else {
            session_id($this->sessionId);
        }
    }


    // Start the session
    private function startSession() {


        // Set save handler instance if a handler class supplied in config
        if ($sessionSaveHandlerClass = Configuration::instance()->getParameter("session.save.handler.class")) {
            $this->sessionConfigHandler->setSaveHandler(Container::instance()->get($sessionSaveHandlerClass), true);
        }

        // Set session save path and handlers if set in config
        if ($sessionSaveHandler = Configuration::instance()->getParameter("session.save.handler")) {
            ini_set("session.save_handler", $sessionSaveHandler);
        }

        // Set session save path and handlers if set in config
        if ($sessionSavePath = Configuration::instance()->getParameter("session.save.path")) {
            ini_set("session.save_path", $sessionSavePath);
        }



        // Resolve the cookie domain
        $cookieDomain = Configuration::instance()->getParameter('session.cookie.domain');
        $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";

        if ($cookieDomain) {

            if ($cookieDomain == "WILDCARD") {
                if ($host) {
                    $splitHost = explode(".", $host);
                    $tld = array_pop($splitHost);
                    $splitTld = explode(":", $tld);
                    $domain = array_pop($splitHost);
                    $cookieDomain = ".$domain.$splitTld[0]";
                } else {
                    return;
                }
            }

        } else {
            $cookieDomain = $host;
        }

        // Set other parameters oveloadable by config
        $cookieLifetime = Configuration::readParameter("session.cookie.lifetime") ?? self::DEFAULT_COOKIE_LIFETIME;
        $cookieSecure = Configuration::readParameter("session.cookie.secure") ?? self::DEFAULT_COOKIE_SECURE;
        $cookieHttpOnly = Configuration::readParameter("session.cookie.httponly") ?? self::DEFAULT_COOKIE_HTTP_ONLY;
        $cookiePath = Configuration::readParameter("session.cookie.path") ?? self::DEFAULT_COOKIE_PATH;
        $cookieSameSite = Configuration::readParameter("session.cookie.samesite") ?? self::DEFAULT_COOKIE_SAME_SITE;

        if (!headers_sent()) {
            $this->sessionConfigHandler->setCookieParameters($cookieLifetime, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly, $cookieSameSite);
            @session_start([
                "use_strict_mode" => 1
            ]);
            $this->sessionId = session_id();
        }
    }


}

