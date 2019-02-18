<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 12.02.2019
 * Time: 23:44
 */

namespace BiblioRest\http;

require_once 'Request.php';

abstract class RequestHandler
{
    private $apiFolderPath;

    public function __construct($apiFolderPath)
    {
        $this->apiFolderPath = $apiFolderPath;
    }

    public function run() {
        // Request formatlari
// {dosya_adı}/{fonksiyon_adı}
// {dosya_adı}/{fonksiyon_adı}/{id}

        require_once 'Request.php';

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");

        if (Request::getMethod() == RequestMethod::Options) {
            header('Cross-Origin-Resource-Sharing: true');
            header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE');
            header('Access-Control-Allow-Headers: access-token,content-type');
            header("HTTP/1.1 200");
            exit;
        }

        $success = true;
        $commandArray = explode('/', strtolower(trim($_REQUEST ['rquest'])));
        if (count($commandArray) > 0) {
            if (count($commandArray) > 1) {
                $apiClassName = ucfirst($commandArray [0]);
                $phpFileName = $apiClassName . ".php";
                $func = $commandArray [1];
                $param = null;
                if (count($commandArray) > 2)
                    $param = $commandArray[2];

                $filePath = $this->apiFolderPath . $phpFileName;
                if (file_exists($filePath)) {
                    require_once("$filePath");
                    $api = new $apiClassName ();
                    if (method_exists($api, $func)) {
                        if (is_null($param))
                            $api->$func ();
                        else
                            $api->$func ($param);
                    } else
                        $success = false;
                } else
                    $success = false;
            } else if (count($commandArray) == 1) {
                $command = $commandArray [0];
                if (strtolower($command) == "updatedatabase") {
                    self::checkForDatabaseUpdate();
                }
            }
        } else
            $success = false;

        if (!$success) {
            header("HTTP/1.1 404 Api Not Found");
            header("Content-Type:text/plain");
            exit;
        }
    }

    abstract protected function checkForDatabaseUpdate();
}