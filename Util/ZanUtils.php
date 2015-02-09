<?php


namespace Zan\CoreBundle\Util;


class ZanUtils 
{
    /**
     * Returns the first argument found that is not equivalent
     * to an empty string. If all arguments are equivalent to an empty
     * string, the last argument passed is returned.
     *
     * For example:
     *
     *      ZanUtils::tryChoices(false, "a value");
     *      // Will return "a value"
     *
     *      ZanUtils::tryChoices(null, false);
     *      // Will return false.
     *
     * @warning the string "0" evaluates to false
     *
     * @returns mixed first non-empty value or the last argument
     *
     */
    public static function tryChoices()
    {
        $args = func_get_args();

        foreach ($args as $arg) {
            if ($arg) return $arg;
        }

        return array_pop($args);
    }

    /**
     * Converts $data to a string by employing the following techniques:
     *
     * For primitives: casts to a string
     * For arrays: uses print_r
     * For objects:
     *  - result of __toString(), if implemented
     *  - object class name otherwise
     *
     * @param $data
     * @return int|mixed|string
     */
    public static function toString($data)
    {
        if (self::isPrimitive($data)) {
            return (string) $data;
        }
        elseif (is_array($data)) {
            return print_r($data, true);
        }
        elseif (is_object($data)) {
            // can cast directly to string if it implements __toString()
            if (method_exists($data, '__toString')) {
                return (string) $data;
            }
            // Otherwise, return class name
            else {
                return printf('[Object:%s]', get_class($data));
            }
        }

        return $data;
    }

    /**
     * if the data is a primitive value or is an array made up entirely of primitive values.
     *
     * @param $data
     * @return bool
     *
     */
    public static function isPrimitive($data)
    {
        if (is_object($data)) return false;

        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (!self::isPrimitive($value)) return false;
            }
        }

        return true;
    }
}