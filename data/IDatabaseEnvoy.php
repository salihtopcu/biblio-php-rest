<?php

namespace Biblio\data;


use Biblio\model\Entity;

interface IDatabaseEnvoy
{
    /**
     * IDatabaseEnvoy constructor.
     *
     * @param DBConnectionInfo $dbConnectionInfo
     */
    public function __construct(DBConnectionInfo $dbConnectionInfo, $charset = "utf8");

    /**
     * @param string $sql
     *
     * @return array
     */
    public function runSelectSql($sql);

    /**
     * @param string $sql
     *
     * @return bool
     */
    public function runInsertSql($sql);

    /**
     * @param string $sql
     *
     * @return bool
     */
    public function runUpdateSql($sql);

    /**
     * @param string $sql
     *
     * @return bool
     */
    public function runDeleteSql($sql);

    /**
     * Escapes special characters in a string for use in an SQL statement
     * 
     * @param string $string
     *
     * @return string
     */
    public function escapeString($string);

    /**
     * @param string        $sql
     * @param Entity::class $modelClass
     *
     * @return mixed
     */
    public function getEntity($sql, $modelClass);

    /**
     * @param string        $sql
     * @param Entity::class $modelClass
     *
     * @return \Collection mixed
     */
    public function getCollection($sql, $modelClass);

    /**
     * @return bool
     */
    public function beginTransaction();

    /**
     * @return bool
     */
    public function commitTransaction();

    /**
     * @return bool
     */
    public function rollbackTransaction();

    /**
     * @return bool
     */
    public function hasError();

    /**
     * @return string|null
     */
    public function getErrorMessage();

    /**
     * @return int|null
     */
    public function getLastInsertId();

    /**
     * @return void
     */
    public function disconnect();
}
