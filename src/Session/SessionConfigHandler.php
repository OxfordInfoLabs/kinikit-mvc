<?php


namespace Kinikit\MVC\Session;

/**
 * Class SessionCookieHandler
 *
 * @noProxy
 */
class SessionConfigHandler {

    /**
     * Set cookie parameters - simple wrapper around the session set cookie function for improved testing
     *
     * @param $cookieLifetime
     * @param $cookiePath
     * @param $cookieDomain
     * @param $cookieSecure
     * @param $cookieHttpOnly
     * @param $cookieSameSite
     */
    public function setCookieParameters($cookieLifetime, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly, $cookieSameSite) {
        session_set_cookie_params($cookieLifetime, $cookiePath . "; samesite=" . $cookieSameSite, $cookieDomain, $cookieSecure, $cookieHttpOnly);
    }


    /**
     * Wrapper to the set save handler function for improved testing
     *
     * @param mixed $saveHandler
     * @param bool $registerShutdown
     * @return void
     */
    public function setSaveHandler($saveHandler, $registerShutdown ){
        session_set_save_handler($saveHandler, $registerShutdown);
    }

}
