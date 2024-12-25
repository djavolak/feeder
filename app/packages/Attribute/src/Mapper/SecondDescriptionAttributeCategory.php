<?php

namespace EcomHelper\Attribute\Mapper;

use Skeletor\Core\Mapper\PDOWrite;
use Skeletor\Core\Mapper\MysqlCrudMapper;

class SecondDescriptionAttributeCategory extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'secondDescriptionAttributeCategory', 'secondDescriptionAttributeCategoryId');
    }

    public function getAttributeIdsByCategoryId($categoryId)
    {
        $sql = "SELECT attributeId from {$this->tableName} WHERE categoryId = {$categoryId} ORDER BY secondDescriptionAttributeCategoryId ASC";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}