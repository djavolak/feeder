<?php

namespace EcomHelper\Category\Factory;

use EcomHelper\Product\Entity\Supplier;
use EcomHelper\Product\Factory\SupplierFactory;
use EcomHelper\Tenant\Entity\Tenant;
use EcomHelper\Tenant\Model\TenantFactory;
use Skeletor\Image\Entity\Image;
use function EcomHelper\Product\Factory\SupplierFieldsMappingFactory;

class CategoryMapperFactory
{
    public static function compileEntityForUpdate($data, $em)
    {
        $mapper = $em->getRepository(\EcomHelper\Category\Entity\CategoryMapper::class)->find($data['id']);
        if ($data['supplierId']) {
            $supplier = $em->getRepository(Supplier::class)->find($data['supplierId']);
            $mapper->setSupplier($supplier);
        }
        if ($data['categoryId'] && $data['categoryId'] != '-1' && $data['categoryId'] != 0) {
            $category = $em->getRepository(\EcomHelper\Category\Entity\Category::class)->find($data['categoryId']);
            $mapper->setCategory($category);
        }
        unset($data['supplierId']);
        unset($data['categoryId']);
        $categoryDto = new \EcomHelper\Category\Model\CategoryMapper(...$data);
        $mapper->populateFromDto($categoryDto);
        $em->persist($mapper);

        return $mapper->getId();
    }

    public static function compileEntityForCreate($data, $em)
    {
        $mapper = new \EcomHelper\Category\Entity\CategoryMapper();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        if ($data['supplierId']) {
            $supplier = $em->getRepository(Supplier::class)->find($data['supplierId']);
            $mapper->setSupplier($supplier);
        }
        if ($data['categoryId'] && $data['categoryId'] != '-1' && $data['categoryId'] != 0) {
            $category = $em->getRepository(\EcomHelper\Category\Entity\Category::class)->find($data['categoryId']);
            $mapper->setCategory($category);
        }
        unset($data['supplierId']);
        unset($data['categoryId']);
        $mapper->populateFromDto(new \EcomHelper\Category\Model\CategoryMapper(...$data));
        $em->persist($mapper);

        return $mapper->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Category\Model\CategoryMapper
    {
        if (isset($itemData['supplierId']) && $itemData['supplierId']) {
            $itemData['supplier'] = SupplierFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['supplier']), $em);
        } else {
            $itemData['supplier'] = null;
        }
        if (isset($itemData['categoryId']) && $itemData['categoryId']) {
            $itemData['category'] = CategoryFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['category']), $em);
        } else {
            $itemData['category'] = null;
        }
        unset($itemData['supplierId']);
        unset($itemData['categoryId']);

        return new \EcomHelper\Category\Model\CategoryMapper(...$itemData);
    }
}