<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 15.02.2019
 * Time: 23:30
 */

namespace BiblioRest\entity;

class Collection
{
    public static function constructFromData(array $data, $className)
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