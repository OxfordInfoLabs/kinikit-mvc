<?php

namespace Kinikit\MVC\Framework\HTTP;

use Kinikit\Core\Configuration;
use Kinikit\Core\Util\Logging\Logger;

/**
 * NB:  THIS CLASS IS REPLACING THE OLD SESSION CLASS TO WORK AROUND A CLASS CLASH WITH OTHER SOFTWARE
 *
 * Convenient static class for accessing the http session.  Adds built in methods for the core stuff like getting
 * logged in user as well as a generic get / set property for user use.
 *
 */
class HttpSession {

    private static $instance;
    private $sessionData = null;

    // Public constructor if auto wiring
    public function __construct() {
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
     * @param unknown_type $key
     */
    public function getValue($key, $lockSession = false) {
        $allValues = $this->getAllValues($lockSession);
        if (isset($allValues[$key])) {
            return $allValues[$key];
        } else {
            return null;
        }
    }

    /**
     * Get all values - return as array and close session to prevent threading locks.
     */
    public function getAllValues($lockSession = false) {

        if (!$this->sessionData || $lockSession) {
            $this->startSession();
            $this->sessionData = isset($_SESSION) ? $_SESSION : array();
            if (!$lockSession)
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
     * Save and close the session - does nothing
     * but is kept for legacy compatibility - superceded by logic above
     *
     */
    public function saveAndClose() {
    }


    /**
     * Force a reload of the session
     */
    public function reload() {
        $this->sessionData = null;
        $this->getAllValues();
    }


    // Start the session
    private function startSession() {

        if (session_id() == "" || session_id() == null) {
            if (isset ($_REQUEST ["HTTPSESSID"])) {
                session_id($_REQUEST ["HTTPSESSID"]);
            } else if (isset ($_REQUEST ["PHPSESSID"])) {
                session_id($_REQUEST ["PHPSESSID"]);
            }
        }

        $cookieDomain = Configuration::instance()->getParameter('session.cookie.domain');
        if ($cookieDomain) {

            if ($cookieDomain == "WILDCARD") {
                $host = isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "";
                if ($host) {
                    $splitHost = explode(".", $host);
                    $tld = array_pop($splitHost);
                    $domain = array_pop($splitHost);
                    $cookieDomain = ".$domain.$tld";
                } else {
                    return;
                }
            }


            ini_set("session.cookie_domain", $cookieDomain);
        }

        if (!headers_sent())
            @session_start();

    }


    /**
     * Enforce a singleton session object
     *
     * @return HttpSession
     */
    public static function instance() {

        if (self::$instance == null) {
            $class = get_class(new static());
            self::$instance = new $class();
        }

        return self::$instance;
    }

}

?>
