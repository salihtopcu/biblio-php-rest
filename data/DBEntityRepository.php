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
    protected $dateTimeFormat = "Y-m-d H:i:s";

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

    /**
     * @return string
     */
    protected final function generateSelectPhrase()
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
     * @param array $conditions
     *
     * @return string
     */
    protected final function generateWherePhrase(array $conditions)
    {
        $phrase = "";
        if (count($conditions) > 0 && \ArrayMethod::isAssociative($conditions)) {
            $revertedMap = $this->revertColumnNameMap();
            foreach ($conditions as $property => $value) {
                if (!is_null($value)) {
                    $isDate = is_object($value) && get_class($value) == "DateTime";
                    $isLike = is_string($value) && !empty($value) && (substr($value, 0, 1) == '%' || substr($value, -1) == '%');
                    $phrase .= \StringMethod::contains($phrase, "WHERE") ? "AND " : "WHERE ";
                    $phrase .= $revertedMap[$property];
                    $phrase .= $isLike ? " LIKE " : " = ";
                    $phrase .= is_string($value) || $isDate ? "'" : "";
                    if ($this->convertBoolToInt && is_bool($value))
                        $phrase .= (int) $value;
                    else
                        $phrase .= $value;
                    $phrase .= is_string($value) || $isDate ? "' " : " ";
                }
            }
        }
        return $phrase;
    }

    /**
     * @param array $properties
     *
     * @return string
     */
    protected final function generateOrderPhrase(array $properties)
    {
        $phrase = "";
        if (count($properties) > 0) {
            $revertedMap = $this->revertColumnNameMap();
            foreach ($properties as $property) {
                if (!is_null($property) && $property != "") {
                    $phrase .= \StringMethod::contains($sql, "ORDER BY") ?
                        ", " : " ORDER BY ";
                    $phrase .= $revertedMap[$property] . " ";
                }
            }
        }
        return $phrase;
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool|string
     */
    protected function generateInsertPhrase(DBEntity $instance)
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
                            $sqlValues .= date_format($value, $this->dateTimeFormat);
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
            return $sql;
        } else {
            echo "instance must be " . $this->getEntityClassName();
            return false;
        }
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool|string
     */
    public function generateUpdatePhrase(DBEntity $instance)
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
                            $setPhrase .= date_format($value, $this->dateTimeFormat);
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
            return $sql;
        } else {
            echo "instance must be " . $this->getEntityClassName() . " and must have id";
            return false;
        }
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool
     */
    protected function generateDeletePhrase(array $conditions)
    {
        $sql = "DELETE FROM " . $this->getTableName() . " " . $this->generateWherePhrase($conditions);
        return $sql;
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
     * @param array|null $conditions
     * @param array|null $orderBy
     *
     * @return \Collection
     */
    public function filter(array $conditions = null, array $orderBy = null)
    {
        $wherePhrase = "";
        if (!is_null($conditions)) {
            if (\ArrayMethod::isAssociative($conditions))
                $wherePhrase = $this->generateWherePhrase($conditions);
            else {
                echo "conditions array must be associative";
                return null;
            }
        }
        $orderPhrase = "";
        if (!is_null($orderBy)) {
            $orderPhrase = $this->generateOrderPhrase($orderBy);
        }
        $sql = $this->generateSelectPhrase() . " $wherePhrase $orderPhrase";
//        echo $sql . '<br/>';
        return $this->getCollection($sql);
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool
     */
    public function insert(DBEntity $instance)
    {
        $sql = $this->generateInsertPhrase($instance);
        if (is_string($sql))
            return $this->dbEnvoy->runInsertSql($sql);
        else
            return false;
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool
     */
    public function update(DBEntity $instance)
    {
        if ($instance->id > 0 && $this->getEntityClassName() == get_class($instance)) {
            $sql = $this->generateUpdatePhrase($instance);
            if (is_string($sql))
                return $this->dbEnvoy->runUpdateSql($sql);
            else
                return false;
        } else {
            echo "instance must be " . $this->getEntityClassName() . " and must have id";
            return false;
        }

    }

    public function deleteWithConditions(array $conditions)
    {
        $sql = $this->generateDeletePhrase($conditions);
//        echo $sql . '<br/>';
        if (\StringMethod::contains($sql, "WHERE")) {
            return $this->dbEnvoy->runDeleteSql($sql);
        } else {
            echo "conditions array must be associative and must contain at least one valid condition";
            return false;
        }
    }

    /**
     * @param DBEntity $instance
     *
     * @return bool
     */
    public function delete(DBEntity $instance)
    {
        if ($instance->id > 0 && $this->getEntityClassName() == get_class($instance)) {
            return $this->deleteWithConditions(["id" => $instance->id]);
        } else {
            echo "instance must be " . $this->getEntityClassName() . " and must have id";
            return false;
        }
    }

    // TODO:
    // class FilterCondition: property, value, equal/greater/smaller, nullable
    // class OrderCondition: property, asc/desc

}
