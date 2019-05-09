<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 16.02.2019
 * Time: 01:25
 */

namespace Biblio\http;

use Biblio\util\Language;

require_once dirname(__FILE__) . '/../_config.php';
require_once ROOT_PATH . '/util/Types.php';
require_once ROOT_PATH . '/util/Methods.php';
require_once ROOT_PATH . '/http/Request.php';
require_once ROOT_PATH . '/model/Entity.php';
require_once ROOT_PATH . '/util/Language.php';

interface ISession
{
    public static function getInstance();
}

abstract class Session implements ISession
{

    /** @var array */
    private $word;
    private $languagesPath;
    private $currentLanguage;
    private $requestHandler;
    private $responseBuilder;
    private $languageOptions = [];

    protected $defaultLanguage = Language::english;

    /**
     * @var Session
     */
    protected static $instance;

    /**
     * Session constructor.
     *
     * @param                 $languagesPath | "some/directory/"
     * @param RequestHandler  $requestHandler
     * @param ResponseBuilder $responseBuilder
     */
    protected final function __construct($languagesPath, RequestHandler $requestHandler, ResponseBuilder $responseBuilder)
    {
        $this->languagesPath = $languagesPath;
        $this->requestHandler = $requestHandler;
        $this->responseBuilder = $responseBuilder;

//        echo getcwd() . '<br/>';
//        echo dirname(__FILE__) . '<br/>';
//        echo dirname(dirname(__FILE__)) . '<br/>';
//        echo $biblioPath . '<br/>';
    }

    // dışarıdan kopyalanmasını engelledik
    private function __clone()
    {
    }

    // unserialize() metodu ile tekrardan oluşturulmasını engelledik
    private function __wakeup()
    {
    }

    public function build()
    {
        $this->setLanguage($this->languagesPath);
        $this->requestHandler->run();
    }

    public function sendResponse($data, $code, array $dbEnvoys = [])
    {
        $this->responseBuilder->run($data, $code, $dbEnvoys);
    }

    private function setLanguage()
    {
        foreach (scandir($this->languagesPath) as $fileName) {
            if (strpos($fileName, '.php') !== null) {
                $languageName = explode('.', $fileName)[0];
                array_push($this->languageOptions, strtolower($languageName));
            }
        }
        if (!is_null($this->languagesPath)) {
            $lang = Request::getAcceptedLanguage();
            if (!is_null($lang)) {
                $lang = strtolower($lang);
                foreach ($this->languageOptions as $option)
                    if ($lang == $option) {
                        $this->currentLanguage = $option;
                        break;
                    }
            }
            if (empty($this->currentLanguage))
                $this->currentLanguage = Language::english;
            include_once $this->languagesPath . '/' . $this->currentLanguage . '.php';
            if (isset($word))
                $this->word = $word;
        }
    }

    public static function getWord($key)
    {
//        return !is_null(self::$instance) && isset(self::$instance->word[$key]) ? self::$instance->word[$key] : $key;
        return isset(self::getInstance()->word[$key]) ? self::getInstance()->word[$key] : $key;
    }
}