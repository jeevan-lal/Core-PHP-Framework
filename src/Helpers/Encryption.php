<?php

use CTH\Config\Encryption;

/**
 * Encrypt data
 */
if (!function_exists('encrypt'))
{
    function encrypt(string $string)
    {
        $key = openssl_digest(Encryption::$key, 'SHA256', TRUE);
        $iv = substr(hash('SHA256', Encryption::$key), 0, 16);
        $encryptText = openssl_encrypt($string, Encryption::$method, $key, OPENSSL_RAW_DATA, $iv);
        return bin2hex($encryptText);
    }
}

/**
 * Decrypts data
 */
if (!function_exists('decrypt'))
{
    function decrypt(string $string)
    {
        try 
        {
            if (ctype_xdigit($string) && strlen($string) % 2 == 0)
            {
                $string = hex2bin($string);
                $key = openssl_digest(Encryption::$key, 'SHA256', TRUE);
                $iv = substr(hash('SHA256', Encryption::$key), 0, 16);
                $encryptText = openssl_decrypt($string, Encryption::$method, $key, OPENSSL_RAW_DATA, $iv);
                return $encryptText;

            } else 
            {
                return false;
            }

        } catch (Exception $e) {
            return false;
        }
    }
}