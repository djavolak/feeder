<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Uspon extends Parser
{

    /**
     * @throws MapNotExistingException
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     */
    protected function parseItem($itemData): Product
    {

        $cat1 = (string) $itemData->nadgrupa;
        $cat2 = (string) $itemData->grupa;
        $catString = sprintf('%s-%s', $cat1, $cat2);
        $categoryMap = $this->checkMappingStatus($catString);

        $attributes['attributeSet'] = 1;
        $attributes['attributes']['manufacturer'] = (string) $itemData->proizvodjac;
        $attributes['attributes']['originCountry'] = '';
        $attributes['attributes']['model'] = (string) $itemData->model;
        $images = [];
        foreach ($itemData->slike as $item) {
            $images[] = (string) $item->slika;
        }
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ((int) str_replace(['<', '>', '+'], '', (string) $itemData->kolicina) < 1) {
            $stockStatus = 0;
        }
        if (count($images) === 0 || (int) $itemData->b2bcena === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $inputPrice = $itemData->b2bcena;
        $vat = 1.20;
        if ((int) $itemData->pdv > 0) {
            $vat = '1.' . (int) $itemData->pdv;
        }
        $inputPrice *= $vat;
        $supplierSku = (string) $itemData->sifra;

        $item = new Product(...[
            'productId' => '',
            'title' => (string) $itemData->naziv,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => str_replace('<br>', '', (string) $itemData->opis),
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => 0,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => (string) $itemData->barKod,
//            'attributes' => $attributes,
            'attributes' => ['scrape' => true],
            'ean' => '',
            'sku' => $this->supplier->getSkuPrefix() .'-'. $supplierSku,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => $supplierSku,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);

//        if (false !== strpos($supplierSku, '4540540549')) {
//            var_dump($supplierSku);
//            var_dump((array) $itemData);
//            var_dump($item);
//            die();
//        }
//        if (false !== strpos($supplierSku, '04540540549')) {
//            var_dump($supplierSku);
//            var_dump((array) $itemData);
//            var_dump($item);
//            die();
//        }

        return $item;
    }

    protected function fetchData(): array
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource());
        $xml = new \SimpleXMLElement($response->getBody()->getContents());

        return $xml->xpath('//artikal');
    }
}