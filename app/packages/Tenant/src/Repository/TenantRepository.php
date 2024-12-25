<?php
namespace EcomHelper\Tenant\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Skeletor\Core\TableView\Repository\TableViewRepository;
use EcomHelper\Tenant\Model\Tenant as Model;
use Skeletor\Tenant\Repository\TenantRepositoryInterface;

class TenantRepository extends TableViewRepository implements TenantRepositoryInterface
{
    const ENTITY = \EcomHelper\Tenant\Entity\Tenant::class;
    const FACTORY = \EcomHelper\Tenant\Model\TenantFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getSearchableColumns(): array
    {
        return ['name'];
    }
}
