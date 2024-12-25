<?php

namespace EcomHelper\Attribute\Service;

use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Repository\AttributeValues;
use EcomHelper\Feeder\Mapper\SupplierAttributeLocalAttribute;
use EcomHelper\Feeder\Repository\CategoryParsingRule;
use Skeletor\Image\Service\Image;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\TableView\Service\TableView;
use Skeletor\User\Service\Session;

class AttributeValue extends TableView
{
    public function __construct(
        AttributeValues $repo, Session $user, Logger $logger, private Attribute $attributeService, private Image $imageService,
//        private Product $productService,
//        private ProductAttributeValues $productAttributeValuesMapper,
//        private CategoryParsingRule $categoryParsingRuleRepo,
//        private SupplierAttributeLocalAttribute $supplierAttributeMappingRepo
    )
    {
        parent::__construct($repo, $user, $logger);
    }

    function compileTableColumns()

    {
        $attributes = $this->attributeService->getEntities();
        $options = [];
        foreach ($attributes as $attribute) {
            $options[$attribute->getId()] = $attribute->getName();
        }

        return [
            ['name' => 'attributeValue', 'label' => 'Attribute value'],
            ['name' => 'image', 'label' => 'Image'],
            ['name' => 'attributeId', 'label' => 'Attribute', 'filterData' => $options],
            ['name' => 'updatedAt', 'label' => 'Created At'],
            ['name' => 'createdAt', 'label' => 'Updated At'],
        ];
    }
    public function prepareEntities($entities)
    {
        $items = [];
        /** @var \EcomHelper\Attribute\Model\AttributeValue $attributeValue */
        foreach ($entities as $attributeValue) {
            $attribute = $this->attributeService->getById($attributeValue->getAttribute()->getid());
            $imageHtml = '';
            if ($attributeValue->getImage()) {
                $image = $attributeValue->getImage();
                $imageHtml = sprintf('<img width="50px" src="%s" style="max-width: 100px; max-height: 100px" alt="%s">',
                   '/images/' . $image->getFilename(), $image->getAlt());
            }
            $itemData = [
                'id' => $attributeValue->getId(),
                'attributeId' => $attribute->getName(),
                'attributeValue' => [
                    'value' => $attributeValue->getValue(),
                    'editColumn' => true,
                ],
                'image' => $imageHtml,
                'createdAt' => $attributeValue->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $attributeValue->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $attributeValue->getId(),
            ];
        }
        return $items;
    }
}