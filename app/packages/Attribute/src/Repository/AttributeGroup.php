<?php
namespace EcomHelper\Attribute\Repository;

use Skeletor\Core\TableView\Repository\TableViewRepository;

class AttributeGroup extends TableViewRepository
{
    const ENTITY = \EcomHelper\Attribute\Entity\Group::class;
    const FACTORY = \EcomHelper\Attribute\Factory\GroupFactory::class;

    public function getSearchableColumns(): array
    {
        return [];
    }
}