<?php
namespace EcomHelper\Attribute\Repository;

use EcomHelper\Attribute\Model\Attribute as Model;
use Skeletor\Core\TableView\Repository\TableViewRepository;

class Attribute extends TableViewRepository
{
    const ENTITY = \EcomHelper\Attribute\Entity\Attribute::class;
    const FACTORY = \EcomHelper\Attribute\Factory\AttributeFactory::class;

    public function getSearchableColumns(): array
    {
        return ['name'];
    }

    public function createAttributeValue($attributeId, $attributeValue)
    {
        return $this->attributeValuesMapper->insert([
             'attributeId' => $attributeId,
             'attributeValue' => $attributeValue,
             'imageId' => 0
         ]);
    }

    public function getAttributesWithoutValues()
    {
        return $this->mapper->getAttributesWithoutValues();
    }

    public function getAttributesSorted($attributeIds) {
        return $this->mapper->getAttributesSorted($attributeIds);
    }

    public function fetchAllForSearch($search, $limit = null): array
    {
        $data = [] ;
        foreach ($this->mapper->fetchAllForSearch($search, $limit) as $item) {
            $data[] = $this->make($item)->toArray();
        }
        return $data;
    }
}