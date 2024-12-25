<?php

namespace EcomHelper\Feeder\Repository;

use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Feeder\Mapper\ParsingRuleCategory;
use EcomHelper\Product\Repository\SupplierRepository;
use Skeletor\Mapper\NotFoundException;
use Skeletor\Model\Model;
use Skeletor\TableView\Repository\TableViewRepository;

class CategoryParsingRule extends TableViewRepository
{
    public function __construct(
        \EcomHelper\Feeder\Mapper\CategoryParsingRule $mapper,
        \DateTime $dt,
        private SupplierRepository $supplierRepository,
        private CategoryRepository $categoryRepository,
        private ParsingRuleCategory $parsingRuleCategoryMapper
    )
    {
        parent::__construct($mapper, $dt);
    }

    /**
     * @throws NotFoundException
     * @throws \Exception
     */
    function make($itemData): Model
    {
        $data = [];
        foreach ($itemData as $name => $value) {
            if (in_array($name, ['createdAt', 'updatedAt'])) {
                $data[$name] = null;
                if ($value) {
                    if (strtotime($value)) {
                        $dt = clone $this->dt;
                        $dt->setTimestamp(strtotime($value));
                        $data[$name] = $dt;
                    } else {
                        $data[$name] = null;
                    }
                }
            } else {
                $data[$name] = $value;
            }
        }
        $data['categoryParsingRuleId'] = (int)$itemData['categoryParsingRuleId'] ?? null;
        if ((int)$data['supplierId'] !== 0) {
            $data['supplier'] = $this->supplierRepository->getById((int)$data['supplierId']);
        } else {
            $data['supplier'] = null;
        }
        unset($data['supplierId']);
        $categoryIds = $itemData['categories'] ?? [];
        unset($data['categories']);
        if ($categoryIds !== []) {
            foreach ($categoryIds as $categoryId) {
                $data['categories'][] = $this->categoryRepository->getById((int)$categoryId);
            }
        } else {
            $cats = $this->parsingRuleCategoryMapper->fetchAll(['parsingRuleId' => $data['categoryParsingRuleId']]);
            foreach ($cats as $cat) {
                $data['categories'][] = $this->categoryRepository->getById((int)$cat['categoryId']);
            }
        }
        $data['action'] = (string)$itemData['action'];
        $data['data'] = $itemData['data'];
        return new \EcomHelper\Feeder\Model\CategoryParsingRule(...$data);
    }

    function getSearchableColumns(): array
    {
        return ['action', 'data', 'categoryParsingRuleId'];
    }

    /**
     * @throws \Exception
     */
    public function fetchRuleCategories(int $ruleId): array
    {
        return $this->parsingRuleCategoryMapper->fetchAll(['parsingRuleId' => $ruleId]);
    }

    public function createRuleCategory(int $ruleId, int $categoryId): void
    {
        $this->parsingRuleCategoryMapper->insert(['parsingRuleId' => $ruleId, 'categoryId' => $categoryId]);
    }

    public function deleteRuleCategory(int $id): void
    {
        $this->parsingRuleCategoryMapper->delete($id);
    }

    /**
     * @throws NotFoundException
     * @throws \Exception
     */
    public function getParsingRules(int $supplierId = null, int $categoryId = null): array
    {
        if ($supplierId) {
            $args = ['supplierId' => $supplierId];
        }
        if ($supplierId === 0) {
            $args = ['supplierId' => 0];
        }
        $rules = $this->mapper->fetchAll($args ?? []);
        $result = [];
        foreach ($rules as $rule) {
            $categories = $this->parsingRuleCategoryMapper->fetchAll(['parsingRuleId' => $rule['categoryParsingRuleId']]);
            foreach ($categories as $category) {
                if ($categoryId === null) {
                    $result[] = $this->make($rule);
                } else {
                    if ((int)$category['categoryId'] === $categoryId) {
                        $result[] = $this->make($rule);
                    }
                }
            }
        }
        return $result;
    }

    /**
     * @throws NotFoundException
     */
    public function searchInData($search)
    {
        $rules = $this->mapper->searchInData($search);
        $result = [];
        foreach ($rules as $rule) {
            if (strpos($rule['data'], $search) !== false) {
                $result[] = $this->make($rule);
            }
        }
        return $result;
    }

    /**
     * @throws NotFoundException
     */
    public function getRulesByCatId(int $catId): array
    {
        $results = [];
        $rules = $this->parsingRuleCategoryMapper->fetchAll(['categoryId' => $catId]);
        foreach ($rules as $rule) {
            $results[] = $this->getById($rule['parsingRuleId']);
        }
        return $results;
    }

    public function fetchAllRulesAffectingCategory(int $categoryId): array
    {
        $rules = $this->mapper->fetchAllRulesAffectingCategory($categoryId);
        $results = [];
        foreach ($rules as $rule) {
            $results[] = $this->make($rule);
        }
        return $results;
    }
}