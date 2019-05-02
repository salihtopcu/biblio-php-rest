<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 13.04.2019
 * Time: 10:48
 */

namespace Biblio\data;


class DBConnectionInfo
{
    private $dbLocation, $dbName, $dbUser, $dbPassword, $dbPort, $timeout;

    /**
     * DBConnectionInfo constructor.
     *
     * @param $dbLocation string
     * @param $dbName string
     * @param $dbUser string
     * @param $dbPassword string
     * @param $dbPort string
     * @param $timeout int
     */
    public function __construct($dbLocation, $dbName, $dbUser, $dbPassword, $dbPort = "3306", $timeout = 10)
    {
        $this->dbLocation = $dbLocation;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbPort = $dbPort;
        $this->timeout = $timeout;
    }

    /**
     * @return string
     */
    public function getDbLocation()
    {
        return $this->dbLocation;
    }

    /**
     * @return string
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @return string
     */
    public function getDbUser()
    {
        return $this->dbUser;
    }

    /**
     * @return string
     */
    public function getDbPassword()
    {
        return $this->dbPassword;
    }

    /**
     * @return string
     */
    public function getDbPort()
    {
        return $this->dbPort;
    }

    /**
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }
}