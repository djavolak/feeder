<?php
namespace EcomHelper\Feeder\Service;

use EcomHelper\Feeder\Service\Importer\Importer;
use EcomHelper\Product\Model\Product;
use EcomHelper\Feeder\Model\Product as ParsedProduct;
use Skeletor\Mapper\NotFoundException;

class Updater extends Importer
{
    protected function getCacheKey()
    {
        return Parser::CACHE_KEY_UPDATE;
    }

    protected function getLogTemplate()
    {
        return 'Updated %d out of %d items.';
    }

    /**
     * @throws NotFoundException
     * @throws \Exception
     */
    protected function processItem(ParsedProduct $parsedProduct): bool
    {
        $localProduct = $this->productRepo->getById($parsedProduct->getId());
        //needed if there is a need to update attribute mapping to old products
//        $this->getLocalAttributesAndCreateMappingIfNeeded($parsedProduct);
//        $this->supplierAttributeMappingRepo->addProductIdToAttributeRelations($localProduct);
        $addedImages = false;
        if (count($localProduct->getImages()) === 0 && count($parsedProduct->getImages()) > 0) {
            foreach ($parsedProduct->getImages() as $key => $imageUrl) {
                try {
                    if ($imageUrl !== '') {
                        $path = $this->imageFetcher->fetch($imageUrl);
                        if ($path === '') {
                            continue;
                        }
                        $image = $this->image->createFromPath($path, (int)$parsedProduct->getSupplierId());
                        $imageData = [
                            'imageId' => $image->getid(),
                            'productId' => $localProduct->getId(),
                            'file' => '',
                            'main' => ($key === 0) ? 1 : 0,
                            'sort' => 0,
                        ];
                        $this->imageMapper->insert($imageData);
                        $addedImages = true;
                    }
                }catch (\Exception $e) {
                }
            }
        }
        $delta = ['status' => $localProduct->getStatus()];
        // update status
        if ($localProduct->getIgnoreStatusChange() !== 1) {
            $delta = $this->updateStatus($parsedProduct, $localProduct, $addedImages);
        }

        if ($localProduct->getMappingId() === null && $parsedProduct->getMappingId() !== null) {
            $delta['mappingId'] = $parsedProduct->getMappingId();
        }
        if(strlen($localProduct->getDescription()) < 20 && $parsedProduct->getDescription() !== '') {
            $delta['description'] = $parsedProduct->getDescription();
        }


        // update prices
        $delta = array_merge($delta, $this->updatePrices($parsedProduct, $localProduct));

        // tmp, for testing
        $delta = array_merge($delta, $this->updateName($parsedProduct, $localProduct));

//        $delta = array_merge($delta, $this->updateCategory($parsedProduct, $localProduct));

        // tmp add supplierCategory
//        if (!$localProduct->getSupplierCategory()) {
//            $delta['supplierCategory'] = $parsedProduct->getSupplierCategory();
//        }

        // debug
//        $parsedData = $parsedProduct->toArray();
        if (!empty($delta)) {
            $delta['productId'] = (int) $localProduct->getId();
            $delta['sku'] = $localProduct->getSku();
            $delta['slug'] = $localProduct->getSlug();
            $this->productRepo->update($delta, true);
            return true;
        }
        return false;


    }

    private function updatePrices(ParsedProduct $parsedProduct, Product $localProduct)
    {
        $data = [];
        if ($parsedProduct->getPrice() !== $localProduct->getPrice()) {
            $data['price'] = $parsedProduct->getPrice();
        }
        //ignoring special price for now
//        if ($parsedProduct->getSpecialPrice() !== $localProduct->getSpecialPrice()) {
//            $data['specialPrice'] = $parsedProduct->getSpecialPrice();
//        }
        $data['inputPrice'] = $parsedProduct->getInputPrice();

        return $data;
    }

    private function updateStatus(ParsedProduct $parsedProduct, Product $localProduct, $addedImages = false)
    {
        $data = [];
        if ($localProduct->getStatus() === Product::STATUS_PRIVATE && $parsedProduct->getStatus() === Product::STATUS_DRAFT) {
            $data['status'] = $parsedProduct->getStatus();
        }
        if (($localProduct->getStatus() === Product::STATUS_DRAFT && $addedImages && $parsedProduct->getPrice() != 0) ||
            ($localProduct->getStatus() === Product::STATUS_DRAFT && ($parsedProduct->getPrice() != 0 && $localProduct->getImages() !== []))) {
            $data['status'] = Product::STATUS_PRIVATE;
        }
        if ($parsedProduct->getStockStatus() !== $localProduct->getStockStatus()) {
            $data['stockStatus'] = $parsedProduct->getStockStatus();
        }

        if ($localProduct->getStatus() === Product::STATUS_PUBLISH) {
            $data['status'] = $localProduct->getStatus();
            if ($parsedProduct->getPrice() == 0) {
                $data['status'] = Product::STATUS_DRAFT;
            }
        }
        if($localProduct->getStatus() === Product::STATUS_SOURCE_REMOVED) {
            $data['status'] = Product::STATUS_DRAFT;
            if (($parsedProduct->getPrice() != 0 && $addedImages) || ($localProduct->getImages() !== [] && $parsedProduct->getPrice() != 0)) {
                $data['status'] = Product::STATUS_PRIVATE;
            }
        }

        if (!$addedImages) {
            // Edge case
            if($localProduct->getStatus() !== Product::STATUS_SOURCE_REMOVED && count($localProduct->getImages()) === 0) {
                $data['status'] = Product::STATUS_DRAFT;
            }
        }


        $specialPriceLocal = $localProduct->getSpecialPrice();
        $profitLocal = ($specialPriceLocal ?: $localProduct->getPrice()) - $localProduct->getInputPrice();

        if($profitLocal <= 0 && $localProduct->getStatus() !== Product::STATUS_SOURCE_REMOVED) {
            $data['status'] = Product::STATUS_DRAFT;
        }
        return $data;
    }

    private function updateName(ParsedProduct $parsedProduct, Product $localProduct)
    {
        $data = [];
        if ($parsedProduct->getTitle() !== $localProduct->getTitle()) {
            $data['title'] = $parsedProduct->getTitle();
        }

        return $data;
    }

    private function setDelta()
    {

    }

    private function updateCategory(ParsedProduct $parsedProduct, Product $localProduct): array
    {
        $data = [];
        if ($parsedProduct->getCategories() !== $localProduct->getCategory()?->getId())
        {
            $data['category'] = $parsedProduct->getCategories();
        }
        return $data;
    }
}