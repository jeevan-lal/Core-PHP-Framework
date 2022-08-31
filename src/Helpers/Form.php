<?php

/**
 * Fetch Request POST Data
 * _______________________________
 * getPost("parameter", boolean)
 */
if (!function_exists('getPost')) {
    function getPost($parameter, $clean = true)
    {
        if (empty($_POST)) {
            return false;
        }
        if (empty($parameter)) {
            return false;
        }
        if (!isset($_POST[$parameter])) {
            return false;
        }

        if ($clean === true) {
            if (!is_array($_POST[$parameter])) {
                return htmlspecialchars($_POST[$parameter]);
            }
            return $_POST[$parameter];
        }

        return $_POST[$parameter];
    }
}

/**
 * Set POST Data
 * _______________________________
 * setPost("parameter", "value")
 */
if (!function_exists('setPost')) {
    function setPost($parameter, $value, $clean = true)
    {
        if ($clean === true) {
            $value = htmlspecialchars($value);
        }

        $_POST[$parameter] = $value;

        return true;
    }
}

/**
 * Fetch Request GET Data
 * _______________________________
 * getGet("parameter", boolean)
 */
if (!function_exists('getGet')) {
    function getGet($parameter, $clean = true)
    {
        if (empty($_GET)) {
            return false;
        }
        if (empty($parameter)) {
            return false;
        }
        if (!isset($_GET[$parameter])) {
            return false;
        }

        if ($clean === true) {
            return htmlspecialchars($_GET[$parameter]);
        }

        return $_GET[$parameter];
    }
}

/**
 * Set Value in the Form Field
 * --------------------------------------------
 * setValue("field_name")
 */
if (!function_exists('setValue')) {
    function setValue($fieldName, $fieldValue = NULL)
    {

        if (empty($fieldName)) {
            return;
        }

        if ($fieldValue !== NULL && (empty($_POST) || empty($_GET))) {
            return $fieldValue;
        }

        if (empty($_POST) && empty($_GET)) {
            return;
        }

        if (!isset($_POST[$fieldName]) && !isset($_GET[$fieldName])) {
            return;
        }

        // Check Field Data in the POST and GET Request
        $value = "";
        if (isset($_POST[$fieldName])) {
            $value = $_POST[$fieldName];
        } else if (isset($_GET[$fieldName])) {
            $value = $_GET[$fieldName];
        }

        return $value;
    }
}

/**
 * Select Field Option
 * --------------------------------------------
 * selectValue("option_value", "field_name")
 */
if (!function_exists('selectValue')) {
    function selectValue($optionValue, $fieldName, $fieldValue = NULL)
    {
        if (empty($fieldName) || !isset($optionValue)) {
            return;
        }

        if ($fieldValue !== NULL && (empty($_POST) || empty($_GET))) {
            if ($fieldValue === $optionValue) {
                return "selected";
            }
            return;
        }

        if (empty($_POST) && empty($_GET)) {
            return;
        }

        if (!isset($_POST[$fieldName]) && !isset($_GET[$fieldName])) {
            return;
        }

        // Check Field Data in the POST and GET Request
        $value = "";
        if (isset($_POST[$fieldName])) {
            $value = $_POST[$fieldName];
        } else if (isset($_GET[$fieldName])) {
            $value = $_GET[$fieldName];
        }

        if ($value === $optionValue) {
            return "selected";
        }
        return;
    }
}
