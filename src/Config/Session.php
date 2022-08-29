<?php namespace Ctechhindi\CorePhpFramework\Config;

class Session
{
    public function __construct() {

        if (!isset($_SESSION)) { 
            // Starting session
            session_start();
        }
    }

    /**
     * Set Data in the Session
     */
    public function set(array $session_data) {
        $_SESSION = array_merge($_SESSION, $session_data);
    }


    /**
     * Get Data in the Session
     */
    public function get(string $session_name) {
        return $_SESSION[$session_name] ?? [];
    }


    /**
     * Get All Session Data
     */
    public function getAll() {
        return $_SESSION;
    }


    /**
     * Check Session Name in the Session
     */
    public function has(string $session_name) {
        if (isset($_SESSION[$session_name])) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Remove Session Data in the Session
     * 
     * @var $session_data [string|array]
     */
    public function remove($session_data) {

        if (!empty($session_data) && is_array($session_data) && !empty($_SESSION)) {
            foreach ($session_data as $key => $value) {
                unset($_SESSION[$value]);
            }
        }

        if (!empty($session_data) && is_string($session_data) && !empty($_SESSION)) {
            unset($_SESSION[$session_data]);
        }
    }


    ####### Flashdata

    /**
     * Set Flash Session Data
     * 
     * @var $session_key [string]
     * @var $session_value [string]
     */
    public function setFlash(string $session_key, string $session_value) {
        $_SESSION[$session_key] = $session_value;
    }


    /**
     * Get Flash Session Data
     * 
     * @var $session_key [string]
     */
    public function getFlash(string $session_key) {

        if (isset($_SESSION[$session_key]))
        {
            $flashData = $_SESSION[$session_key];

            // Clear Flash Message
            unset($_SESSION[$session_key]);

            return $flashData;
        }
    }


    /**
     * Destroy Session
     */
    public function destroy() {

        // Destroying session
        session_destroy();
    }
}