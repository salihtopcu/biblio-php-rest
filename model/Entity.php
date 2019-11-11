<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 15.02.2019
 * Time: 18:56
 */

namespace Biblio\model;

interface iEntity
{
    public static function constructFromData(array $data);

    /**
     * @param int|null $maxChildCount
     *
     * @return array
     */
    public function dto($maxChildCount = null);
}

abstract class Entity implements iEntity
{
    public static $dateTimeFormat = DATE_ISO8601;

    /**
     * Constructs an Entity by array. You can use this method to parse JSON data to an Entity.
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function constructFromData(array $data)
    {
        $class = get_called_class();
        $instance = new $class();
        foreach ($instance as $property => $value) {
            $newValue = \ArrayMethod::getValue($data, $property);
            if (!is_array($newValue)) {
                if (is_double($value))
                    $instance->$property = (double) $newValue;
                else if (is_int($value) || is_integer($value))
                    $instance->$property = (int) $newValue;
                else if (is_bool($value))
                    $instance->$property = (bool) $newValue;
                else {
                    if (!is_null($newValue))
                        $instance->$property = \DateMethod::create($newValue);
                    if (is_null($instance->$property))
                        $instance->$property = $newValue;
                }
            } else {
                if (\ArrayMethod::isAssociative($newValue)) {
                    $subClass = self::generateEntityClassName($property);
                    if (class_exists($subClass) && method_exists($subClass, "constructFromData"))
                        $instance->$property = $subClass::constructFromData($newValue);
                    else {
                        $instance->$property = json_decode(json_encode($newValue));
                    }
                } else {
                    $subClass = self::generateCollectionItemClassName($property);
                    if (!is_null($subClass)) {
//                        if (!is_a($value, Collection::class) && !is_subclass_of($value, Collection::class))
                        if (is_null($value))
                            $instance->$property = new Collection();
                        if (!is_null($value)) {
                            foreach ($newValue as $item)
                                $instance->$property->append($subClass::constructFromData((array) $item));
                        }
                    } else
                        $instance->$property = $newValue;
                }
            }
        }
        return $instance;
    }

    private static function generateEntityClassName($propertyName) {
        $subClass = ucfirst($property);
        return class_exists($subClass) && method_exists($subClass, "constructFromData") ? $subClass : null;
    }

    private static function generateCollectionItemClassName($collectionName) {
        $subClass = ucfirst($collectionName);
        if (substr($subClass, -1) == "s")
            $newSubClass = substr($subClass, 0, -1);
        if (!class_exists($newSubClass) && substr($subClass, -2) == "es")
            $newSubClass = substr($subClass, 0, -2);
        if (!class_exists($newSubClass) && substr($subClass, -3) == "ies")
            $newSubClass = substr($subClass, 0, -3) . "y";
        return class_exists($newSubClass) && method_exists($newSubClass, "constructFromData") ? $newSubClass : null;
    }

    /**
     * Converts Entity to array. You can use this method to send your Entity as response data.
     *
     * @param null $maxChildCount
     *
     * @return array|null
     */
    public function dto($maxChildCount = null)
    {
//        echo "<br/>COUNTER: $maxChildCount";
        if (is_null($maxChildCount) || $maxChildCount >= 0) {
            $result = array();
            foreach ($this as $key => $value) {
//                echo "<br/>KEY: $key";
                if (is_null($value) && (is_null($maxChildCount) || $maxChildCount > 0)) {
                    $getterMethod = 'get' . ucfirst($key);
                    if (method_exists($this, $getterMethod)) {
//                        echo "<br/>$getterMethod<br/>";
                        $value = $this->$getterMethod();
                    }
                }
                if (is_object($value)) {
                    if (is_subclass_of($value, Entity::class) && method_exists($value, 'dto')) {
//                        echo '<br/>to entity dto()';
                        $result[$key] = is_null($maxChildCount) ? $value->dto(null) : $value->dto($maxChildCount - 1);
                    } else if (get_class($value) == Collection::class || is_subclass_of($value, Collection::class)) {
//                        echo '<br/>to collection dto()';
                        $result[$key] = is_null($maxChildCount) ? $value->dto(null) : $value->dto($maxChildCount - 1);
                    } else if (get_class($value) == "DateTime") {
                        $result[$key] = \DateMethod::format($value, Entity::$dateTimeFormat);
//                        echo "<br/>VAL: $result[$key]";
                    } else {
                        $result[$key] = "UNDEFINED";
//                        echo "<br/>VAL: $result[$key]";
                    }
                } else {
                    $result[$key] = $value;
//                    echo "<br/>VAL: $result[$key]<br/>";
                }
            }
            return $result;
        } else
            return null;
    }

    /**
     * You can override this method to customize your Entities in Collections
     *
     * @param null $maxChildCount
     *
     * @return array|null
     */
    public function listingDto($maxChildCount = null)
    {
        return $this->dto($maxChildCount);
    }
}

class Collection extends \ArrayObject
{
    /**
     * Constructs a Collection by array. You can use this method to parse JSON data to a Collection.
     *
     * @param array $data
     *
     * @return mixed
     */
    public static function constructFromData(array $data, $className)
    {
        $collection = new Collection();
        foreach ($data as $item) {
            if (class_exists($className) && \ArrayMethod::isAssociative($item)) {
                $collection->append($className::constructFromData($item));
            }
        }
        return $collection;
    }

    /**
     * Converts Collection to array. You can use this method to send your Collection as response data.
     *
     * @param null $maxChildCount
     *
     * @return array|null
     */
    public function dto($maxChildCount = null)
    {
        $result = array();
        foreach ($this as $item) {
            if (method_exists($item, 'listingDto')) {
//                echo '<br/>to entity listingDto()';
                array_push($result, $item->listingDto($maxChildCount));
            } else {
//                echo '<br/>3';
                array_push($result, array('info' => 'Does not exist dto method.'));
            }
        }
        return $result;
    }
}
