<?php
namespace EcomHelper\Attribute\Service;

use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\TableView\Service\TableView;
use Skeletor\User\Service\Session;

class AttributeGroup extends TableView
{
    public function __construct(
      \EcomHelper\Attribute\Repository\AttributeGroup $repo, Session $user, Logger $logger,
        \EcomHelper\Attribute\Filter\AttributeGroup $filter
    )
    {
        parent::__construct($repo, $user, $logger, $filter);
    }

    public function compileTableColumns()
    {
        $columnDefinitions = [
            ['name' => 'name', 'label' => 'Group Name'],
            ['name' => 'category', 'label' => 'Category'],
        ];

        return $columnDefinitions;
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $attributeGroup) {
            $itemData = [
                'name' => [
                    'value' => $attributeGroup->getName(),
                    'editColumn' => true,
                ],
                'category' => (string) $attributeGroup->getCategoryId(),
                'createdAt' => $attributeGroup->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $attributeGroup->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $attributeGroup->getId(),
            ];
        }
        return $items;
    }
}