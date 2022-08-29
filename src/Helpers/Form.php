<?php


/**
 * Set Value in the Form Field
 * --------------------------------------------
 * setValue("field_name")
 */
if (!function_exists('setValue'))
{
    function setValue($fieldName, $fieldValue = NULL)
    {

        if (empty($fieldName)) {
            return;
        }

        if ($fieldValue !== NULL && (empty($_POST) || empty($_GET)))
        {
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
if (!function_exists('selectValue'))
{
    function selectValue($optionValue, $fieldName, $fieldValue = NULL)
    {
        if (empty($fieldName) || !isset($optionValue)) {
            return;
        }

        if ($fieldValue !== NULL && (empty($_POST) || empty($_GET)))
        {
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