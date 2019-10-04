<?php

namespace Kinikit\MVC\Session;

use Kinikit\Core\Configuration\Configuration;

/**
 * Convenient static class for accessing the http session.  Adds built in methods for the core stuff like getting
 * logged in user as well as a generic get / set property for user use.
 *
 * @noProxy
 */
class PHPSession implements Session {

    private $sessionData = null;

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


    // Start the session
    private function startSession() {

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


}

?>
