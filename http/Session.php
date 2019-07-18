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

abstract class Session
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
        Session::$instance = $this;
    }

    // Blocked creating new instance with clone
    private function __clone()
    {
    }

    // Blocked creating new instance with unserialize() method
    private function __wakeup()
    {
    }

    public function getInstance(): Session
    {
        return Session::$instance;
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
            $this->word = isset($word) ? $word : [];
        }
    }

    public static function getWord($key)
    {
//        echo $key . ' ' . count(Session::getInstance()->word);exit;
//        echo is_null(Session::getInstance()->word) ? 'null' : 'degil';exit;
//        return !is_null(self::$instance) && isset(self::$instance->word[$key]) ? self::$instance->word[$key] : $key;
        return isset(get_called_class()::getInstance()->word[$key]) ? self::getInstance()->word[$key] : $key;
    }
}