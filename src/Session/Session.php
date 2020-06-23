<?php


namespace Kinikit\MVC\Session;

/**
 * @defaultImplementation Kinikit\MVC\Session\PHPSession
 *
 * Interface Session
 */
interface Session {

    /**
     * Set a session value for a string key.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setValue($key, $value);

    /**
     * Get a session value by key
     *
     * @param string $key
     * @return mixed
     */
    public function getValue($key);

    /**
     * Get all values - return as array of values keyed in by string.
     *
     * @return mixed[string]
     */
    public function getAllValues();

    /**
     * Clear the session of all values
     *
     */
    public function clearAll();


    /**
     * Reload session data - particularly useful if session implementation is caching
     *
     * @return mixed
     */
    public function reload();


    /**
     * Regenerate a session - generally called in authentication
     * scenarios to prevent session fixation
     *
     * @return mixed
     */
    public function regenerate();

}
