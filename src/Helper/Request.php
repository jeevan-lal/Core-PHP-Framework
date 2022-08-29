<?php namespace Ctechhindi\CorePhpFramework\Helper;

class Request
{

    /**
     * Fetch Request POST Data
     * _______________________________
     * getPost("parameter", boolean)
     */
    public function getPost($parameter, $clean = true)
    {
        if (empty($_POST)) { return false; }
        if (empty($parameter)) { return false; }
        if (!isset($_POST[$parameter])) { return false; }

        if ($clean === true) {
            if (!is_array($_POST[$parameter])) {
                return htmlspecialchars($_POST[$parameter]);
            }
            return $_POST[$parameter];
        }

        return $_POST[$parameter];
    }

    /**
     * Set POST Data
     * _______________________________
     * setPost("parameter", "value")
     */
    public function setPost($parameter, $value, $clean = true)
    {
        if ($clean === true) {
            $value = htmlspecialchars($value);
        }

        $_POST[$parameter] = $value;

        return true;
    }

    /**
     * Fetch Request GET Data
     * _______________________________
     * getGet("parameter", boolean)
     */
    public function getGet($parameter, $clean = true)
    {
        if (empty($_GET)) { return false; }
        if (empty($parameter)) { return false; }
        if (!isset($_GET[$parameter])) { return false; }

        if ($clean === true) {
            return htmlspecialchars($_GET[$parameter]);
        }

        return $_GET[$parameter];
    }
}