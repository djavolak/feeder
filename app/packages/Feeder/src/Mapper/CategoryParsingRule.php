<?php /** @noinspection SqlResolve */

namespace EcomHelper\Feeder\Mapper;

use Skeletor\Mapper\MysqlCrudMapper;
use Skeletor\Mapper\PDOWrite;

class CategoryParsingRule extends MysqlCrudMapper
{
    public function __construct(PDOWrite $pdo)
    {
        parent::__construct($pdo, 'categoryParsingRule', 'categoryParsingRuleId');
    }

    public function fetchTableData(
        $search, $filter = null, $searchableColumns = [], $offset = 0, $limit = 10, $order = false, $returnCount = false,
        $uncountableFilter = null
    ) {
        $sql = "SELECT * FROM `{$this->tableName}` ";
        if($returnCount) {
            $sql = "SELECT COUNT(*) FROM `{$this->tableName}` ";
        }
        $i = 0;
        $filterActive = false;
        if ($filter) {
            if (isset($filter['categories'])) {
                unset($filter['categories']);
            }
            if (isset($filter['idsToInclude'])) {
                $idsToInclude = $filter['idsToInclude'];
                unset($filter['idsToInclude']);
            }
            foreach ($filter as $name => $value) {
                $filterActive = true;
                $sqlValue = "'{$value}'";
                if (is_numeric($value)) {
                    $sqlValue = "{$value}";
                }
                if ($i === 0) {
                    if ($name !== 'action') {
                        $sql .= "WHERE `{$name}` = {$sqlValue} ";
                    } else {
                        $sql .= "WHERE `{$name}` LIKE '%".$value."%' ";
                    }
                } else {
                    if ($name !== 'action') {
                        $sql .= "AND `{$name}` = {$sqlValue} ";
                    } else {
                        $sql .= "AND `{$name}` LIKE '%".$value."%' ";
                    }
                }
                $i++;
            }
            if(isset($idsToInclude)) {
                if(count($filter) === 0) {
                    $sql .= " WHERE $this->primaryKeyName in ($idsToInclude)";
                } else {
                    $sql .= " AND $this->primaryKeyName in ($idsToInclude)";
                }
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
                    if(count($searchableColumns) === 1) {
                        $sql .= ' ) ';
                    }
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

    public function searchInData($search)
    {
        //search in data column
        $sql = "SELECT * FROM `$this->tableName` WHERE `data` LIKE '%{$search}%'";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return  $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function fetchAllRulesAffectingCategory(int $categoryId)
    {
        $len = strlen($categoryId);
        $sql = "SELECT * FROM `$this->tableName` WHERE `data` LIKE '%s:8:\"category\";s:{$len}:\"{$categoryId}\"%'";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        return  $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}