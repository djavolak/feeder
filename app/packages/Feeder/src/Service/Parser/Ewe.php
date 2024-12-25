<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Ewe extends Parser
{
    protected function parseItem($itemData): Product
    {
        $cat1 = (string) $itemData->category;
        $cat2 = (string) $itemData->subcategory;
        $catString = sprintf('%s-%s', $cat1, $cat2);
        $categoryMap = $this->checkMappingStatus($catString);
        $images = [];
        foreach ($itemData->images as $item) {
            $images[] = (string) $item->image;
        }
        $quantity = str_replace(['+', ',', '<', '>'], '', trim($itemData->stock));
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ($quantity < 1) {
            $stockStatus = 0;
        }
        if (count($images) === 0 || (int) $itemData->price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        if ((int) $itemData->vat > 0) {
            $vat = '1.' . (int) $itemData->vat;
        }
        $inputPrice = $itemData->price_rebate * 123.33;
        $inputPrice *= $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string) $itemData->name,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->description,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => $quantity,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => '',
            'attributes' => ['gpt' => true],
            'ean' => (string) $itemData->ean,
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->id,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->id,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    protected function fetchData(): array
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource());
        $xml = new \SimpleXMLElement($response->getBody()->getContents());

        return $xml->xpath('//product');
    }
}