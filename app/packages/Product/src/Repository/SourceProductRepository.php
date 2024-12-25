<?php
namespace EcomHelper\Product\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Skeletor\Core\TableView\Repository\TableViewRepository;

class SourceProductRepository extends TableViewRepository
{
    const ENTITY = \EcomHelper\Product\Entity\SourceProduct::class;
    const FACTORY = \EcomHelper\Product\Factory\SourceProductFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getBySupplierId($supplierId, $supplierProductId)
    {
        $items = $this->fetchAll(['supplierId' => $supplierId, 'supplierProductId' => $supplierProductId]);
        if (count($items)) {
            return $items[0];
        }
        return false;
    }

    public function getSearchableColumns(): array
    {
        return ['cat1', 'cat2', 'supplierProductId', 'cat3'];
    }
}
