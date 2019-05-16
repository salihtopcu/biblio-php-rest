<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 13.02.2019
 * Time: 00:39
 */

namespace Biblio\data;

use Biblio\model\Collection;

require_once "IDatabaseEnvoy.php";

class MysqliDatabaseEnvoy implements IDatabaseEnvoy
{
    /**
     * @var DBConnectionInfo
     */
    private $dbConnectionInfo;

    /**
     * @var string
     */
    private $charset;
    
    /**
     * @var \mysqli
     */
    private static $dbConnection;

    /**
     * MysqliSqlDatabaseEnvoy constructor.
     *
     * @param DBConnectionInfo $dbConnectionInfo
     * @param string           $charset
     */
    public final function __construct(DBConnectionInfo $dbConnectionInfo, $charset = "utf8")
    {
        $this->dbConnectionInfo = $dbConnectionInfo;
        $this->charset = $charset;
    }

    public function runSelectSql($sql)
    {
        return mysqli_query($this->getDbConnection(), $sql);
    }

    public function runInsertSql($sql)
    {
        return $this->runSql($sql);
    }

    public function runUpdateSql($sql)
    {
        return $this->runSql($sql);
    }

    public function runDeleteSql($sql)
    {
        return $this->runSql($sql);
    }

    protected function getDbConnection()
    {
//        if (is_null(MysqliDatabaseEnvoy::$dbConnection) && \StringMethod::areNotEqual(get_class(MysqliDatabaseEnvoy::$dbConnection), 'mysqli')) {
        if (is_null(MysqliDatabaseEnvoy::$dbConnection) || !MysqliDatabaseEnvoy::$dbConnection->get_connection_stats()) {
            MysqliDatabaseEnvoy::$dbConnection = \MysqliMethod::createMySqliConnection($this->dbConnectionInfo->getDbLocation(), $this->dbConnectionInfo->getDbUser(), $this->dbConnectionInfo->getDbPassword(), $this->dbConnectionInfo->getDbName(), $this->dbConnectionInfo->getDbPort(), $this->dbConnectionInfo->getTimeout());
            if (is_null(MysqliDatabaseEnvoy::$dbConnection)) {
                echo "DB CONNECTION ERROR" . MysqliDatabaseEnvoy::$dbConnection->connect_error;
                header("HTTP/1.1 506 Database Connection Error");
                exit;
            }
        }
        return MysqliDatabaseEnvoy::$dbConnection;
    }

    // for INSERT, UPDATE and DELETE
    private function runSql($sql)
    {
//        echo "<br/>$sql<br/>";
        return mysqli_query($this->getDbConnection(), $sql) === true;
    }

    public function getCollection($sql, $modelClass)
    {
//        echo "<br/>$sql<br/>";
        $dbResult = mysqli_query($this->getDbConnection(), $sql);
        $collection = null;
        if (is_object($dbResult))
            $collection = new Collection();
        while ($row = mysqli_fetch_array($dbResult, MYSQLI_ASSOC))
            $collection->append($modelClass::constructFromData($row));
        return $collection;
    }
    
    public function getEntity($sql, $modelClass)
    {
//        echo "<br/>$sql<br/>";
        $dbResult = mysqli_query($this->getDbConnection(), $sql);
        $myResult = null;
        if (is_object($dbResult) && $row = mysqli_fetch_array($dbResult, MYSQLI_ASSOC))
            return $modelClass::constructFromData($row);
        else
            return null;
    }

    public function beginTransaction()
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
        return $this->hasError() ? $this->getDbConnection()->error : null;
    }

    public function getLastInsertId()
    {
        return mysqli_insert_id($this->getDbConnection());
    }

    public function disconnect()
    {
        if (!is_null(self::$dbConnection)) {
            self::$dbConnection->rollback();
            self::$dbConnection->close();
        }
    }

}
