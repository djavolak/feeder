<?php
namespace EcomHelper\Feeder\Service\Parser;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Dsc extends AbstractParser
{
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
        foreach ((array) $itemData->slike->slika as $item) {
            $images[] = $item;
        }
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ((int) str_replace('+', '', (string) $itemData->kolicina) < 1) {
            $stockStatus = 0;
        }
        if (count($images) === 0 || (int) $itemData->cena === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }

        $sifra = (string) $itemData->sifra;
//        if ($sifra !== '18497') {
//            throw new CategoryIgnoredException();
//        }
        $vat = 1.20;
        if ((int) $itemData->pdv > 0) {
            $vat = '1.' . (int) $itemData->pdv;
        }
        $inputPrice = $itemData->cena;
        $inputPrice *= $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string) $itemData->naziv,
            'images' => $images,
            'inputPrice' => $inputPrice,
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
//            'attributes' => $attributes,
            'attributes' => '',
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
        $xml = new \SimpleXMLElement($response->getBody()->getContents());

        return $xml->xpath('//artikal');
    }
}