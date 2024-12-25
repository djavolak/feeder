<?php
namespace EcomHelper\Product\Service;

use EcomHelper\Product\Repository\SourceProductRepository as Repository;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\Session;
use EcomHelper\Product\Filter\Supplier as SupplierFilter;

class SourceProduct extends TableView
{
    /**
     * @param Repository $clientRepo
     * @param Session $user
     * @param Logger $logger
     * @param SupplierFilter $filter
     */
    public function __construct(
        Repository $clientRepo, Session $user, Logger $logger, private Supplier $supplier
    ) {
        parent::__construct($clientRepo, $user, $logger);
    }

    public function compileTableColumns()
    {
        $suppliers = $this->supplier->getEntities();
        $supplierFilter = [];
        foreach ($suppliers as $supplier) {
            $supplierFilter[$supplier->getId()] = $supplier->getName();
        }
        return [
            ['name' => 'cat1', 'label' => 'Cat1'],
            ['name' => 'cat2', 'label' => 'Cat2'],
            ['name' => 'cat3', 'label' => 'Cat3'],
            ['name' => 'supplierProductId', 'label' => 'Supplier product id'],
//            ['name' => 'productData', 'label' => 'Product data'],
            ['name' => 'supplier', 'label' => 'Supplier', 'filterData' => $supplierFilter],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at']
        ];
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $product) {
            $itemData = [
                'id' => $product->getId(),
                'cat1' => $product->getCat1() ?? '',
                'cat2' => $product->getCat2() ?? '',
                'cat3' => $product->getCat3() ?? '',
                'supplierProductId' => $product->getSupplierProductId(),
                'supplier' => $product->getSupplier()->getName(),
                'createdAt' => $product->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $product->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $product->getId(),
            ];
        }
        return $items;
    }
}