<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Exception\GuzzleException;

class Beli extends Parser
{
    private $itemData = [];
    /**
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     * @throws \RedisException
     * @throws MapNotExistingException
     * @throws \Exception
     */
    protected function parseItem($itemData): Product
    {
        $cat1 = (string) $itemData->product_category;
        $cat2 = '';
        $catString = sprintf('%s-%s', $cat1, $cat2);
        $categoryMap = $this->checkMappingStatus($catString);
        $images = [];
        foreach ($itemData->product_image_urls as $image) {
            $images[] = (string) $image->product_image_url;
        }
        $quantity = 0; // this supplier does not provide stock info
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 0;
        //Supplier has two statuses ograničeno and po zahtevu, ograničeno means it is in stock
        if ((string)$itemData->product_status === '1') {
            $stockStatus = 1;
        }
        if (count($images) === 0 || (int) $itemData->product_price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        $inputPrice = (int)$itemData->product_price * $vat;



        return new Product(...[
            'productId' => '',
            'title' => (string)$itemData->ProductName,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->product_description,
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
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->sku,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->sku,
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
        return $xml->xpath('//product');
    }

    protected function checkMappingStatus($catString)
    {
        $cats = explode('-', $catString);
        $mapFilter = [
            'source1' => $cats[0],
            'source2' => $cats[1] ?? '',
            'supplierId' => $this->supplier->getId()
        ];
        $categoryMap = $this->catMapperRepo->fetchAll($mapFilter);
        $catString = $cats[0] . '#' . $cats[1];
        if (!count($categoryMap)) {
            // create new unmapped category map
            throw new MapNotExistingException($catString);
        }
        if (!$categoryMap[0]->getCategory()) {
            throw new CategoryNotMappedException($catString);
        }
        if ($categoryMap[0]->getCategory() instanceof IgnoredCategory) {
            // skip product parse
            throw new CategoryIgnoredException($catString);
        }
        return $categoryMap[0];
    }
}