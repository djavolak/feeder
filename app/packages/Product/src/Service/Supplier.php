<?php
namespace EcomHelper\Product\Service;

use EcomHelper\Product\Repository\SupplierRepository;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\Session;
use EcomHelper\Product\Filter\Supplier as SupplierFilter;

class Supplier extends TableView
{
    /**
     * @param SupplierRepository $clientRepo
     * @param Session $user
     * @param Logger $logger
     * @param SupplierFilter $filter
     */
    public function __construct(
        SupplierRepository $clientRepo, Session $user, Logger $logger, SupplierFilter $filter
    ) {
        parent::__construct($clientRepo, $user, $logger, $filter);
    }

    public function getEntityData($id)
    {
        $supplier = $this->repo->getById($id);

        return [
            'id' => $supplier->getId(),
            'name' => $supplier->getName(),
            'code' => $supplier->getCode(),
            'skuPrefix' => $supplier->getSkuPrefix(),
            'status' => $supplier->getStatus(),
            'feedSource' => $supplier->getFeedSource(),
            'feedUsername' => $supplier->getFeedUsername(),
            'feedPassword' => $supplier->getFeedPassword(),
            'sourceType' => $supplier->getSourceType(),
            'createdAt' => $supplier->getUpdatedAt()->format('m.d.Y'),
            'updatedAt' => $supplier->getCreatedAt()->format('m.d.Y'),
        ];
    }

    public function compileTableColumns()
    {
        $columnDefinitions = [
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'code', 'label' => 'Code'],
            ['name' => 'skuPrefix', 'label' => 'Sku Prefix'],
            ['name' => 'status', 'label' => 'Status'],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at']
        ];

        return $columnDefinitions;
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $supplier) {
            $itemData = [
                'id' => $supplier->getId(),
                'name' =>  [
                    'value' => $supplier->getName(),
                    'editColumn' => true,
                ],
                'code' => $supplier->getCode(),
                'skuPrefix' => $supplier->getSkuPrefix(),
                'status' => $supplier->getStatus(),
                'createdAt' => $supplier->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $supplier->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $supplier->getId(),
            ];
        }
        return $items;
    }
}