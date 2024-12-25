<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Client;

class Gembird extends Parser
{

    protected function parseItem($itemData): Product
    {
        $cat1 = (string)$itemData->category;
        $categoryMap = $this->catMapperRepo->fetchAll(
            [
                'source1' => $cat1,
                'source2' => '',
                'source3' => '',
                'supplierId' => $this->supplier->getId()
            ]
        );
        if (!count($categoryMap)) {
            // create new unmapped category map
            throw new MapNotExistingException($cat1);
        }
        if (!$categoryMap[0]->getCategory()) {
            throw new CategoryNotMappedException($cat1);
        }
        if ($categoryMap[0]->getCategory() instanceof IgnoredCategory) {
            // skip product parse
            throw new CategoryIgnoredException($cat1);
        }
        $images = [];
        foreach ($itemData->images as $item) {
            $images[] = (string) $item->image;
        }
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 0;
        if ((int) $itemData->stock > 0) {
            $stockStatus = 1;
        }
        if (count($images) === 0 || (int) $itemData->price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }

        $vat = 1.20;
        if ((int) $itemData->vat > 0) {
            $vat = '1.' . (int) $itemData->vat;
        }
        $inputPrice = (int)$itemData->price * $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string) $itemData->name,
            'images' => $images,
            'inputPrice' => (int) $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => (string) $itemData->specifications->attribute_group->attribute,
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => 0,
            'categories' => (int) $categoryMap[0]->getCategory()->getId(),
            'weight' => 0,
            'barcode' => '',
            'attributes' => [],
            'ean' => (string)$itemData->ean,
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->id,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->id,
            'supplierCategory' => $cat1,
            'mappingId' => $categoryMap[0]->getId(),
        ]);
    }

    protected function fetchData(): array
    {
        $client = new Client(['cookies' => true]);
        // login
        $client->request('POST', 'https://www.gembird.rs/b2b/login/store', [
            'timeout' => 60,
            'form_params' => [
                'username' => 'konovo',
                'password' => 'bg011'
            ]
        ]);
        // get data
        $response = $client->get($this->supplier->getFeedSource());
        $xml = new \SimpleXMLElement($response->getBody()->getContents());
        return $xml->xpath('//product');
    }
}
