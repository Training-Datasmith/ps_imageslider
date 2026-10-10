<?php

class Tools
{
    private static $submitted = array();
    public static $lastRedirect;

    public static function resetRequest()
    {
        $_GET = array();
        $_POST = array();
        $_FILES = array();
        self::$submitted = array();
        self::$lastRedirect = null;
    }

    public static function isSubmit($key)
    {
        return isset(self::$submitted[$key]) || isset($_POST[$key]) || isset($_GET[$key]);
    }

    public static function getValue($key, $default = false)
    {
        if (isset($_POST[$key])) {
            return $_POST[$key];
        }
        if (isset($_GET[$key])) {
            return $_GET[$key];
        }

        return $default;
    }

    public static function submitKey($key)
    {
        self::$submitted[$key] = true;
        $_POST[$key] = '1';
    }

    public static function strlen($value)
    {
        return strlen((string) $value);
    }

    public static function hash($value)
    {
        return 'test-secure-key-hash';
    }

    public static function redirectAdmin($url)
    {
        self::$lastRedirect = $url;
        throw new RedirectException($url);
    }

    public static function getAdminTokenLite($tab)
    {
        return 'test-token';
    }

    public static function strtolower($value)
    {
        return strtolower($value);
    }

    public static function substr($value, $start, $length = null)
    {
        if (null === $length) {
            return substr($value, $start);
        }

        return substr($value, $start, $length);
    }

    public static function str2url($value)
    {
        return preg_replace('/[^a-z0-9-]+/', '-', strtolower($value));
    }
}
