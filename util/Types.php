<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 20.02.2019
 * Time: 23:42
 */

namespace BiblioRest\util;

abstract class BaseEnum
{
    private static $constCacheArray = null;

    private static function getConstants()
    {
        if (is_null(self::$constCacheArray))
            self::$constCacheArray = [];
        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }
        return self::$constCacheArray[$calledClass];
    }

    public static function isValidName($name, $strict = false)
    {
        $constants = self::getConstants();

        if ($strict)
            return array_key_exists($name, $constants);

        $keys = array_map('strtolower', array_keys($constants));
        return in_array(strtolower($name), $keys);
    }

    public static function isValidValue($value, $strict = true)
    {
        $values = array_values(self::getConstants());
        return in_array($value, $values, $strict);
    }
}
