<?php
namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Psr7\Request;

class Comtrade extends Parser
{
    protected function parseItem($itemData): Product
    {
        $qty = (int) str_replace(['>', '<'], '', (string) $itemData['QTTYINSTOCK']);
        $stockStatus = 1;
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        if ($qty === 0) {
            $stockStatus = 0;
        }

        $productTitle = (string) $itemData['NAME'];
        // not all items have barcode... !@#$!@
        $vendorCode = (string) $itemData['BARCODE'];
        $eurExchangeRate = (string) $itemData['EUR_ExchangeRate'];
        $exchangeRateFormat = str_replace(',', '.', $eurExchangeRate);
        $formatedExchangeRate = round($exchangeRateFormat, 2);
        $warranty = (string) $itemData['WARRANTY'];
        $description = (string) $itemData['SHORT_DESCRIPTION'];
        if ($description === '') {
            $description = sprintf('%s %s %s', $productTitle, $vendorCode, $warranty);
        } else {
            $description .= sprintf(' %s %s', $vendorCode, $warranty);
        }

        $cat1 = $itemData['categoryCode'];
        $catString = $cat1;
        $categoryMap = $this->checkMappingStatus($catString);
        //@todo check with client if this is needed in description or not
//        if (count($attributes) > 0) {
//            $specs = '<br /><ul>';
//            foreach ($attributes as $attrData) {
//                $specs .= '<li>' . sprintf('%s:%s', $attrData['name'], implode(' ,', $attrData['values'],)) .'</li>';
//            }
//            $specs .= '</ul><br />';
//            $description .= $specs;
//        }
        $price = ceil($itemData['PRICE'] * $formatedExchangeRate);
        $vat = 1.20;
        if ((int) $itemData['TAX'] > 0) {
            $vat = '1.' . (int) $itemData['TAX'];
        }
        $inputPrice = $price * $vat;
        if (count($itemData['images']) === 0 || (int) $price === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }
        $data = [
            'sku' => 'CT-' . $itemData['CODE'],
            'productId' => '',
            'supplierProductId' => (string) $itemData['CODE'],
            'barcode' => (string) $itemData['CODE'],
            'ean' => '',
            'supplierId' => $this->supplier->getId(),
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'title' => (string) $itemData['NAME'],
            'status' => $status,
            'description' => $description,
            'images' => $itemData['images'],
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'quantity' => 0,
            'stockStatus' => $stockStatus,
//            'manufacturer' => (string) $itemData['MANUFACTURER'],
//                    'attributes' => $attributes,
            'attributes' => $itemData['attributes'],
            'weight' => 0,
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ];
        return new Product(...$data);
    }

