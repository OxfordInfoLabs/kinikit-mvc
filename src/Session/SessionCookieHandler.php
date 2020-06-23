<?php


namespace Kinikit\MVC\Session;

/**
 * Class SessionCookieHandler
 *
 * @noProxy
 */
class SessionCookieHandler {

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

}
