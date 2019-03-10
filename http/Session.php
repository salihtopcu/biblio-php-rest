<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 16.02.2019
 * Time: 01:25
 */

namespace BiblioRest\http;

require_once dirname(__FILE__) . '/../_config.php';
require_once ROOT_PATH . '/util/Types.php';
require_once ROOT_PATH . '/util/Methods.php';
require_once 'Request.php';

abstract class Session
{

    /** @var array */
    private $word;
    private $languagesPath;
    private $currentLanguage;
    private $requestHandler;
    private $responseBuilder;
    private $languageOptions = [];

    /**
     * Session constructor.
     * @param $languagesPath | "some/directory/"
     * @param RequestHandler $requestHandler
     * @param ResponseBuilder $responseBuilder
     */
    public function __construct($languagesPath, RequestHandler $requestHandler, ResponseBuilder $responseBuilder)
    {
        $this->languagesPath = $languagesPath;
        $this->requestHandler = $requestHandler;
        $this->responseBuilder = $responseBuilder;

//        echo getcwd() . '<br/>';
//        echo dirname(__FILE__) . '<br/>';
//        echo dirname(dirname(__FILE__)) . '<br/>';
//        echo $biblioPath . '<br/>';
    }

    final public function build()
    {
        self::setLanguage($this->languagesPath);
        $this->requestHandler->run();
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
                $this->currentLanguage = 'en';
            include_once 'language/' . $this->currentLanguage . '.php';
            if (isset($word))
                $this->word = $word;
        }
    }

    public static function getWord($key)
    {
        return isset(self::$word[$key]) ? self::$word[$key] : $key;
    }
}