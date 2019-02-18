<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 16.02.2019
 * Time: 01:25
 */

namespace BiblioRest\http;

require_once 'Request.php';

abstract class Session
{

    /** @var array */
    private $word;
    private $languagesDirectory;
    private $currentLanguage;
    private $requestHandler;

    /**
     * Session constructor.
     * @param $languagesDirectory | "some/directory/"
     * @param RequestHandler $requestHandler
     */
    public function __construct($languagesDirectory, RequestHandler $requestHandler)
    {
        $this->languagesDirectory = $languagesDirectory;
        $this->requestHandler = $requestHandler;
    }

    final public function build()
    {
        self::setLanguage($this->languagesDirectory);
        $this->requestHandler->run();
    }

    private function setLanguage($languagesDirectory)
    {
        if (!is_null($languagesDirectory)) {
            $lang = Request::getAcceptedLanguage();
            if (!is_null($lang)) {
                $lang = strtolower($lang);
                foreach (array("en", "tr", "fr", "de") as $systemLanguage)
                    if ($lang == $systemLanguage) {
                        Session::$language = $lang;
                        break;
                    }
            }
            if (empty($this->currentLanguage))
                $this->currentLanguage = 'en';
            include_once 'language/' . $this->currentLanguage . '.php';
            if (!empty($this->word))
                $this->word = $word;
        }
    }

    public static function getWord($key)
    {
        return isset(Application::$word[$key]) ? Application::$word[$key] : $key;
    }
}