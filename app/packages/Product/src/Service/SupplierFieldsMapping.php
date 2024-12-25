<?php
namespace EcomHelper\Product\Service;

use EcomHelper\Product\Repository\SupplierFieldsMappingRepository as Repository;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\Session;
use EcomHelper\Product\Filter\Supplier as SupplierFilter;

class SupplierFieldsMapping extends TableView
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

//    public function getEntityData($id)
//    {
//        $supplier = $this->repo->getById($id);
//
//        return [
//            'id' => $supplier->getId(),
//            'name' => $supplier->getName(),
//            'createdAt' => $supplier->getUpdatedAt()->format('m.d.Y'),
//            'updatedAt' => $supplier->getCreatedAt()->format('m.d.Y'),
//        ];
//    }

    public function compileTableColumns()
    {
        $suppliers = $this->supplier->getEntities();
        $supplierFilter = [];
        foreach ($suppliers as $supplier) {
            $supplierFilter[$supplier->getId()] = $supplier->getName();
        }
        return [
            ['name' => 'sourceFieldName', 'label' => 'Source field name'],
            ['name' => 'productFieldName', 'label' => 'Product field name'],
            ['name' => 'supplier', 'label' => 'Supplier', 'filterData' => $supplierFilter],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at']
        ];
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $mapping) {
            $itemData = [
                'id' => $mapping->getId(),
                'sourceFieldName' =>  [
                    'value' => $mapping->getSourceFieldName(),
                    'editColumn' => true,
                ],
                'productFieldName' => $mapping->getProductFieldName() == 0 ? 'Ignored' : $mapping->getProductFieldName(),
                'supplier' => $mapping->getSupplier()?->getName(),
                'createdAt' => $mapping->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $mapping->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $mapping->getId(),
            ];
        }
        return $items;
    }
}