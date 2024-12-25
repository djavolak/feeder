<?php

namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Feeder\Model\Product;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;

class Atom extends Parser
{

    /**
     * @throws MapNotExistingException
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     */
    protected function parseItem($itemData): Product
    {
        $cats = explode('/', (string) $itemData->category);
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
        $categoryMap = $this->checkMappingStatus($catString);;
        $quantity = (int)$itemData->stock;
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 0;
        if ($quantity > 0) {
            $stockStatus = 1;
        }
        if ((int) $itemData->price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $vat = 1.20;
        $inputPrice = (string)$itemData->price;
        $inputPrice = str_replace(',', '', $inputPrice);
        $inputPrice = (int)$inputPrice * $vat;

        return new Product(...[
            'productId' => '',
            'title' => (string)$itemData->name,
            'images' => [], // this supplier does not provide images
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'description' => '', // this supplier does not provide description
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'status' => $status,
            'stockStatus' => $stockStatus,
            'quantity' => $quantity,
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'weight' => 0,
            'barcode' => (string)$itemData->barcode,
            'attributes' => '',
            'ean' => '',
            'sku' => $this->supplier->getSkuPrefix() .'-'. $itemData->id,
            'supplierId' => $this->supplier->getId(),
            'supplierProductId' => (string) $itemData->id,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    protected function fetchData(): array
    {
        $connection = ftp_connect($this->supplier->getFeedSource()) or die("Could not connect to {$this->supplier->getFeedSource()}");
        $login = ftp_login($connection, $this->supplier->getFeedUsername(), $this->supplier->getFeedPassword());
        ftp_pasv($connection, true);
        if ($login){
            $localFile = DATA_PATH . '/feed/' . $this->supplier->getName() . '.xml';
            $remoteFile = 'konovo.xml';
            $file = ftp_get($connection, $localFile, $remoteFile);
            ftp_close($connection);
            if ($file){
                $xml = simplexml_load_file($localFile);
                return $xml->xpath('//Item');
            }
        }

    }
}