<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Exception\GuzzleException;

class Asbis extends Parser
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
        $cat1 = (string) $itemData->ProductType;
        $cat2 = (string) $itemData->ProductCategory;
        $catString = sprintf('%s-%s', $cat1, $cat2);
        $additionalInfo = $this->itemData[(string)$itemData->ProductCode];
        $categoryMap = $this->checkMappingStatus($catString);
        $images = [];
        foreach ($itemData->Images as $image) {
            $images[] = (string) $image->Image;
        }
        if (!$additionalInfo) {
            throw new \Exception('No additional info for ' . $itemData->ProductCode);
        }
        $quantity = 0; // this supplier does not provide stock info
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 0;
        //Supplier has two statuses ograničeno and po zahtevu, ograničeno means it is in stock
        if ((string)$additionalInfo->AVAIL === 'ograničeno') {
            $stockStatus = 1;
        }
        if (count($images) === 0 || (int) $additionalInfo->MY_PRICE === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        $inputPrice = (int)$additionalInfo->MY_PRICE * $vat;

        return new Product(...[
            'productId' => '',
            'title' => substr((string)$itemData->ProductDescription, 0, strrpos(substr((string)$itemData->ProductDescription, 0, 90), ' ')),
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->ProductDescription,
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
            'ean' => (string) $additionalInfo->EAN,
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->ProductCode,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->ProductCode,
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
        $this->setAdditionalItemInfoInMemory();
        return $xml->xpath('//Product');
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    private function setAdditionalItemInfoInMemory()
    {
        $url = 'https://services.it4profit.com/product/sr/710/PriceAvail.xml?USERNAME=masterkonovo&PASSWORD=f3@NrhuTMNdeug6';
        $response = $this->httpClient->get($url);
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        $itemData = $xml->xpath('//PRICE');
        foreach ($itemData as $item) {
            $this->itemData[(string)$item->WIC] = $item;
        }
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