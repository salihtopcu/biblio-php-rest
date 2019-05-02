<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 28.04.2019
 * Time: 17:39
 */

namespace Biblio\data;

use Biblio\data\IRepository;
use Biblio\data\Repository;
use Biblio\model\DBEntity;

require_once "Repository.php";

interface IDBEntityRepository extends IRepository
{
    /**
     * @return string Table name of current DBEntity
     */
    public function getTableName();

    /**
     * @return array An assovicative array that looks like array( "columnName" => "propertyName", ... )
     */
    public function getColumnNameMap();

    /**
     * @return string Class name of current DBEntity
     */
    public function getEntityClassName();
}

abstract class DBEntityRepository extends Repository implements IDBEntityRepository
{
    public $hasIdColumn = true;
    protected $convertBoolToInt = false;

    private $revertedColumnNameMap;

    protected function revertColumnNameMap()
    {
        if (is_null($this->revertedColumnNameMap))
            $this->revertedColumnNameMap = \ArrayMethod::revert($this->getColumnNameMap());
        return $this->revertedColumnNameMap;
    }

    protected function getEntity($sql)
    {
        return $this->dbEnvoy->getEntity($sql, $this->getEntityClassName());
    }

    protected function getCollection($sql)
    {
        return $this->dbEnvoy->getCollection($sql, $this->getEntityClassName());
    }

    protected function generateSelectPhrase()
    {
        $selectPhrase = "SELECT ";
        $columnNames = "";
        if ($this->hasIdColumn)
            $columnNames .= $this->getTableName() . ".id";
        foreach ($this->getColumnNameMap() as $columnName => $propertyName) {
            if ($propertyName != "id") {
                $columnNames .= empty($columnNames) ? "" : ", ";
                $columnNames .= $this->getTableName() . ".$columnName AS $propertyName";
            }
        }
        $selectPhrase .= $columnNames . " FROM " . $this->getTableName() . " ";
        return $selectPhrase;
    }

    /**
     * @param int $id
     *
     * @return DBEntity
     */
    public function find($id)
    {
        if ($this->hasIdColumn) {
            $sql = $this->generateSelectPhrase() . " WHERE id = $id";
            return $this->getEntity($sql);
        } else
            return null;
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool
     */
    public function insert(DBEntity $instance)
    {
        if ($this->getEntityClassName() == get_class($instance)) {
            $sqlColumns = "";
            $sqlValues = "";
            foreach ($this->getColumnNameMap() as $columnName => $propertyName) {
                if ($propertyName != "id") {
                    $value = $instance->$propertyName;
                    $isDate = is_object($value) && get_class($value) == "DateTime";
                    if (is_null($value) || is_string($value) || is_int($value) || is_double($value) || is_bool($value) || $isDate) {
                        $sqlColumns .= empty($sqlColumns) ? "" : ", ";
                        $sqlColumns .= $columnName;

                        $sqlValues .= empty($sqlValues) ? "" : ", ";
                        $sqlValues .= is_string($value) || $isDate ? "'" : "";
                        if ($isDate)
                            $sqlValues .= date_format($value, DATE_TIME_FORMAT);
                        else if (is_null($value))
                            $sqlValues .= 'NULL';
                        else if (is_bool($value) && $this->convertBoolToInt)
                            $sqlValues .= (int) $value;
                        else
                            $sqlValues .= $value;
                        $sqlValues .= is_string($value) || $isDate ? "'" : "";
                    }
                }
            }
            $sql = "INSERT INTO " . $this->getTableName() . " ($sqlColumns) VALUES ($sqlValues)";
            return $this->dbEnvoy->runInsertSql($sql);
        } else {
            echo "instance must be " . $this->getEntityClassName();
            return false;
        }
    }

    public function update(DBEntity $instance)
    {
        if ($this->hasIdColumn && $this->getEntityClassName() == get_class($instance)) {
            $setPhrase = "";
            foreach ($this->getColumnNameMap() as $columnName => $propertyName) {
                if ($propertyName != "id") {
                    $value = $instance->$propertyName;
                    $isDate = is_object($value) && get_class($value) == "DateTime";
                    if (is_null($value) || is_string($value) || is_int($value) || is_double($value) || is_bool($value) || $isDate) {
                        $setPhrase .= empty($setPhrase) ? "" : ", ";
                        $setPhrase .= "$columnName = ";
                        $setPhrase .= is_string($value) || $isDate ? "'" : "";
                        if ($isDate)
                            $setPhrase .= date_format($value, DATE_TIME_FORMAT);
                        else if (is_null($value))
                            $setPhrase .= 'NULL';
                        else if (is_bool($value) && $this->convertBoolToInt)
                            $setPhrase .= (int) $value;
                        else
                            $setPhrase .= $value;
                        $setPhrase .= is_string($value) || $isDate ? "'" : "";
                    }

                }
            }
            $sql = "UPDATE " . $this->getTableName() . " SET $setPhrase WHERE id = $instance->id";
            return $this->dbEnvoy->runUpdateSql($sql);
        } else {
            echo "instance must be " . $this->getEntityClassName() . " and must have id";
            return false;
        }
    }

    public function delete(DBEntity $instance)
    {
        if ($this->hasIdColumn && $this->getEntityClassName() == get_class($instance)) {
            $sql = "DELETE FROM " . $this->getTableName() . " WHERE id = $instance->id";
            return $this->dbEnvoy->runDeleteSql($sql);
        } else {
            echo "instance must be " . $this->getEntityClassName() . " and must have id";
            return false;
        }
    }

    public function filter(array $conditions = null, array $orderBy = null)
    {
        $sql = $this->generateSelectPhrase();
        if (!is_null($conditions) && count($conditions) > 0 && \ArrayMethod::isAssociative($conditions)) {
            foreach ($conditions as $property => $value) {
                if (!is_null($value)) {
                    $isDate = is_object($value) && get_class($value) == "DateTime";
                    $sql .= \StringMethod::contains($sql, "WHERE") ? " AND " : " WHERE ";
                    $sql .= $this->revertColumnNameMap()[$property] . " = ";
                    $sql .= is_string($value) || $isDate ? "'" : "";
                    if ($this->convertBoolToInt && is_bool($value))
                        $sql .= (int) $value;
                    else
                        $sql .= $value;
                    $sql .= is_string($value) || $isDate ? "'" : "";
                }
            }
        }
        if (!is_null($orderBy) && count($orderBy) > 0) {
            foreach ($orderBy as $property) {
                $sql .= \StringMethod::contains($sql, "ORDER BY") ? " , " : " ORDER BY ";
                $sql .= $this->revertColumnNameMap()[$property];
            }
        }
//        echo $sql . '<br/>';
        return $this->getCollection($sql);
    }

    // TODO:
    // class FilterCondition: property, value, equal/greater/smaller, nullable
    // class OrderCondition: property, asc/desc

}
