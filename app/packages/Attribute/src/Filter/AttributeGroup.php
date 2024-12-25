<?php
namespace EcomHelper\Attribute\Filter;

use Skeletor\Core\Filter\FilterInterface;

class AttributeGroup implements FilterInterface
{
    public function getErrors()
    {
        return [];
    }

    public function filter(array $postData): array
    {
        $data = [
            'id' => $postData['id'],
            'name' => $postData['name'],
            'categoryId' => $postData['categoryId'] ?? null,
            'attributes' => $postData['attributes'] ?? []
        ];

        return $data;
    }
}