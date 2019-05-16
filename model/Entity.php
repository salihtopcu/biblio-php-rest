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
     * @param array $data
     *
     * @return mixed
     *
     * Önce gelen data'nın içeriği data, daha sonra Object'in yapısı dikkate alınarak parse işlemi yapılır.
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
                $subClass = ucfirst($property);
                if (\ArrayMethod::isAssociative($newValue)) {
                    if (class_exists($subClass) && method_exists($subClass, "constructFromData"))
                        $instance->$property = $subClass::constructFromData($newValue);
                    else {
                        $instance->$property = json_decode(json_encode($newValue));
                    }
                } else {
                    if (is_object($value) && get_class($value) == "Collection") {
                        if (!class_exists($subClass) && substr($subClass, -1) == "s") {
                            if (substr($subClass, -3) == "ies")
                                $subClass = substr($subClass, 0, -3) . "y";
                            else if (substr($subClass, -2) == "es")
                                $subClass = substr($subClass, 0, -2);
                            else if (substr($subClass, -1) == "s")
                                $subClass = substr($subClass, 0, -1);
                            if (!class_exists($subClass))
                                $subClass = $class . $subClass;
                        }
                        if (class_exists($subClass) && method_exists($subClass, "constructFromData")) {
                            foreach ($newValue as $item)
                                $instance->$property->append($subClass::constructFromData($item));
                        } else
                            $instance->$property = $newValue;
                    } else
                        $instance->$property = $newValue;
                }
            }
        }
        return $instance;
    }

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

    public function listingDto($maxChildCount = null)
    {
        return $this->dto($maxChildCount);
    }
}

class Collection extends \ArrayObject
{
    public static function constructFromData(array $data, $className)
    {
        $collection = new \Collection();
        foreach ($data as $item) {
            if (class_exists($className) && \ArrayMethod::isAssociative($item)) {
                $collection->append($className::constructFromData($item));
            }
        }
        return $collection;
    }

    /**
     * @param int|null $maxChildCount
     *
     * @return array
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
