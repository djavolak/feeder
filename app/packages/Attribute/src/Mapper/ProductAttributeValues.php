<?php


namespace EcomHelper\Attribute\Mapper;


use Skeletor\Core\Mapper\PDOWrite;
use Skeletor\Core\Mapper\MysqlCrudMapper;

class ProductAttributeValues extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'productAttributeValues', 'id');
    }

    public function getOrderedAttributes($productId)
    {
        //$orderedAttributes = '1442,1040,1417,163,1486,1689,513,645,614,811,1530,1076,1209,140,833,114,1192,978';
        $sql = "SELECT * FROM `productAttributeValues` JOIN attribute using(attributeId) WHERE `productId` = {$productId} 
ORDER BY ISNULL(position), position ASC";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function bulkDeleteProductAttributes($attributeId, $attributeValueId, $productIds)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE `attributeId` = {$attributeId} AND `attributeValueId` = {$attributeValueId} AND `productId` IN ({$productIds});";
        return $this->driver->prepare($sql)->execute();
    }

    public function fetchProductIdsIncludingAttributes($includeAttributes)
    {
        $count = count($includeAttributes);
        $sql = "SELECT `productId` from {$this->tableName} WHERE (";
        foreach($includeAttributes as $key => $attribute) {
            if($key === 0) {
                if(!$attribute['attributeValueId']) {
                    $sql .= "(`attributeId` = {$attribute['attributeId']} AND `attributeValueId` != '')";
                    continue;
                }
                $sql .= "(`attributeId` = {$attribute['attributeId']} AND `attributeValueId` = {$attribute['attributeValueId']})";
                continue;
            }
            $sql .= " OR ";
            if(!$attribute['attributeValueId']) {
                $sql .= "(`attributeId` = {$attribute['attributeId']} AND `attributeValueId` != '')";
            } else {
                $sql .= "(`attributeId` = {$attribute['attributeId']} AND `attributeValueId` = {$attribute['attributeValueId']})";
            }
        }
        $sql .= ");";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        $results =  $stmt->fetchAll(\PDO::FETCH_GROUP);
        $ids = [];
        foreach($results as $key => $result) {
            if(count($result) === $count) {
                $ids[] = $key;
            }
        }
        return $ids;
    }

    public function productHasAttributeValue($productId, $attributeId, $attributeValueId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->tableName} WHERE productId = {$productId} AND
                                 attributeId = {$attributeId} AND attributeValueId = {$attributeValueId}";

        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_COLUMN);
    }

    public function getProductIdsWithAttributeValue(int $attributeId, string $attributeValue)
    {
        $sql = "SELECT productId FROM {$this->tableName} WHERE attributeId = {$attributeId} AND attributeValue = '{$attributeValue}'";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

}