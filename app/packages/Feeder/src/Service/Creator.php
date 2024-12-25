<?php

namespace EcomHelper\Feeder\Service;

use EcomHelper\Feeder\Service\Importer\Importer;
use EcomHelper\Feeder\Model\Product;
use EcomHelper\Product\Service\UrlHelper;

class Creator extends Importer
{
    protected function getCacheKey()
    {
        return Parser::CACHE_KEY_CREATE;
    }

    protected function getLogTemplate()
    {
        return 'Created %d out of %d items.';
    }

    /**
     * @throws \Exception
     */
    protected function processItem(Product $product): bool
    {
        $productData = $product->toArray();
        $productData['attributes'] = $product->getAttributes();
        if (is_array($productData['attributes']) && count($productData['attributes']) > 0) {
            $productData['attributes'] = $this->getLocalAttributesAndCreateMappingIfNeeded($product);
        }
        $images = $productData['images'];
        unset($productData['images']);
        $productData['newAttributes'] = [];
        if (!is_array($productData['attributes'])) {
            $productData['attributes'] = [];
        }
        $productData['ignoreStatusChange'] = 0;
        $productData['slug'] = UrlHelper::slugify($productData['title']);

        if ($productData['specialPriceFrom'] === '') {
            unset($productData['specialPriceFrom']);
        }
        if ($productData['specialPriceTo'] === '') {
            unset($productData['specialPriceTo']);
        }
        // @TODO dont import if outofstock, exception, stats
        if (!$productData['stockStatus']) {
            return false;
        }
        // @TODO validate data

        $productData['productId'] = $productData['id'];
        $productData['category'] = $productData['categories'];
        unset($productData['id']);
        unset($productData['categories']);

        // @TODO apply price rules

        // @TODO notice when this happens
        if (count($images) === 0 || (int) $product->getPrice() === 0) {
            $productData['status'] = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $productData['fictionalDiscountPercentage'] = 0;
        $globalParsingRules = $this->parsingRulesService->getParsingRules(0, $productData['category']);
        $parsingRulesSupplier = $this->parsingRulesService->getParsingRules($productData['supplierId'], $productData['category']);
        //do global rules first
        foreach ($globalParsingRules as $rule) {
//            $productHash = md5(json_encode($productData));
            $action = $rule->getAction();
            global $container;
            $productData = ($container->get($action))($productData, $rule);
//            $newHash = md5(json_encode($productData));
//            if ($newHash !== $productHash) {
//                $this->logger->info('Global rule changed product data', ['rule' => $rule->getId(), 'product' => $productData['sku']]);
//            }
        }

        //then do supplier specific rules
        foreach ($parsingRulesSupplier as $rule) {
            $action = $rule->getAction();
//            $productHash = md5(json_encode($productData));
            global $container;
            $productData = ($container->get($action))($productData, $rule);
//            $newHash = md5(json_encode($productData));
//            if ($newHash !== $productHash) {
//                $this->logger->info('Supplier rule changed product data', ['rule' => $rule->getId(), 'product' => $productData['sku']]);
//            }
        }
        $product = $this->productRepo->create($productData);
        if(count($productData['attributes']) > 0) {
            $this->generateNewShortDesc($product);
        }
        /**cause attribute mapping is saved with sku and not id, here after product is created we need to update
         * the mapping and add id
       **/
        $this->supplierAttributeMappingRepo->addProductIdToAttributeRelations($product);
        $goodImages = 0;
        foreach ($images as $key => $imageUrl) {
            try {
                if ($imageUrl !== '') {
                    $path = $this->imageFetcher->fetch($imageUrl);
                    if ($path === '') {
                        continue;
                    }
                    $image = $this->image->createFromPath($path, $product->getSupplier()->getId());
                    $imageData = [
                        'imageId' => $image->getid(),
                        'productId' => $product->getId(),
                        'file' => '',
                        'main' => ($key === 0) ? 1 : 0,
                        'sort' => 0,
                    ];
                    $this->imageMapper->insert($imageData);
                    $goodImages++;
                }
            } catch(\Exception $e) {

            }
        }
        if($goodImages === 0) {
            $this->productRepo->updateField('status', \EcomHelper\Product\Model\Product::STATUS_DRAFT, $product->getId());
        }

        return true;
    }
}