<?php

namespace EcomHelper\Feeder\Mapper;

use PDO;
use Skeletor\Mapper\MysqlCrudMapper;
use Skeletor\Mapper\PDOWrite;

class SupplierAttributeMapping extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'supplierAttribute', 'supplierAttributeId');
    }

    public function fetchSupplierAttributes(int $supplierId = null, $search = null, $category = null): bool|array
    {
        $sql = "select distinct attribute from $this->tableName";
        if ($supplierId) {
            $sql .= " where supplier = $supplierId";
        }
        if ($search) {
            $sql .= " and attribute like '%$search%'";
        }
        if ($category) {
            $sql .= " and category = $category";
        }
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchSupplierCategories(int $supplierId = null, $search = null): bool|array
    {
        $sql = "select distinct category from $this->tableName";
        if ($supplierId) {
            $sql .= " where supplier = $supplierId";
        }
        if ($search) {
            $sql .= " and category like '%$search%'";
        }
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function fetchSupplierAttributeValues(int $supplierId = null, string $attributeName = null, $search = null): bool|array
    {
        $sql = "select distinct attributeValue from $this->tableName";
        if ($supplierId) {
            $sql .= " where supplier = $supplierId";
        }
        if ($attributeName) {
            $sql .= " and attribute = '$attributeName'";
        }
        if ($search) {
            $sql .= " and attributeValue like '%$search%'";
        }
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}