<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 20.02.2019
 * Time: 23:27
 */

namespace Biblio\http;

use Biblio\util\BaseEnum;

abstract class Network
{
    private static $statusMessages = array(
        NetworkStatusCode::Continue_ => 'Continue',
        NetworkStatusCode::SwitchProtocols => 'Switching Protocols',
        NetworkStatusCode::Ok => 'OK',
        NetworkStatusCode::Created => 'Created',
        NetworkStatusCode::Accepted => 'Accepted',
        NetworkStatusCode::NonAuthoritativeInformation => 'Non-Authoritative Information',
        NetworkStatusCode::ResourceNotFound => 'Resource Not Found',
        NetworkStatusCode::ResetContent => 'Reset Content',
        NetworkStatusCode::PartialContent => 'Partial Content',
        NetworkStatusCode::MultipleChoices => 'Multiple Choices',
        NetworkStatusCode::MovedPermanently => 'Moved Permanently',
        NetworkStatusCode::Found => 'Found',
        NetworkStatusCode::SeeOther => 'See Other',
        NetworkStatusCode::NotModified => 'Not Modified',
        NetworkStatusCode::UseProxy => 'Use Proxy',
        NetworkStatusCode::Unused => '(Unused)',
        NetworkStatusCode::TemporaryRedirect => 'Temporary Redirect',
        NetworkStatusCode::BadRequest => 'Bad Request',
        NetworkStatusCode::Unauthorized => 'Unauthorized',
        NetworkStatusCode::PaymentRequired => 'Payment Required',
        NetworkStatusCode::Forbidden => 'Forbidden',
        NetworkStatusCode::NotFound => 'Not Found',
        NetworkStatusCode::MethodNotAllowed => 'Method Not Allowed',
        NetworkStatusCode::NotAcceptable => 'Not Acceptable',
        NetworkStatusCode::ProxyAuthRequired => 'Proxy Auth Required',
        NetworkStatusCode::RequestTimeout => 'Request Timeout',
        NetworkStatusCode::Conflict => 'Conflict',
        NetworkStatusCode::Gone => 'Gone',
        NetworkStatusCode::LengthRequired => 'Length Required',
        NetworkStatusCode::PreconditionFailed => 'Precondition Failed',
        NetworkStatusCode::RequestEntityTooLarge => 'Request Entity Too Large',
        NetworkStatusCode::RequestUriTooLong => 'Request-URI Too Long',
        NetworkStatusCode::UnsupportedMediaType => 'Unsupported Media Type',
        NetworkStatusCode::RequestedRangeNotSatisfiable => 'Requested Range Not Satisfiable',
        NetworkStatusCode::ExpectationFailed => 'Expectation Failed',
        NetworkStatusCode::InternalServerError => 'Internal Server Error',
        NetworkStatusCode::NotImplemented => 'Not Implemented',
        NetworkStatusCode::BadGateway => 'Bad Gateway',
        NetworkStatusCode::ServiceUnavailable => 'Service Unavailable',
        NetworkStatusCode::GatewayTimeout => 'Gateway Timeout',
        NetworkStatusCode::HttpVersionNotSupported => 'HTTP Version Not Supported',
        NetworkStatusCode::DatabaseConnectionError => 'Database Connection Error'
    );

    public static function getStatusMessage($networkStatusCode)
    {
        return self::$statusMessages[$networkStatusCode];
    }

    public static function addStatusMessage($networkStatusCode, $message)
    {
        self::$statusMessages[$networkStatusCode] = $message;
    }
}

abstract class RequestMethod extends BaseEnum
{
    const Get = "GET";
    const Post = "POST";
    const Put = "PUT";
    const Delete = "DELETE";
    const Options = "OPTIONS";
}

abstract class ContentType
{
    const Json = "application/json";
    const Xml = "application/xml";
    const PlainText = "text/plain";
    const Html = "text/html";
    const FormUrlEncoded = "application/x-www-form-urlencoded";
}

abstract class NetworkStatusCode
{
    const Continue_ = 100;
    const SwitchProtocols = 101;
    const Ok = 200;
    const Created = 201;
    const Accepted = 202;
    const NonAuthoritativeInformation = 203;
    const ResourceNotFound = 204;
    const ResetContent = 205;
    const PartialContent = 206;
    const MultipleChoices = 300;
    const MovedPermanently = 301;
    const Found = 302;
    const SeeOther = 303;
    const NotModified = 304;
    const UseProxy = 305;
    const Unused = 306;
    const TemporaryRedirect = 307;
    const BadRequest = 400;
    const Unauthorized = 401;
    const PaymentRequired = 402;
    const Forbidden = 403;
    const NotFound = 404;
    const MethodNotAllowed = 405;
    const NotAcceptable = 406;
    const ProxyAuthRequired = 407;
    const RequestTimeout = 408;
    const Conflict = 409;
    const Gone = 410;
    const LengthRequired = 411;
    const PreconditionFailed = 412;
    const RequestEntityTooLarge = 413;
    const RequestUriTooLong = 414;
    const UnsupportedMediaType = 415;
    const RequestedRangeNotSatisfiable = 416;
    const ExpectationFailed = 417;
    const InternalServerError = 500;
    const NotImplemented = 501;
    const BadGateway = 502;
    const ServiceUnavailable = 503;
    const GatewayTimeout = 504;
    const HttpVersionNotSupported = 505;
    const DatabaseConnectionError = 506;
}
