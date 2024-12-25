<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\AttributeWebScraper;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Irismega extends Parser
{

    protected function parseItem($itemData): Product
    {
        $catString = sprintf('%s-%s', $itemData->grupa, $itemData->podgrupa);
        $categoryMap = $this->checkMappingStatus($catString);

        $images = [$itemData->slika];
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        if (count($images) === 0 || (int) $itemData->mp_cena === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        if ((int) $itemData->porez > 0) {
            $vat = '1.' . (int) $itemData->porez;
        }
        $inputPrice = $itemData->cena_sa_rabatom;
        $inputPrice *= $vat;
        $supplierSku = (string) $itemData->sifra;

        return new Product(...[
            'productId' => $itemData->id ?? '',
            'title' => $itemData->naziv,
            'images' => $images,
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => $itemData->opis,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => ($itemData->stanje_vp > 0) ? 1:0,
            'quantity' => $itemData->stanje_vp,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => $itemData->barkod,
            'attributes' => ['scrape' => true],
            'ean' => $itemData->itm,
            'sku' => $this->supplier->getSkuPrefix() .'-'. $supplierSku,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => $supplierSku,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    protected function fetchData(): array
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource());
        $data = json_decode($response->getBody()->getContents());
        $data = json_decode($data); // dunno is this my covid or som1 else's madness

        return $data;
    }
}