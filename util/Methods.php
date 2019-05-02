<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 20.02.2019
 * Time: 22:04
 */

abstract class ArrayMethod
{
    public static function getArray(array $array, $key, $defaultValue = null)
    {
        return (array_key_exists($key, $array) && is_array((array) $array[$key])) ? (array) $array[$key] : $defaultValue;
    }

    public static function getDate($array, $key, $dateFormat, $defaultValue = null)
    {
        $dateString = ArrayMethod::getValue($array, $key, '');
        $date = date_create_from_format($dateFormat, $dateString);
        if ($date !== false)
            return $date;
        else
            return $defaultValue;
    }

    public static function getValue($array, $key, $defaultValue = null)
    {
        return array_key_exists($key, $array) ? $array [$key] : $defaultValue;
    }

    public static function revert(array $array)
    {
        $result = array();
        if (self::isAssociative($array)) {
            foreach ($array as $key => $value)
                $result[$value] = $key;
        }
        return $result;
    }

    /**
     * @param array $array
     *
     * @return bool
     */
    public static function isAssociative(array $array)
    {
        if (array() === $array)
            return false;
        return array_keys($array) !== range(0, count($array) - 1);
    }

    public static function mergeArrayAsString($array, $separator = ",", $preChar = null, $endChar = null)
    {
        $result = "";
        for ($i = 0; $i < count($array); $i++) {
            if (!is_null($preChar))
                $result .= $preChar;
            $result .= $array[$i];
            if (!is_null($endChar))
                $result .= $endChar;
            if ($i < count($array) - 1)
                $result .= $separator;
        }
        return $result;
    }
}

abstract class DateMethod
{
    public static function create($dateString)
    {
        if (strlen($dateString) == 10)
            $dateString .= " 00:00:00";
        $format = self::generateFormat($dateString);
//        return empty($format) ? null : date_create_from_format($format, $dateString);
        if (empty($format))
            return null;
        else {
            $date = date_create_from_format($format, $dateString);
            return $date === false ? null : $date;
        }
    }

    public static function format(DateTime $date, $format)
    {
        return date_format($date, $format);
    }

    public static function generateFormat($dateString)
    {
        $l = strlen($dateString);
        if ($l == 10 || $l == 19 || $l == 23) {
            $ds = $l >= 10 ? $dateString[4] : null; // date separator
            $ts = $l >= 19 ? $dateString[13] : null; // time separator
            $ts2 = $l == 23 ? $dateString[19] : null; // time separator 2
            $format = "";
            if ($l >= 10)
                $format = "Y" . $ds . "m" . $ds . "d";
            if ($l >= 19)
                $format = $format . " " . "H" . $ts . "i" . $ts . "s";
            if ($l == 23)
                $format = $format . $ts2 . "P";
            return $format;
        } else
            return null;
    }

    public static function getDayValue(DateTime $date)
    {
        return (int) date_parse_from_format(DATE_ISO8601, date_format($date, DATE_ISO8601))["day"];
    }

    public static function getMonthValue(DateTime $date)
    {
        return (int) date_parse_from_format(DATE_ISO8601, date_format($date, DATE_ISO8601))["month"];
    }

    public static function getYearValue(DateTime $date)
    {
        return (int) date_parse_from_format(DATE_ISO8601, date_format($date, DATE_ISO8601))["year"];
    }

    public function isValidDateTimeString($date)
    {
        $timeZone = date_default_timezone_get();
        $date = DateTime::createFromFormat("Y-m-d", substr($date, 0, 10), new DateTimeZone($timeZone));
        return $date && DateTime::getLastErrors()["warning_count"] == 0 && DateTime::getLastErrors()["error_count"] == 0;
    }
}

abstract class Encryption
{
    public static function decrypt_mcrypt_base64($text, $key)
    {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($text), MCRYPT_MODE_ECB));
    }

    public static function encrypt_base64_mcrypt($text, $key)
    {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $text, MCRYPT_MODE_ECB));
    }

    public static function encrypt_md5($text)
    {
        return md5($text);
    }
}

abstract class MysqliMethod {

    public static function createMySqliConnection($url, $user, $password, $dbName, $port = "3306", $dbConnectionTimeout = 10, $charset = "utf8")
    {
        $connection = mysqli_init();
        if ($connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $dbConnectionTimeout))
            self::buildMySqliConnection($connection, $url, $user, $password, $dbName, $port, $charset);
        else
            echo $connection->error . "<br/>";
        return $connection;
    }

    private static function buildMySqliConnection(mysqli $connection, $url, $user, $password, $dbName, $port = "3306", $charset = "utf8")
    {
        if ($connection->real_connect($url, $user, $password, $dbName, $port) && $connection->set_charset($charset)) {
            return true;
        } else {
            echo $connection->error . "<br/>";
            return false;
        }
    }
}

abstract class StringMethod
{
    public static function areEqual($text1, $text2)
    {
        return strcmp($text1, $text2) == 0;
    }

    public static function areNotEqual($text1, $text2)
    {
        return !self::areEqual($text1, $text2);
    }

    public static function clearRtf($text)
    {
        return preg_replace("#(\{.*?\})|\}|(\\\\\S+)#", "", $text);
    }

    public static function clearSpaces($text)
    {
        return is_null($text) ? '' : str_replace(' ', '', $text);
    }

    public static function contains($text, $search)
    {
        return substr_count($text, $search) > 0;
    }

    public static function isEmail($text)
    {
        return is_null($text) ? false : filter_var($text, FILTER_VALIDATE_EMAIL);
    }

    public static function isEmpty($text)
    {
        return is_null($text) || empty($text);
    }

    public static function isNotEmail($text)
    {
        return !StringMethod::isEmail($text);
    }

    public static function isNotEmpty($text)
    {
        return !self::isEmpty($text);
    }

    public static function removeMultiSpaces($text)
    {
        return preg_replace('!\s+!', ' ', $text);
    }

    public static function replace($text, $search, $replace)
    {
        return str_replace($search, $replace, $text);
    }
}

abstract class UUID
{
    public static function v4()
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));
    }
}
