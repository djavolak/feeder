<?php

namespace EcomHelper\Attribute\Service;

use EcomHelper\Attribute\Filter\Attribute as AttributeFilter;
use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Repository\AttributeGroup as AttributeGroupRepo;
use EcomHelper\Feeder\Mapper\SupplierAttributeLocalAttribute;
use EcomHelper\Feeder\Repository\CategoryParsingRule;
use Skeletor\Core\TableView\Service\TableView;
use Skeletor\User\Service\Session;
use Psr\Log\LoggerInterface as Logger;

class Attribute extends TableView
{
    public function __construct(
        \EcomHelper\Attribute\Repository\Attribute $repo, Session $user, Logger $logger, AttributeFilter $filter,
        private AttributeGroupRepo $attributeGroupRepo,
//        private ProductAttributeValues $productAttributeValuesMapper,
//        private CategoryParsingRule $categoryParsingRuleRepo,
//        private SupplierAttributeLocalAttribute $supplierAttributeMappingRepo
    )
    {
        parent::__construct($repo, $user, $logger, $filter);
    }

    public function compileTableColumns()
    {
        $attributeGroups = $this->attributeGroupRepo->fetchAll();
        $attributeGroupsFormatted = [];
        foreach ($attributeGroups as $attributeGroup) {
            $attributeGroupsFormatted[$attributeGroup->getId()] = $attributeGroup->getName();
        }
        return [
            ['name' => 'name', 'label' => 'Attribute Name'],
            ['name' => 'group', 'label' => 'Group', 'sortable' => false, 'filterData' => $attributeGroupsFormatted],
            ['name' => 'values', 'label' => 'Values', 'sortable' => false],
            ['name' => 'position', 'label' => 'Priority'],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at'],
        ];
    }

    public function prepareEntities($entities)
    {
        //@todo fix this, make attributeId be id in the DB so this works fluently
//        if (isset($order['orderBy']) && $order['orderBy'] === 'id') {
//            $order['orderBy'] = 'attributeId';
//        }
//        $data = $this->repo->fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter);

        $items = [];
        /* @var \EcomHelper\Attribute\Model\Attribute $attribute */
        foreach ($entities as $attribute) {
            $values = '';
            $attributeValues = $attribute->getValues();
            $countAttributeValues = count($attributeValues);
            foreach ($attributeValues as $key => $value) {
                $comma = ',';
                if ($key === $countAttributeValues - 1) {
                    $comma = '';
                }
                $values .= $value->getValue() . $comma;
            }
            $groups = [];
            foreach ($attribute->getGroups() as $group) {
                $groups[] = $group->getName();
            }
            $itemData = [
                'name' => [
                    'value' => $attribute->getName(),
                    'editColumn' => true,
                ],
                'group' => implode(', ', $groups),
                'values' => $values,
                'position' => (int) $attribute->getPosition(),
                'createdAt' => $attribute->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $attribute->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $attribute->getId(),
            ];
        }
        return $items;
    }

    public function getAttributeValues($attributeId)
    {
        return $this->attributeValuesMapper->fetchAll(['attributeId' => $attributeId]);
    }

//    public function getAttributeValue($value, $attributeId)
//    {
//        return $this->repo->getAttributeValue($value, $attributeId);
//    }

    public function getAttributes($search, $limit)
    {
        $data = [];
        if ($search) {
            $entities = $this->repo->fetchAll(['attributeName' => $search], $limit);
        } else {
            $entities = $this->repo->fetchAll([], $limit);
        }
        foreach ($entities as $attribute) {
            $data[] = $attribute->toArray();
        }
        if ($data === []) {
            return ['message' => 'No more Results'];
        }
        return $data;
    }

    public function getAttributesWithoutValues()
    {
        return $this->repo->getAttributesWithoutValues();
    }

    public function getOrderedAttributesForProduct($productId)
    {
        return $this->productAttributeValuesMapper->getOrderedAttributes($productId);
    }

    public function getGroupAttributesByGroupId($id)
    {
        $data = [];
        if ($id) {
            $entities = $this->repo->fetchAll(['attributeGroupId' => $id]);
            foreach ($entities as $entity) {
                $data[] = $entity->toArray();
            }
        }
        return $data;
    }

    public function saveAttributesForProduct($productId, $dataAttributes, $update = false)
    {
        //@ todo make better, we delete all and insert again
        if ($update) {
            $this->productAttributeValuesMapper->deleteBy('productId', $productId);
        }
        if ($dataAttributes === null) {
            return true;
        }
        $usedAttributeValueIds = [];
        if ($dataAttributes) {
            foreach ($dataAttributes as $attributeId => $values) {
                $attDataFormatted = [];
                $attDataFormatted['productId'] = $productId;
                $attDataFormatted['attributeId'] = (int)$attributeId;
                foreach ($values as $key => $value) {
                    if ($key % 2 === 0) {
                        if ($values[$key + 1] === '-1') {
                            continue;
                        }
                        $attDataFormatted['attributeName'] = $value['name'];
                        $attributeValues = explode('#', $values[$key + 1]['value']);
                        $attDataFormatted['attributeValue'] = $attributeValues[0];
                        $attDataFormatted['attributeValueId'] = (int)$attributeValues[1];
                        if (isset($usedAttributeValueIds[(int)$attributeValues[1]]) ||
                            $this->productHasAttributeValue($productId, $attributeId, (int)$attributeValues[1])) {
                            continue;
                        }
                        $usedAttributeValueIds[(int)$attributeValues[1]] = true;
                        $this->productAttributeValuesMapper->insert($attDataFormatted);
                    }
                }
            }

            return true;
        }
        return false;
    }

    public function productHasAttributeValue($productId, $attributeId, $attributeValueId)
    {
        return (int)$this->productAttributeValuesMapper->productHasAttributeValue(
                $productId,
                $attributeId,
                $attributeValueId
            ) > 0;
    }

    public function getAttributeName($attributeId)
    {
        return $this->repo->getById($attributeId)->getAttributeName();
    }

    public function getAttributesSorted($attributeIds)
    {
        return $this->repo->getAttributesSorted($attributeIds);
    }

    public function createNewAttributeForProduct($productId, $newAttributes)
    {
        if ($newAttributes !== []) {
            foreach ($newAttributes as $newAttribute) {
                // insert attr, insert attr val, insert to productattrvals
                $attribute = $this->repo->create([
                    'attributeName' => $newAttribute['name'],
                    'attributeGroupId' => null
                ]);
                if (isset($newAttribute['values']) && count($newAttribute['values']) > 0) {
                    foreach ($newAttribute['values'] as $value) {
                        $attributeValueId = $this->attributeValuesMapper->insert([
                            'attributeId' => $attribute->getId(),
                            'attributeValue' => $value
                        ]);
                        $this->productAttributeValuesMapper->insert([
                            'productId' => $productId,
                            'attributeId' => $attribute->getId(),
                            'attributeValueId' => $attributeValueId,
                            'attributeName' => $newAttribute['name'],
                            'attributeValue' => $value
                        ]);
                    }
                }
            }
        }
    }

    public function getAttributesForSearch($search, $limit)
    {
        return $this->repo->fetchAllForSearch($search, $limit);
    }
}