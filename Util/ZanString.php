<?php

namespace Zan\CoreBundle\Util;

class ZanString
{
    /**
     * Returns true if $haystack starts with $needle, false otherwise
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith($haystack, $needle)
    {
        return (strpos($haystack, $needle) === 0);
    }

    /**
     * Returns true if $haystack starts with $needle, false otherwise.
     * This function is case-insenstiive
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWithi($haystack, $needle)
    {
        return (stripos($haystack, $needle) === 0);
    }

    /**
     * Returns true if $haystack ends with $needle, false otherwise
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith($haystack, $needle)
    {
        $strpos = strrpos($haystack, $needle);
        if ($strpos === false) return false;

        return (strrpos($haystack, $needle) === strlen($haystack) - strlen($needle));
    }

    /**
     * Case-insensitive version of endsWith()
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWithi($haystack, $needle)
    {
        return self::endsWith(strtolower($haystack), strtolower($needle));
    }

    /**
     * Returns $string with $prefix removed.
     *
     * @param $string
     * @param $prefix
     * @return string
     */
    public static function removePrefix($string, $prefix)
    {
        if (self::startsWith($string, $prefix)) {
            $string = substr($string, strlen($prefix));
        }

        return $string;
    }

    /**
     * Returns $string with $postfix removed
     *
     * @param string $string
     * @param string $postfix
     * @return string
     */
    public static function removePostfix($string, $postfix)
    {
        if (self::endsWith($string, $postfix)) {
            $string = substr($string, 0, 0 - strlen($postfix));
        }

        return $string;
    }

    /**
     * Truncates the given string so that it is \c $numChars long. If the string
     * is truncated, the last three characters are replaced with "..."
     *
     * @param string $string
     * @param int    $numChars
     * @param string $truncatePostfix String to use when truncating
     *
     * @return string
     */
    public static function truncate($string, $numChars = 40, $truncatePostfix = "...")
    {
        if (strlen($string) > $numChars) {
            if (function_exists("mb_substr") && function_exists("mb_strlen")) {
                $string = mb_substr($string, 0, $numChars - mb_strlen($truncatePostfix)) . $truncatePostfix;
            } else {
                $string = substr($string, 0, $numChars - strlen($truncatePostfix)) . $truncatePostfix;
            }
        }

        return $string;
    }

    /**
     * Converts a camel-cased string to a lowercased and dashed string.
     *
     * Examples:
     *      - MonthlyCharges => monthly-charges
     *      - testString => test-string
     *      - TESTstring => test-string
     *
     * @param $string
     * @return string
     */
    public static function camelCaseToLcDashes($string)
    {
        /*
         * Insert a dash before any capital letter followed by a lowercase letter
         *  - but not at the beginning of the string
         */
        $replaced = preg_replace('/(?<!^)([A-Z])(?=[a-z])/', '-$1', $string);

        // Special case: block of capitals at the end (exampleSTRING -> example-STRING)
        $replaced = preg_replace('/^([a-z]+)([A-Z]+)$/', '$1-$2', $replaced);

        // Convert everything to lowercase
        $replaced = strtolower($replaced);

        return $replaced;
    }
}
