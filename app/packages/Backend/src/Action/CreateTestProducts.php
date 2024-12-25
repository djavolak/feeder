<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Product\Mapper\Image;
use EcomHelper\Product\Mapper\Product;
use EcomHelper\Product\Repository\ProductRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\NotFoundException;

class CreateTestProducts
{
    public function __construct(private ProductRepository $productRepository, private Product $productMapper,
        private Image $imageMapper, private Attribute $attributeService)
    {
    }

    /**
     * @throws NotFoundException
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $itemsToCreate = 500;
        for ($i = 0; $i < $itemsToCreate; $i++) {
            $idToCopy = $request->getAttribute('supplier');
            $dummy = $this->productRepository->getById($idToCopy)->toArray();
            $catId = 681; // test cat
            $dummy['category'] = $catId;

            $dummy['productId'] = null;
            unset($dummy['id']);
            $dummy['sku'] = 'test-' . $i;
            $dummy['status'] = \EcomHelper\Product\Model\Product::STATUS_DRAFT;

            $dummy['title'] .= '-copy';
            $dummy['slug'] .= '-copy';
            unset($dummy['supplier']);
            $dummy['supplierId'] = 1; //test supplier

            unset($dummy['createdAt'], $dummy['updatedAt']);

            // Attributes
            $attributes = $dummy['attributes'];
            unset($dummy['attributes']);

            $dummyImages = $dummy['images'];

            $dummy['images'] = '';
            /** @depreciated* */

            $productId = $this->productMapper->insert($dummy);
            if (count($dummyImages) > 0) {
                foreach ($dummyImages as $image) {
                    $imageData = [
                        'imageId' => $image['imageId'],
                        'productId' => $productId,
                        'file' => $image['file'],
                        'main' => $image['isMain'],
                        'sort' => 0,
                    ];
                    $this->imageMapper->insert($imageData);
                }
            }

            $dataAttributes = [];
            foreach ($attributes as $attribute) {
                $dataAttributes[$attribute['attributeId']][]['name'] = $attribute['attributeName'];
                $dataAttributes[$attribute['attributeId']][]['value'] = $attribute['attributeValue'] . '#' . $attribute['attributeValueId'];
            }
            $this->attributeService->saveAttributesForProduct($productId, $dataAttributes);
        }
        return $response->withStatus(200);
    }
}