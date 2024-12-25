<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;

class Test extends Parser
{
    protected $supplierId = 1;
    protected $supplierCode = 'test';
    protected $supplierName = 'Test supplier';

    public function getSupplierName(): string
    {
        return $this->supplierName;
    }

    public function parseItem($itemData): Product
    {
        return new Product(...[
            'id' => $itemData->id ?? '',
            'title' => $itemData->title,
            'images' => $itemData->images,
            'price' => $itemData->price,
            'inputPrice' => $itemData->inputPrice,
            'description' => $itemData->description,
            'specialPrice' => $itemData->specialPrice,
            'specialPriceFrom' => $itemData->specialPriceFrom,
            'specialPriceTo' => $itemData->specialPriceTo,
            'status' => $itemData->status,
            'stockStatus' => $itemData->stockStatus,
            'quantity' => $itemData->quantity ?? 0,
            'categories' => $itemData->category,
            'weight' => $itemData->weight,
            'barcode' => $itemData->barcode,
            'attributes' => $itemData->attributes,
            'ean' => $itemData->ean,
            'sku' => $itemData->sku,
            'supplierId' => $this->supplierId,
            'supplierProductId' => $itemData->productId
        ]);
    }

    protected function fetchData(): array
    {
        $startTime = $this->getMicrotime();
        $data = json_decode(file_get_contents($this->config->offsetGet('suppliers')->{$this->supplierCode}->source));
        $endTime = $this->getMicrotime();
        $msg = sprintf('items for %s fetched in %s seconds',
            $this->supplierName,
            $endTime - $startTime
        );
        $this->logger->info($msg);

        return $data;
    }
}