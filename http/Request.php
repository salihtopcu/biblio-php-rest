<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 12.02.2019
 * Time: 23:46
 */

namespace BiblioRest\http;

require_once 'Network.php';

abstract class Request
{
    private static $headers = null;

    public static function getMethod()
    {
        return $_SERVER ["REQUEST_METHOD"];
    }

    public static function getValue()
    {
        return $_SERVER ['REQUEST_URI'];
    }

    public static function getHeaders()
    {
        if (is_null(Request::$headers)) {
            if (apache_get_version() !== false)
                Request::$headers = apache_request_headers();
            else {
                Request::$headers = array();
                $rx_http = '/\AHTTP_/';
                foreach ($_SERVER as $key => $val) {
                    if (preg_match($rx_http, $key)) {
                        $arh_key = preg_replace($rx_http, '', $key);
                        $rx_matches = explode('_', $arh_key);
                        if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
                            foreach ($rx_matches as $ak_key => $ak_val)
                                $rx_matches [$ak_key] = ucfirst($ak_val);
                            $arh_key = implode('-', $rx_matches);
                        }
                        Request::$headers [strtolower($arh_key)] = $val;
                    }
                }
            }
            foreach (Request::$headers as $key => $value)
                Request::$headers[strtolower($key)] = $value;
        }
        return Request::$headers;
    }

    public static function getQueryString($key)
    {
        return (is_array($_GET) && array_key_exists($key, $_GET)) ? $_GET[$key] : null;
    }

    public static function getData()
    {
        $contentType = self::getContentType();
        $data = file_get_contents('php://input');
        if ($contentType == ContentType::Json)
            return json_decode($data, true);
        else if ($contentType == ContentType::Xml)
            return xmlrpc_decode($data);
        else if ($contentType == ContentType::FormUrlEncoded) {
            $data = iconv("windows-1251", "UTF-8", $data);
            parse_str(urldecode($data), $data2);
            return $data2;
        } else
            return $data;
    }

    public static function getHeader($key)
    {
        return Request::getHeaders()[strtolower($key)];
    }

    public static function getContentType()
    {
        $typeText = self::getHeader("content-type");
        if (substr_count($typeText, ContentType::Json))
            return ContentType::Json;
        else if (substr_count($typeText, ContentType::Xml))
            return ContentType::Xml;
        else if (substr_count($typeText, ContentType::FormUrlEncoded))
            return ContentType::FormUrlEncoded;
        else if (substr_count($typeText, ContentType::Html))
            return ContentType::Html;
        else
            return ContentType::PlainText;
    }

    public static function getAcceptedContentType()
    {
        $typeText = ArrayMethod::getValue(self::getHeaders(), "ACCEPT", "");
        if (substr_count($typeText, ContentType::Json))
            return ContentType::Json;
        else if (substr_count($typeText, ContentType::Xml))
            return ContentType::Xml;
        else if (substr_count($typeText, ContentType::FormUrlEncoded))
            return ContentType::FormUrlEncoded;
        else if (substr_count($typeText, ContentType::Html))
            return ContentType::Html;
        else if (substr_count($typeText, ContentType::Html))
            return ContentType::PlainText;
        else
            return ContentType::Json;
    }

    public static function getAcceptedLanguage()
    {
        return self::getHeader("ACCEPT-LANGUAGE");
    }

    public static function getAuthorization()
    {
        return self::getHeader("AUTHORIZATION");
    }
}
