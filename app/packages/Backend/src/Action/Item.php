<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Attribute\Mapper\AttributeValues;
use EcomHelper\Attribute\Service\AttributeValue;
use EcomHelper\Category\Repository\TenantCategoryMapperRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Tag\Service\Tag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;
use League\Plates\Engine;

class Item
{
    private $productRepo;

    /**
     * Handler for single item json
     *
     * @param Logger $logger
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(
        ProductRepository $productRepo, Category $category,
        \EcomHelper\Category\Mapper\Category $categoryMapper,
        TenantCategoryMapperRepository $tenantCategoryMapperRepo,
        private AttributeValue $attributeValueService,
        private Tag $tagService
    ) {
        $this->productRepo = $productRepo;
        $this->category = $category;
        $this->categoryMapper = $categoryMapper;
        $this->tenantCategoryMapperRepo = $tenantCategoryMapperRepo;
    }

    /**
     *
     * @param ServerRequestInterface $request request
     * @param ResponseInterface $response response
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $ids = json_decode($request->getBody(), true);
        $tenantId = $request->getAttribute('tenantId');
        $itemsToReturn = [];
        foreach ($ids as $itemId) {
            try {
                $item = $this->productRepo->getById($itemId, true);
                $tags = [];
                $tenantCat = $this->tenantCategoryMapperRepo->fetchAll([
                    'tenantId' => $tenantId,
                    'categoryId' => $item->getCategory()->getId()
                ])[0] ?? null;
                $mappedCategory = $tenantCat?->getRemoteCategory();
                if (!$mappedCategory) {
                    continue;
                }
                $fictionalDiscount = $item->getFictionalDiscountPercentage();
                $attributes = $item->getAttributes();
                foreach ($attributes as $key  => $attribute) {
                    $attributeValueId = $attribute['attributeValueId'];
                    /** @var \EcomHelper\Attribute\Model\AttributeValue $attributeValue */
                    $attributeValue = $this->attributeValueService->getById($attributeValueId);
                    $attributes[$key]['attributeValueImage'] = $attributeValue->getImage()?->getFilename();
                    $attributes[$key]['attributeValueLink'] = $attributeValue->getLink();
                }
                $tagRelation = $this->tagService->getTagsForProductByProductId($item->getId());
                foreach ($tagRelation as $tagData) {
                    $tag = $this->tagService->getById($tagData['tagId']);
                    $tags[] = $tag->toArray();
                }
                $item = $this->removeUnwantedFieldsForWp($item->toArray());
                if ($fictionalDiscount) {
                    //change price to be the discounted price
                    $item['specialPrice'] = $item['price'];
                    //reduce price by discount
                    $item['price'] = $item['price'] / ((100 - $fictionalDiscount) / 100);
                    $item['price'] = ceil($item['price'] / 10) * 10;
                }
                $item['attributes'] = $attributes;
                $item['tags'] = $tags;
                $item['category'] = $mappedCategory?->toArray();
                if ($item['category']) {
                    $item['categories'] = (int) $item['category']['id'];
                    if ($item['category']['level'] !== 1) {
                        if ($item['category']['parent']['level'] === 2) {
                            $item['categories'] .= ',' . (int) $item['category']['parent']['parent']['id'];
                        }
                        $item['categories'] .= ',' . (int) $item['category']['parent']['id'];
                    }
                }
                foreach ($item['images'] as $key => $image) {
                    $item['images'][$key] = '/images' . $image['file'];
                }
                unset($item['category']);
            } catch (\Exception $e) {
                $item['id'] = $itemId;
                $item['error'] = $e->getMessage();
                var_dump($e->getMessage());
            }
            $itemsToReturn[] = $item;
        }
        $response->getBody()->write(json_encode($itemsToReturn));
        $response->getBody()->rewind();
        return $response->withHeader('Content-Type', 'application/json');
    }
    private function removeUnwantedFieldsForWp($item)
    {
        //@todo add rest of the fields that are handled inside plugin container here
        unset($item['salePriceLoop']);
        return $item;
    }
}
