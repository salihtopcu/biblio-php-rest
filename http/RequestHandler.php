<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 12.02.2019
 * Time: 23:44
 */

namespace Biblio\http;

require_once ROOT_PATH . '/util/Methods.php';

abstract class RequestHandler
{
    private $apiFolderPath;

    public function __construct($apiFolderPath)
    {
        $this->apiFolderPath = $apiFolderPath;
    }

    public function run()
    {
// Request formatlari
// {dosya_adı}/{fonksiyon_adı}
// {dosya_adı}/{fonksiyon_adı}/{id}

        require_once 'Request.php';

        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Credentials: true");

        if (Request::getMethod() == RequestMethod::Options) {
            header('Cross-Origin-Resource-Sharing: true');
            header('Access-Control-Allow-Methods: ' . \ArrayMethod::mergeArrayAsString($this->getAllowedRequestMethods()));
            header('Access-Control-Allow-Headers: ' . \ArrayMethod::mergeArrayAsString($this->getAllowedHeaders()));
            header("HTTP/1.1 200");
            exit;
        }

        $success = true;
        $commandArray = explode('/', strtolower(trim($_REQUEST ['rquest'])));
        if (count($commandArray) > 0) {
            if (count($commandArray) > 1) {
                $moduleName = ucfirst($commandArray [0]);
                $phpFileName = $moduleName . ".php";
                $func = $commandArray [1];
                $param = null;
                if (count($commandArray) > 2)
                    $param = $commandArray[2];

                $filePath = $this->apiFolderPath . $phpFileName;
                if (file_exists($filePath)) {
                    require_once("$filePath");
                    $module = new $moduleName ();
                    if (method_exists($module, $func)) {
                        if (is_null($param))
                            $module->$func ();
                        else
                            $module->$func ($param);
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


    /**
     * Requires for CORS handshake with Web Clients
     *
     * @return array
     */
    protected function getAllowedHeaders(): array
    {
        return ["content-type", "authorization"];
    }

    /**
     * Requires for CORS handshake with Web Clients
     *
     * @return array
     */
    protected function getAllowedRequestMethods(): array
    {
        return [RequestMethod::Get, RequestMethod::Post, RequestMethod::Put, RequestMethod::Delete];
    }

    abstract protected function checkForDatabaseUpdate();

}
