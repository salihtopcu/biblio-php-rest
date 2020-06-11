<?php

namespace Biblio\model;


class DBEntity extends Entity
{
    public $id = 0;

    public function dto($maxChildCount = null)
    {
        $result = parent::dto($maxChildCount);
        if (is_array($result)) {
            return empty($this->id) ? $result : array_merge(["id" => $this->id], $result);
        } else
            return null;
    }
}
