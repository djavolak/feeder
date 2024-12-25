<?php
namespace EcomHelper\Attribute\Mapper;

use Skeletor\Core\Mapper\PDOWrite;
use Skeletor\Core\Mapper\MysqlCrudMapper;

class Attribute extends MysqlCrudMapper
{
//    public function __construct(PDOWrite $pdo, private AttributeGroupAttributeValue $attributeGroupAttributeValueMapper)
//    {
//        parent::__construct($pdo, 'attribute', 'attributeId');
//    }

    public function fetchTableData(
        $search,
        $filter = null,
        $searchableColumns = [],
        $offset = 0,
        $limit = 10,
        $order = false,
        $returnCount = false,
        $uncountableFilter = null
    ) {
        $attributeIds = null;
        if(isset($filter['groupId']) && $filter['groupId'] !== '-1') {
            $attributeIdsByGroup = $this->attributeGroupAttributeValueMapper->getAttributeIdsByGroupId((int)$filter['groupId']);
            $attributeIdsByGroupFormatted = [];
            foreach($attributeIdsByGroup as $id) {
                $attributeIdsByGroupFormatted[] = $id['attributeId'];
            }
            if(count($attributeIdsByGroupFormatted) > 0) {
                $attributeIds = implode(',', $attributeIdsByGroupFormatted);
            }
        }
        unset($filter['groupId']);

        $sql = "SELECT * FROM `{$this->tableName}` ";
        if($returnCount) {
            $sql = "SELECT COUNT(*) FROM `{$this->tableName}` ";
        }
        $i = 0;
        $filterActive = false;
        if ($filter) {
            foreach ($filter as $name => $value) {
                $filterActive = true;
                $sqlValue = "'{$value}'";
                if (is_numeric($value)) {
                    $sqlValue = "{$value}";
                }
                if ($i === 0) {
                    $sql .= " WHERE `{$name}` = {$sqlValue} ";
                } else {
                    $sql .= " AND `{$name}` = {$sqlValue} ";
                }
                $i++;
            }
        }
        if ($uncountableFilter) {
            $i = 0;
            foreach ($uncountableFilter as $name => $value) {
                $sqlValue = "'{$value}'";
                if (is_numeric($value)) {
                    $sqlValue = "{$value}";
                }
                if ($i === 0 && !$filter) {
                    $sql .= " WHERE `{$name}` = {$sqlValue} ";
                } else {
                    $sql .= " AND `{$name}` = {$sqlValue} ";
                }
                $i++;
            }
        }
        if ($filterActive && !$search) {
            $sql .= ' AND (1=1 ';
        }
        if ($search) {
            $i = 0;
            foreach ($searchableColumns as $key => $column) {
                $sqlValue = "'%$search%'";
                if ($i === 0 && !$filterActive) {
                    $sql .= " WHERE `{$column}` LIKE {$sqlValue} ";
                } elseif($i === 0 && $filterActive) {
                    $sql .= " AND (`{$column}` LIKE {$sqlValue} ";
                } else {
                    $closedParenthesis = '';
                    if ($filterActive && $key === array_key_last($searchableColumns)) {
                        $closedParenthesis = ')';
                    }
                    $sql .= " OR `{$column}` LIKE {$sqlValue} $closedParenthesis";
                }
                $i++;
            }
        }
        if ($filterActive && !$search) {
            $sql .= ' ) ';
        }
        if($attributeIds) {
            if($filterActive || $search) {
                $sql .= "AND attributeId in ($attributeIds)";
            } else {
                $sql .= "WHERE attributeId in ($attributeIds)";
            }
        }
        if(!$returnCount) {
            if ($order) {
                $sql .= " ORDER BY {$order['orderBy']} {$order['order']} ";
            }
            $sql .= sprintf(" LIMIT %d,%d", $offset, $limit);
        }

        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        if($returnCount) {
            return  $stmt->fetch(\PDO::FETCH_COLUMN);
        }
        return  $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAttributesWithoutValues()
    {
        $sql = "SELECT attributeId, attributeName, position FROM {$this->tableName}";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getAttributesSorted($attributeIds) {
        $sql = "SELECT attributeId from {$this->tableName} WHERE attributeId IN ({$attributeIds}) ORDER BY position DESC"; // client wanted it this way, oof
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchAllForSearch($search, $limit = null): array
    {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE `attributeName` LIKE '%{$search}%' ";
        if ($limit) {
            if (is_int($limit)) {
                $sql .= " LIMIT " . $limit;
            } elseif (is_array($limit)) {
                $sql .= sprintf(" LIMIT %d,%d", $limit['offset'], $limit['limit']);
            } else {
                throw new \Exception('Unsupported limit type, use integer or array with offset/limit keys.');
            }
        }
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();

        return  $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}