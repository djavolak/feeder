<?php

namespace EcomHelper\Feeder\Mapper;

use Skeletor\Mapper\MysqlCrudMapper;
use Skeletor\Mapper\PDOWrite;

class ProductSupplierAttributes extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'productSupplierAttributes', 'id');
    }
}