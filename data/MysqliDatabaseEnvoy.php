<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 13.02.2019
 * Time: 00:39
 */

namespace BiblioRest\data;

abstract class MysqliDatabaseEnvoy
{
    private $dbLocation;
    private $dbPort;
    private $dbName;
    private $dbUser;
    private $dbPassword;
    private $dbConnection;

    public function __construct($dbLocation, $dbName, $dbUser, $dbPassword, $dbPort = null)
    {
        $this->dbLocation = $dbLocation;
        $this->dbName = $dbName;
        $this->dbUser = $dbUser;
        $this->dbPassword = $dbPassword;
        $this->dbPort = $dbPort;
    }

    private static function createMySqliConnection($url, $user, $password, $dbName, $port = null)
    {
        $connection = mysqli_init();
        if ($connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, self::$dbConnectionTimeout))
            self::buildMySqliConnection($connection, $url, $user, $password, $dbName, $port);
        else
            echo $connection->error . "<br/>";
        return $connection;
    }

    private static function buildMySqliConnection(mysqli $connection, $url, $user, $password, $dbName, $port = null)
    {
        if ($connection->real_connect($url, $user, $password, $dbName, $port) && $connection->set_charset("utf8")) {
            array_push(Session::$activeDbConnections, $connection);
            return true;
        } else {
            echo $connection->error . "<br/>";
            return false;
        }
    }

    private function getDbConnection()
    {
        if (is_null($this->dbConnection)) {
            $this->dbConnection = self::createMySqliConnection($this->dbLocation, $this->dbUser, $this->dbPassword, $this->dbName);
            if (is_null($this->dbConnection)) {
                echo "DB CONNECTION ERROR" . $this->dbConnection->connect_error;
                header("HTTP/1.1 506 Database Connection Error");
                exit;
            }
        }
        return $this->dbConnection;
    }

    // for INSERT, UPDATE and DELETE
    private function runSql($sql)
    {
        return mysqli_query($this->getDbConnection(), $sql) === true;
    }

    public function startTransaction()
    {
        $this->getDbConnection()->autocommit(false);
    }

    public function commitTransaction()
    {
        $this->getDbConnection()->autocommit(true);
    }

    public function rollbackTransaction()
    {
        $this->getDbConnection()->rollBack();
    }

    public function hasNotError()
    {
        return empty($this->getDbConnection()->error);
    }

    public function hasError()
    {
        return !self::hasNotError();
    }

    public function getErrorMessage()
    {
        return empty($this->getDbConnection()->error) ? null : $this->getDbConnection()->error;
    }

    public function getLastInsertId()
    {
        return mysqli_insert_id($this->getDbConnection());
    }
}