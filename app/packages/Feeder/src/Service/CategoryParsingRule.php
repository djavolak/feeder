<?php

namespace EcomHelper\Feeder\Service;

use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Repository\SupplierRepository;
use GuzzleHttp\Psr7\ServerRequest as Request;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Activity\Repository\ActivityRepository;
use Skeletor\TableView\Model\Column;
use Skeletor\TableView\Service\Table;
use Skeletor\Tenant\Repository\TenantRepositoryInterface;
use Skeletor\User\Service\User;

class CategoryParsingRule extends Table
{
    public function __construct(\EcomHelper\Feeder\Repository\CategoryParsingRule $repo, User $user, Logger $logger,
        TenantRepositoryInterface $tenant, \EcomHelper\Feeder\Filter\CategoryParsingRule $filter,
        ActivityRepository $activity,
        private CategoryRepository $categoryRepo, private SupplierRepository $supplierRepo,
        private Category $categoryService,
    )
    {
        parent::__construct($repo, $user, $logger, $tenant, $filter, $activity);
    }

    function compileTableColumns()
    {
        $categories = $this->categoryRepo->fetchAll(['tenantId' => 0]);
        foreach ($categories as $category) {
            $categoryOptions[$category->getId()] = $category->getName();
        }
        $suppliers = $this->supplierRepo->fetchAll();
        $supplierOptions[0] = 'Global';
        foreach ($suppliers as $supplier) {
            $supplierOptions[$supplier->getId()] = $supplier->getName();
        }
        $columnDefinitions = [(new Column('categoryParsingRuleId', 'ID')), (new Column('name',
            'Name'))->addJsDataParam('editColumn', true), (new Column('supplierId',
            'Supplier'))->addJsDataParam('orderable', true)
            ->addViewParam('filterable', true)
            ->addViewParam('filterValues', $supplierOptions ?? []), (new Column('categories',
            'Categories'))->addJsDataParam('orderable', true)
            ->addViewParam('filterable', true)
            ->addViewParam('filterValues', $categoryOptions ?? []), (new Column('action',
            'Rule type'))->addViewParam('filterable', true)
            ->addViewParam('filterValues',
                \EcomHelper\Feeder\Model\CategoryParsingRule::$supportedRules), new Column('description',
            'Description'),
            new Column('ruleActions', 'Rule Actions')];

        return $columnDefinitions;
    }

    public function fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter = null)
    {
        if (array_key_exists('action', $filter)) {
            $filter['action'] = substr($filter['action'], strrpos($filter['action'], '\\') + 1);
        }
        if (isset($filter['categories'])) {
            $categoryIds = [(int)$filter['categories']];
            unset($filter['categories']);
        }
        if (isset($filter['includeChildCategories'], $categoryIds)) {
            $categoryId = $categoryIds[0];
            unset($filter['includeChildCategories']);

            $childCategories = $this->categoryService->getChildCategories($categoryId);
            foreach ($childCategories as $childCategory) {
                $categoryIds[] = $childCategory->getId();
            }
            $categoryIds[] = $categoryId;
        }
        if (!isset($filter['categoryTreeFilterIds'])) {
            unset($filter['includeChildCategories']);
        }
        if (!$order) {
            $order = ['orderBy' => 'categoryParsingRuleId', 'order' => 'desc'];
        }
        $idsToInclude = [];
        if (isset($filter['ids'])) {
            $idsToInclude = explode(',', $filter['ids']);
            unset($filter['ids']);
        }
        if (isset($categoryIds)) {
           foreach ($categoryIds as $categoryId) {
               $supplerId = null;
               if (isset($filter['supplierId'])) {
                   $supplerId = $filter['supplierId'];
               }
               $rulesToInclude = $this->repo->getParsingRules($supplerId, $categoryId);
                 foreach ($rulesToInclude as $rule) {
                     $idsToInclude[] = $rule->getId();
                 }
           }
        }
        if (count($idsToInclude) > 0) {
            $filter['idsToInclude'] = implode(',', $idsToInclude);
        }
        $data = parent::fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter);


        $items = [];
        $supportedRules = \EcomHelper\Feeder\Model\CategoryParsingRule::$supportedRules;
        foreach ($data['entities'] as $item) {
            $arr = $item->toArray();
            $arr['categoryParsingRuleId'] = $item->getId();
            $arr['supplierId'] = $item->getSupplier()?->getName() ?? 'Global';
            $categories = [];
            foreach ($item->getCategories() as $category) {
                $categories[] = $category->getName();
            }
            $arr['categories'] = implode(', ', $categories);
            $arr['action'] = $supportedRules[$item->getAction()];
            $arr['ruleActions'] = $this->getRuleActions($item);
            $items[] = $arr;
        }
        return ['entities' => $items, 'count' => $data['count']];
    }

    public function getParsingRules(mixed $supplierId, mixed $categoryId)
    {
        return $this->repo->getParsingRules($supplierId, $categoryId);
    }

    private function getRuleActions(\EcomHelper\Feeder\Model\CategoryParsingRule $item): string
    {
        return <<<HTML
            <div class="ruleButtonsContainer">
                <button data-action="/category-parsing-rules/handleRulesForExistingProducts/?id={$item->getId()}&undo=0"
                 data-id="{$item->getId()}" class="applyRuleNow">Apply</button>
                <button data-action="/category-parsing-rules/handleRulesForExistingProducts/?id={$item->getId()}&undo=1" 
                data-id="{$item->getId()}" class="undoRule">Undo</button>
            </div>
            HTML;
    }

    /**
     * @throws \Exception
     */
    public function update(Request $request)
    {
        if ($this->filter) {
            $data = $this->filter->filter($request);
        } else {
            $data = $request->getParsedBody();
        }
        $existingRelation = $this->repo->fetchRuleCategories($data['categoryParsingRuleId']);
        $categories = $data['categories'];
        foreach ($existingRelation as $key => $relation) {
            if (!in_array($relation['categoryId'], $categories)) {
                $this->repo->deleteRuleCategory($relation['id']);
                unset($existingRelation[$key]);
            }
        }
        foreach ($categories as $category) {
            $exists = false;
            foreach ($existingRelation as $relation) {
                if ($relation['categoryId'] == $category) {
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $this->repo->createRuleCategory($data['categoryParsingRuleId'], $category);
            }
        }
        unset($data['categories']);
        $oldModel = $this->repo->getById((int)$request->getAttribute('id'));
        $model = $this->repo->update($data);
        $this->createActivity($model, $oldModel);

        return $model;
    }

    /**
     * @throws \Exception
     */
    public function create(Request $request)
    {
        if ($this->filter) {
            $data = $this->filter->filter($request);
        } else {
            $data = $request->getParsedBody();
        }
        $categories = $data['categories'];
        unset($data['categories']);
        $model = $this->repo->create($data);
        foreach ($categories as $category) {
            $this->repo->createRuleCategory($model->getId(), $category);
        }
        $this->createActivity($model);

        return $model;
    }

    public function delete(int $id)
    {
        $existingRelation = $this->repo->fetchRuleCategories($id);
        foreach ($existingRelation as $key => $relation) {
                $this->repo->deleteRuleCategory($relation['id']);
            }
        parent::delete($id);
    }

    public function fetchAllRulesAffectingCategory(int $categoryId)
    {
        return $this->repo->fetchAllRulesAffectingCategory($categoryId);
    }
}