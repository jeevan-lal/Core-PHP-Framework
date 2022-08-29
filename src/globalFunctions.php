<?php

use CTH\Config\App;
use Ctechhindi\CorePhpFramework\Config\Session;

// SYSTEM
define('SPATH', __DIR__.DIRECTORY_SEPARATOR);

/**
 * Base URL
 */
if (!function_exists('baseURL'))
{
    function baseURL($url = "")
    {
        // Application Config
        $configApp = new App();

        if (empty($url)) {
            return $configApp->get("baseURL");
        }
        return $configApp->get("baseURL"). $url;
    }
}

/**
 * Redirect to Another Page
 */
if (!function_exists('redirect'))
{
    function redirect($url = "", $isStatic = false)
    {
        // Application Config
        $configApp = new App();

        if ($isStatic) {
            header("Location: ". $url);
        } else {
            header("Location: ". $configApp->get("baseURL"). $url);
        }
        die();
    }
}

/**
 * Set Timezone
 */
if (!function_exists('set_timezone'))
{
    function set_timezone()
    {
        // Application Config
        $configApp = new App();

        date_default_timezone_set($configApp->get("appTimezone") ?? 'UTC');
    }
}

/**
 * Session
 */
if (!function_exists('session'))
{
    function session()
    {
        $session = new Session();

        return $session;
    }
}

/**
 * Load Helper File
 */
if (!function_exists('helper'))
{
    function helper($name)
    {
        // Helpers
        $helpers = [
            "form" => "Form.php",
            "encryption" => "Encryption.php",
            "request" => "Request.php",
            "datetime" => "DateTime.php",
        ];
        
        if (is_string($name)) {
            if (isset($helpers[$name])) {
                require_once SPATH ."Helpers/". $helpers[$name];
            }
        }

        if (is_array($name)) {
            foreach ($name as $value) {
                if (isset($helpers[$value])) {
                    require_once SPATH ."Helpers/". $helpers[$value];
                }
            }
        }
    }
}