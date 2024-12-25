<?php

namespace EcomHelper\Product\Factory;

use EcomHelper\Product\Entity\Supplier;
use EcomHelper\Product\Entity\SupplierFieldsMapping;

class SupplierFieldsMappingFactory
{
    public static function compileEntityForUpdate($data, $em)
    {
        $supplier = $em->getRepository(SupplierFieldsMapping::class)->find($data['id']);
        $supplier->populateFromDto(new \EcomHelper\Product\Model\SupplierFieldsMapping(...$data));

        return $supplier->getId();
    }

    public static function compileEntityForCreate($data, $em)
    {
        $supplier = new SupplierFieldsMapping();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $supplier->populateFromDto(new \EcomHelper\Product\Model\SupplierFieldsMapping(...$data));
        $em->persist($supplier);

        return $supplier->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Product\Model\SupplierFieldsMapping
    {
        if ($itemData['supplier']) {
            $itemData['supplier'] = SupplierFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['supplier']), $em);
        }
        unset($itemData['supplierId']);
        return new \EcomHelper\Product\Model\SupplierFieldsMapping(...$itemData);
    }
}