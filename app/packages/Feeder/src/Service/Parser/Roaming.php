<?php

namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use Symfony\Component\DomCrawler\Crawler;

class Roaming extends Parser
{
    /**
     * @throws MapNotExistingException
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     */
    protected function parseItem($itemData): Product
    {
        $cat1 = (string)$itemData->nadgrupa;
        $cat2 = (string)$itemData->grupa;
        $catString = sprintf('%s-%s', $cat1, $cat2);
        $categoryMap = $this->checkMappingStatus($catString);
        $images = [];
        foreach ($itemData->slike->slika as $item) {
            $images[] = (string)$item;
        }
        $quantity = str_replace(['+', ',', '<', '>'], '', trim($itemData->kolicina));
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ($quantity < 1) {
            $stockStatus = 0;
        }
        if (count($images) === 0 || (int)$itemData->cena === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        if ((int)$itemData->pdv > 0) {
            $vat = '1.' . (int)$itemData->pdv;
        }
        $inputPrice = $itemData->cena;
        $inputPrice *= $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string)$itemData->naziv,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string)$itemData->opis,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => $quantity,
            'categories' => (int)$categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => (string)$itemData->barkod,
//            'attributes' => $attributes,
            'attributes' => ['scrape' => true],
            'ean' => '',
            'sku' => $this->supplier->getSkuPrefix() . '-' . $itemData->sifra,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string)$itemData->sifra,
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