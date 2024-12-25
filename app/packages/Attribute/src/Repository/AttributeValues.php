<?php
namespace EcomHelper\Attribute\Repository;

use Skeletor\Core\TableView\Repository\TableViewRepository;

class AttributeValues extends TableViewRepository
{
    const ENTITY = \EcomHelper\Attribute\Entity\AttributeValue::class;
    const FACTORY = \EcomHelper\Attribute\Factory\AttributeValueFactory::class;

    function getSearchableColumns(): array
    {
        return ['value'];
    }
}