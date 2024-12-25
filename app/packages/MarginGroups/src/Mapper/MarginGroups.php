<?php

namespace EcomHelper\MarginGroups\Mapper;

use Skeletor\Mapper\MysqlCrudMapper;
use Skeletor\Mapper\PDOWrite;

class MarginGroups extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'marginGroups', 'marginGroupId');
    }
}