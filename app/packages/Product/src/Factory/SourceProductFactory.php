<?php

namespace EcomHelper\Product\Factory;

use EcomHelper\Product\Entity\Product;
use EcomHelper\Product\Entity\SourceProduct;
use EcomHelper\Product\Entity\Supplier;

class SourceProductFactory
{
    public static function compileEntityForUpdate($data, $entityManager)
    {
        $product = $entityManager->getRepository(SourceProduct::class)->find($data['id']);
        $supplier = $entityManager->getRepository(Supplier::class)->find($data['supplierId']);
        unset($data['supplierId']);
        $product->populateFromDto(new \EcomHelper\Product\Model\SourceProduct(...$data));
        $product->setSupplier($supplier);

        return $product->getId();
    }

    public static function compileEntityForCreate($data, $entityManager)
    {
        $product = new SourceProduct();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $supplier = $entityManager->getRepository(Supplier::class)->find($data['supplierId']);
        unset($data['supplierId']);
        $product->populateFromDto(new \EcomHelper\Product\Model\SourceProduct(...$data));
        $product->setSupplier($supplier);
        $entityManager->persist($product);

        return $product->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Product\Model\SourceProduct
    {
        if (isset($itemData['supplier']) && $itemData['supplier']) {
            $itemData['supplier'] = SupplierFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['supplier']), $em);
        } else {
            $itemData['supplier'] = null;
        }
        unset($itemData['supplierId']);

        return new \EcomHelper\Product\Model\SourceProduct(...$itemData);
    }

}