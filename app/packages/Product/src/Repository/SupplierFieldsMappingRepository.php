<?php
namespace EcomHelper\Product\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Skeletor\Core\TableView\Repository\TableViewRepository;

class SupplierFieldsMappingRepository extends TableViewRepository
{
    const ENTITY = \EcomHelper\Product\Entity\SupplierFieldsMapping::class;
    const FACTORY = \EcomHelper\Product\Factory\SupplierFieldsMappingFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getSearchableColumns(): array
    {
        return [];
    }
}
