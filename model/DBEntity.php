<?php
/**
 * Created by PhpStorm.
 * User: sLh
 * Date: 28.04.2019
 * Time: 17:32
 */

namespace Biblio\model;


class DBEntity extends Entity
{
    public $id = 0;

    public function dto($maxChildCount = null)
    {
        $result = parent::dto($maxChildCount);
        if (is_array($result))
            return array_merge(["id" => $this->id], $result);
        else
            return null;
    }

}
