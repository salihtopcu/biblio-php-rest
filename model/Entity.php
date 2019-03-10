<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 15.02.2019
 * Time: 18:56
 */

namespace BiblioRest\model;

interface iEntity {
    public static function constructWithData(array $data);
    public function dto();
}

class Entity implements iEntity
{
    /**
     * @param array $data
     * @return mixed
     *
     * Önce gelen data'nın içeriği data, daha sonra Object'in yapısı dikkate alınarak parse işlemi yapılır.
     */
    public static function constructWithData(array $data)
    {
        $class = get_called_class();
        $instance = new $class();
        foreach ($instance as $property => $value) {
            $newValue = ArrayMethod::getValue($data, $property);

            if (!is_array($newValue)) {
                if (is_double($value))
                    $instance->$property = (double)$newValue;
                else if (is_int($value) || is_integer($value))
                    $instance->$property = (int)$newValue;
                else if (is_bool($value))
                    $instance->$property = (bool)$newValue;
                else
                    $instance->$property = ArrayMethod::getValue($data, $property);
            } else {
                $subClass = ucfirst($property);
                if (ArrayMethod::isAssociative($newValue)) {
                    if (class_exists($subClass) && method_exists($subClass, "constructWithData"))
                        $instance->$property = $subClass::constructWithData($newValue);
                    else
                        echo "SubClass not found </br>";
                } else {
                    if (is_object($value) && get_class($value) == "Collection") {
                        if (!class_exists($subClass) && substr($subClass, -1) == "s") {
                            $subClass = substr($subClass, 0, -1);
                            if (!class_exists($subClass))
                                $subClass = $class . $subClass;
                        }
                        if (class_exists($subClass) && method_exists($subClass, "constructWithData")) {
                            foreach ($newValue as $item)
                                $instance->$property->append($subClass::constructWithData($item));
                        } else
                            $instance->$property = $newValue;
                    } else
                        $instance->$property = $newValue;
                }
            }
        }
        return $instance;
    }

    public function dto()
    {
        $result = array();
        foreach ($this as $key => $value) {
            if (method_exists($value, 'dto') && !is_string($value)) // String icerigi bi class ismiyla çakıştığında hata oluşuyordu
                $result[$key] = $value->dto();
            else if (is_array($value)) {
                $subResult = array();
                foreach ($value as $item) {
                    if (method_exists($item, 'dto'))
                        array_push($subResult, $item->dto());
                    else if (!is_object($item))
                        array_push($subResult, $item);
                    else
                        echo get_class($item) . " has no dto() method!<br/>";
                }
                $result[$key] = $subResult;
            } else {
                if ($value instanceof DateTime)
                    $result[$key] = $value->format(DATE_ISO8601);
                else
                    $result[$key] = $value;
            }
        }
        return $result;
    }
}

class Collection
{
    public static function constructWithData(array $data, $className)
    {

    }

    public function dto()
    {
        $result = array();
        foreach ($this as $item)
            if (is_subclass_of($item, "Entity")) {
                if (method_exists($item, 'listingDto'))
                    array_push($result, $item->listingDto());
                else
                    array_push($result, $item->dto());
            } else
                array_push($result, array('info' => 'Not extends Object class.'));
        return $result;
    }
}

class RestError {
    public $code;
    public $message;
}