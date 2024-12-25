<?php

namespace EcomHelper\Product\Factory;

class SupplierFactory
{
    public static function compileEntityForUpdate($data, $entityManager)
    {
        $supplier = $entityManager->getRepository(\EcomHelper\Product\Entity\Supplier::class)->find($data['id']);
        $supplier->populateFromDto(new \EcomHelper\Product\Model\Supplier(...$data));

        return $supplier->getId();
    }

    public static function compileEntityForCreate($data, $entityManager)
    {
        $supplier = new \EcomHelper\Product\Entity\Supplier();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $supplier->populateFromDto(new \EcomHelper\Product\Model\Supplier(...$data));
        $entityManager->persist($supplier);

        return $supplier->getId();
    }

    public static function make($itemData, $entityManager): \EcomHelper\Product\Model\Supplier
    {
        return new \EcomHelper\Product\Model\Supplier(...$itemData);
    }
}