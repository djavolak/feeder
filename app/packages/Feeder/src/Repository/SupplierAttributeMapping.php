<?php

namespace EcomHelper\Feeder\Repository;

use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Attribute\Repository\AttributeValues;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Feeder\Mapper\ProductSupplierAttributes;
use EcomHelper\Feeder\Mapper\SupplierAttributeLocalAttribute;
use EcomHelper\Product\Repository\SupplierRepository;
use Skeletor\Mapper\NotFoundException;
use Skeletor\Model\Model;
use Skeletor\TableView\Repository\TableViewRepository;

class SupplierAttributeMapping extends TableViewRepository
{
    public function __construct(
        \EcomHelper\Feeder\Mapper\SupplierAttributeMapping $mapper,
        \DateTime $dt,
        private Attribute $attributeRepository,
        private AttributeValues $attributeValuesRepository,
        private SupplierRepository $supplierRepository,
        private CategoryRepository $categoryRepository,
        private ProductSupplierAttributes $productSupplierAttributesMapper,
        private SupplierAttributeLocalAttribute $supplierAttributeLocalAttributeMapper
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
        $relations = $this->supplierAttributeLocalAttributeMapper->fetchAll(['supplierAttributeId' => (int)$data['supplierAttributeId']]);
        $data['localAttributes'] = [];
        if (count($relations) > 0) {
            foreach ($relations as $relation) {
                $data['localAttributes'][$relation['localAttributeId']]['localAttribute'] = $this->attributeRepository->getById((int)$relation['localAttributeId']);
                $data['localAttributes'][$relation['localAttributeId']]['localAttributeValues'][] = $this->attributeValuesRepository->getById((int)$relation['localAttributeValueId']);
            }
        }
        if (isset($data['supplier'])) {
            $data['supplier'] = $this->supplierRepository->getById((int)$data['supplier']);
        }
        if (isset($data['category'])) {
            $data['category'] = $this->categoryRepository->getById((int)$data['category']);
        }
        return new \EcomHelper\Feeder\Model\SupplierAttributeMapping(...$data);
    }

    function getSearchableColumns(): array
    {
        return ['attribute', 'attributeValue', 'localAttribute', 'localAttributeValue'];
    }

    function fetchSupplierAttributes(int $supplierId = null, $search = null, $category = null): array
    {
        return $this->mapper->fetchSupplierAttributes($supplierId, $search, $category);
    }

    /**
     * @throws NotFoundException
     */
    function fetchSupplierCategories(int $supplierId = null, $search = null): array
    {
        $cats = [];
        foreach ($this->mapper->fetchSupplierCategories($supplierId, $search) as $catId) {
            $cats[] = ['catId' => $catId, 'catName' => $this->categoryRepository->getById($catId)?->getName()];
        }
        return $cats;
    }

    function fetchSupplierAttributeValues(int $supplierId = null, string $attributeName = null, $search = null): array
    {
        return $this->mapper->fetchSupplierAttributeValues($supplierId, $attributeName, $search);
    }

    /**
     * @throws \Exception
     */
    function createRelationWithProduct($sku, $attributeMappingId)
    {
        return $this->productSupplierAttributesMapper->insert([
            'sku' => $sku,
            'supplierAttributeId' => $attributeMappingId
        ]);
    }

    /**
     * @throws \Exception
     */
    function getRelatedProductIds(int $attributeMappingId): array
    {
        foreach ($this->productSupplierAttributesMapper->fetchAll(['supplierAttributeId' => $attributeMappingId]) as $relation) {
            $productIds[] = $relation['productId'];
        }
        return $productIds ?? [];
    }

    /**
     * @throws \Exception
     */
    function checkIfRelationExist($sku, $mappingId, $productId = null): array
    {
        if ($productId) {
            return $this->productSupplierAttributesMapper->fetchAll(['supplierAttributeId' => $mappingId, 'productId' => $productId]);
        }
        return $this->productSupplierAttributesMapper->fetchAll(['sku' => $sku, 'supplierAttributeId' => $mappingId]);
    }

    /**
     * @throws \Exception
     */
    function addProductIdToAttributeRelations(\EcomHelper\Product\Model\Product $product)
    {
        $relations = $this->productSupplierAttributesMapper->fetchAll(['sku' => $product->getSku()]);
        foreach ($relations as $relation) {
            if ($relation['productId'] !== null) {
                continue;
            }
            $relation['productId'] = $product->getId();
            $this->productSupplierAttributesMapper->update($relation);
        }
    }
}