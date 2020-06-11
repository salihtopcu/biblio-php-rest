<?php

namespace Biblio\util;

abstract class StopWatch
{
    private static $startTimes = array();

    /**
     * Start the timer
     *
     * @param $timerName string The name of the timer
     * @return void
     */
    public static function start($timerName = 'default')
    {
        self::$startTimes[$timerName] = microtime(true);
    }

    /**
     * Get the elapsed time in seconds
     *
     * @param $timerName string The name of the timer to start
     * @return float The elapsed time since start() was called
     */
    public static function getSeconds($timerName = 'default')
    {
        return microtime(true) - self::$startTimes[$timerName];
    }
}
