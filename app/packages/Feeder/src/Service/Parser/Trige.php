<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Trige extends Parser
{

    protected function parseItem($itemData): Product
    {
        $cat1 = (string) $itemData->kategorija1;
        $cat2 = (string) $itemData->kategorija2;
        $cat3 = (string) $itemData->kategorija3;
        $catString = sprintf('%s-%s-%s', $cat1, $cat2, $cat3);
        $categoryMap = $this->checkMappingStatus($catString);
//        $attributes['attributeSet'] = 1;
//        $attributes['attributes']['manufacturer'] = (string) $itemData->proizvodjac;
//        $attributes['attributes']['originCountry'] = '';
        $images = [];
        foreach ($itemData->slike[0] as $url) {
            $url = (string)$url;
            $find = ['(', ')'];
            $replace = [urlencode('('), urlencode(')')];
            $images[] = str_replace($find, $replace, trim($url));
        }
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ((int) $itemData->dostupan !== 1) {
            $stockStatus = 0;
        }
        if (count($images) === 0 || (int) $itemData->vpCena === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
//        $priceWithoutVAT = $itemData->vpcena - ($itemData->vpcena * 0.2);
//        $priceWithoutVAT = $itemData->vpcena / 1.2;
        $inputPrice = (string)$itemData->vpCena;

        $mapWordsFrom = ['Torbica', 'torbica', 'Tempered glass', 'tempered glass', 'Torbica za tablet', 'torbica za tablet'];
        $mapWordsTo = ['Maskica', 'maskica', 'Zaštita za ekran', 'zaštita za ekran', 'Futrola', 'futrola'];
        $title = str_replace($mapWordsFrom, $mapWordsTo, (string) $itemData->naziv);

        return new Product(...[
            'productId' => '',
            'title' => $title,
            'images' => $images,
            'inputPrice' => $inputPrice, // pdv included
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->opis,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => 0,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => (string) $itemData->barkod,
            'attributes' => [],
            'ean' => '',
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->sifra,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->sifra,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    protected function fetchData(): array
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource());
        file_put_contents(DATA_PATH . '/feed/trige.zip', $response->getBody()->getContents());
        $zip = new \ZipArchive();
        $res = $zip->open(DATA_PATH . '/feed/trige.zip');
        if ($res !== true) {
            die('could not unzip.');
        }
        $zip->extractTo(DATA_PATH . '/feed/');
        $zip->close();
        libxml_use_internal_errors(true);
        $xml = simplexml_load_file(DATA_PATH . '/feed/3gKoNovo.xml');
        $items = [];
        foreach ($xml as $itemData) {
            $string = $itemData->asXML();
            $string = str_replace(['<![CDATA[', ']]>'], '', $string);
            $string = str_replace('&', '&amp;', $string);
            $string = str_replace('&nbsp;', '&#160;', $string);
            $xmlString = simplexml_load_string($string);
            if(!$xmlString) {
                $this->logger->error('Error in parsing 3g item', ['error' => libxml_get_errors(), 'xmlString' => $string]);
                continue;
            }
            $items[] = $xmlString;
        }
        if (count($items) === 0) {
            throw new \Exception('No items found in feed.');
        }
        return $items;
    }
}