    protected function fetchData(): array
    {
        $simpleXml = new \SimpleXMLElement($this->getCategories());
        libxml_use_internal_errors(true);
        $categoriesData = $simpleXml->xpath('//soap:Body')[0]->GetCTProductGroups_WithAttributesResponse
            ->GetCTProductGroups_WithAttributesResult->ProductGroup;

        $breakAfter = 0;
        $productsData = [];
        $categories = [];
        foreach ($categoriesData as $categoryElement) {
            $xml = $this->getAllProducts((string) $categoryElement->Code)->getContents();
            $simpleXml = new \SimpleXMLElement($xml);
            try {
                $products = $simpleXml->xpath('//soap:Body')[0]->GetCTProducts_WithAttributesResponse
                    ->GetCTProducts_WithAttributesResult->CTPRODUCT;
            } catch (\Exception $e) {
                var_dump($e->getMessage());
                continue;
            }
//            $categoryCode = (string) $categoryElement->Code;
//            $categoryGroup = (string) $categoryElement->GroupCode;
//            var_dump((string) $categoryElement->Code);
//            var_dump((string) $categoryElement->GroupCode);
//            if (!array_key_exists($categoryCode, $categories)) {
//                $categories[$categoryCode] = $categoryElement;
//            }
            $catAttributes = [];
            foreach ($categoryElement->Attributes->ProductAttribute as $catAttr) {
                $catAttributes[(string) $catAttr->AttributeCode] = (string) $catAttr->AttributeName;
            }
            foreach ($products as $productElement) {
                $productAttributes = [];
                foreach ($productElement->ATTRIBUTES as $attribute) {
                    foreach ($attribute as $attr) {
                       $value = (string) $attr->AttributeValue;
                       $name = $catAttributes[(string) $attr->AttributeCode];
                        if (!$value) {
                            continue;
                        }
                        if (array_key_exists($name, $productAttributes)) {
                            if (!in_array($value, $productAttributes[$name]['values'])) {
                                $productAttributes[$name]['values'][] = $value;
                            }
                        } else {
                            $productAttributes[$name] = [
                                'name' => $name,
                                'values' => [$value]
                            ];
                        }
                    }
                }
//                if ($breakAfter > 20) {
//                    break(2);
//                }
//                $breakAfter++;

                $item = [
                    'CODE' => (string) $productElement->CODE,
                    'PRODUCTGROUPCODE' => (string) $productElement->PRODUCTGROUPCODE,
                    'NAME' => (string) $productElement->NAME,
                    'MANUFACTURER' => (string) $productElement->MANUFACTURER,
                    'SHORT_DESCRIPTION' => (string) $productElement->SHORT_DESCRIPTION,
                    'MANUFACTURERCODE' => (string) $productElement->MANUFACTURERCODE,
                    'QTTYINSTOCK' => (string) $productElement->QTTYINSTOCK,
                    'TAX' => (string) $productElement->TAX,
                    'PRICE' => (string) $productElement->PRICE,
                    'WARRANTY' => (string) $productElement->WARRANTY,
                    'EUR_ExchangeRate' => (string) $productElement->EUR_ExchangeRate,
                    'BARCODE' => (string) $productElement->BARCODE,
                    'attributes' => $productAttributes,
                    'categoryElement' => $categoryElement,
                    'categoryCode' => (string) $categoryElement->Code,
                    'categoryGroup' => (string) $categoryElement->GroupCode,
                ];
                $images = [];
                foreach ($productElement->IMAGE_URLS->URL as $url) {
                    $images[] = (string) $url;
                }
                $item['images'] = $images;
                $productsData[] = $item;
            }
        }
        $this->categories = $categories;

        return $productsData;
    }

    private function getCategories()
    {
        $headers = [
            'Content-Type' => 'application/soap+xml',
        ];
        $xml = $this->compileGetCategoriesWithAttributesXml();
        $request = new Request('POST', $this->supplier->getFeedSource(), $headers, $xml);
        $response = $this->httpClient->send($request);

        return $response->getBody()->getContents();
    }

    private function getAllProducts(string $categoryCode)
    {
        $headers = [
            'Content-Type' => 'application/soap+xml',
        ];
        $xml = $this->compileGetProductsWithAttributesXml($categoryCode);
        $request = new Request('POST', $this->supplier->getFeedSource(), $headers, $xml);
        $response = $this->httpClient->send($request);

        return $response->getBody();
    }

    private function compileGetProductsWithAttributesXml($categoryCode)
    {
        return sprintf('<?xml version="1.0" encoding="utf-8"?>
            <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
             <soap12:Body>
               <GetCTProducts_WithAttributes xmlns="http://www.ct4partners.com/B2B">
                 <username>%s</username>
                 <password>%s</password>
                 <productGroupCode>%s</productGroupCode>
               </GetCTProducts_WithAttributes>
             </soap12:Body>
            </soap12:Envelope>', $this->supplier->getFeedUsername(), $this->supplier->getFeedPassword(), $categoryCode);
    }

    private function compileGetCategoriesWithAttributesXml()
    {
        return sprintf('<?xml version="1.0" encoding="utf-8"?>
            <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
            xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
             <soap12:Body>
               <GetCTProductGroups_WithAttributes xmlns="http://www.ct4partners.com/B2B">
                 <username>%s</username>
                 <password>%s</password>
               </GetCTProductGroups_WithAttributes>
             </soap12:Body>
            </soap12:Envelope>', $this->supplier->getFeedUsername(), $this->supplier->getFeedPassword());
    }
}