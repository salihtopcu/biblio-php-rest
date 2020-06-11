<?php

namespace Biblio\data;

interface IRepository
{
    public function __construct(IDatabaseEnvoy $dbEnvoy);

    public function getDbEnvoy();
}

abstract class Repository implements IRepository
{
    protected $dbEnvoy;

    public final function __construct(IDatabaseEnvoy $dbEnvoy)
    {
        $this->dbEnvoy = $dbEnvoy;
    }

    public function getDbEnvoy()
    {
        return $this->dbEnvoy;
    }
}
