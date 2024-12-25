<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Exception\GuzzleException;

class TelitPower extends Parser
{
    /**
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     * @throws \RedisException
     * @throws MapNotExistingException
     * @throws \Exception
     */
    protected function parseItem($itemData): Product
    {
        $cats = explode('>', (string) $itemData->Category_Name);
        $catString = $cats[0];
        if (count($cats) > 1) {
            $catString .= '-' . $cats[1];
        }
        if (count($cats) > 2) {
            $catString .= '-' . $cats[2];
        }
        //if there are more than 3 categories, we skip first one and use the rest
        if (count($cats) > 3) {
            $catString = $cats[count($cats) - 3] . '-' . $cats[count($cats) - 2] . '-' . $cats[count($cats)-1];
        }
        $categoryMap = $this->checkMappingStatus($catString);
        $images [] = (string)$itemData->Main_Image;
        foreach (explode(';', (string)$itemData->Product_Sub_Images) as $image) {
            $images[] = $image;
        }
        $quantity = (int)$itemData->Quantity;
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 0;
        if ($quantity > 0) {
            $stockStatus = 1;
        }
        if (count($images) === 0 || (int) $itemData->Price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        $inputPrice = (int)$itemData->Price * $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string)$itemData->Product_Name,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->Product_Description,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => $quantity,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => '',
            'attributes' => '',
            'ean' => '',
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->SKU,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->SKU,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    protected function fetchData(): array
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource());
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        return $xml->xpath('//Product');
    }
}