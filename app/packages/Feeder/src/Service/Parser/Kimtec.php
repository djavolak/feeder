<?php
/** @noinspection PhpComposerExtensionStubsInspection */


namespace EcomHelper\Feeder\Service\Supplier;

use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Feeder\Service\CategoryNotMappedException;
use EcomHelper\Feeder\Service\MapNotExistingException;
use EcomHelper\Feeder\Service\Parser;
use EcomHelper\Feeder\Model\Product;
use GuzzleHttp\Exception\GuzzleException;

use function DI\string;

class Kimtec extends Parser
{
    private array $connectionConfig = [
        'cert' => DATA_PATH . '/feed/keys/kimtek.crt.pem',
        'ssl_key' => DATA_PATH . '/feed/keys/kimtek.key.pem'
    ];
    private array $categories;

    /**
     * @throws MapNotExistingException
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     */
    protected function parseItem($itemData): Product
    {
        $cat1 = $itemData['categories'][0] ?? null;
        $cat2 = $itemData['categories'][1] ?? null;
        $cat3 = $itemData['categories'][2] ?? null;
        $catArr = [];
        if($cat1) {
            $catArr[] = $cat1;
        }
        if($cat2) {
            $catArr[] = $cat2;
        }
        if($cat3) {
            $catArr[] = $cat3;
        }
        if (count($catArr) === 0) {
            throw new CategoryNotMappedException('No category found for product ' . $itemData['productCode']);
        }
        $catString = '';
        foreach($catArr as $key => $arr) {
            if (array_key_last($catArr) === $key) {
                $catString .= $arr;
            } else {
                $catString .= $arr . '-';
            }
        }

        $categoryMap = $this->checkMappingStatus($catString);
        $status = \EcomHelper\Product\Model\Product::STATUS_PRIVATE;
        $stockStatus = 1;
        if ((int) $itemData['stockStatus'] !== 1) {
            $stockStatus = 0;
        }
        if (count($itemData['images']) === 0 || (int) $itemData['price'] === 0) {
            $status = \EcomHelper\Product\Model\Product::STATUS_DRAFT;
        }

        $inputPrice = $itemData['price'] * 1.2;
        return new Product(...[
            'sku' => 'K-' . $itemData['productCode'],
            'productId' => '',
            'supplierProductId' => $itemData['productCode'],
            'barcode' => '',
            'ean' => '',
            'supplierId' => $this->supplier->getId(),
            'categories' => (int) $categoryMap->getCategory()->getId(),
            'title' => (string) $itemData['title'],
            'status' => $status,
            'description' => $itemData['description'],
            'images' => $itemData['images'],
            'inputPrice' => $inputPrice,
            'price' => $this->getPriceWithAddedMargin($categoryMap, $inputPrice),
            'specialPrice' => 0,
            'specialPriceFrom' => '',
            'specialPriceTo' => '',
            'quantity' => 0,
            'stockStatus' => $stockStatus,
            'attributes' => $itemData['attributes'],
            'weight' => $itemData['weight'],
            'supplierCategory' => $catString,
            'mappingId' => $categoryMap->getId(),
        ]);
    }

    /**
     * @throws \RedisException
     * @throws GuzzleException
     * @throws \Exception
     */
    protected function fetchData(): array
    {
        ini_set('memory_limit', '1024M');
        $productsData = [];
        $this->categories = $this->getCategories();
        $items = $this->getItemList();
        $prices = $this->getPrices();
        $attributes = $this->groupAttributesByProductId();
//        $breakAfter = 0;
        foreach ($items as $productElement) {
//            $breakAfter++;
//            if ($breakAfter > 4000) {
//                break;
//            }
            $productCode = (string) $productElement->ProductCode;
            $productInfoKey = $productCode . '-productInfo';
            $productData = unserialize($this->getRedis()->get($productInfoKey));
            if ($productData === false) {
                $image = (string) $productElement->ProductImageUrl;
                $images = [$image];
                $productData = [
                    'productId' => '',
                    'productCode' => $productCode,
                    'title' => (string) $productElement->ProductName,
                    'categories' => $this->getProductCats((string)$productElement->ProductCode),
                    'description' => $this->generateDescription($productElement),
                    'images' => $images,
                    'weight' => (string) $productElement->PackageWeight,
                    'manufacturer' => (string) $productElement->Brand,
                    'status' => 1,
                    'stockStatus' => $prices[$productCode]['stockStatus'],
                    'price' => $prices[$productCode]['price'],
                    'attributes' => $attributes[(string) $productElement->ProductCode] ?? []
                ];
                $this->getRedis()->set($productInfoKey, serialize($productData), 60*60*4);
            }
            $productsData[] = $productData;
        }

        return $productsData;
    }

    /**
     * @throws GuzzleException
     */
    private function getItemList(): bool|array|null
    {
        $response = $this->httpClient->get($this->supplier->getFeedSource(), $this->connectionConfig);
        $responseBody = $response->getBody()->getContents();
        $getItems = false;
        if (str_contains($responseBody,'0010 - Too many request.') || str_contains($responseBody,'<b2bexception>')){
//            var_dump('blocked get items, using old data');
        } else {
            file_put_contents(DATA_PATH . '/feed/kimtek.xml', $responseBody);
            $getItems = true;
        }
        //no need to read from file if we got good response
        if (!$getItems) {
            $items = file_get_contents(DATA_PATH . '/feed/kimtek.xml');
        } else {
            $items = $responseBody;
        }
        $xml = new \SimpleXMLElement($items);
        return $xml->xpath('//Table');
    }

    private function getCategories(): array
    {
        $productCategoriesUrl = 'https://b2b.kimtec.rs/B2BService/HTTP/Product/GetCategoriesList.aspx?CategoryTypeID=1';
        $response = $this->httpClient->get($productCategoriesUrl, $this->connectionConfig);
        $responseXml = $response->getBody()->getContents();
         $getCategories = false;
        if (str_contains($responseXml,'0010 - Too many request.') || str_contains($responseXml,'<b2bexception>')){
//            var_dump('blocked get categories, using old data');
        } else {
            file_put_contents(DATA_PATH . '/feed/kimtekCat.xml', $responseXml);
            $getCategories = true;
        }
        //no need to read from file if we got good response
        if (!$getCategories) {
            $categories = file_get_contents(DATA_PATH . '/feed/kimtekCat.xml');
        } else {
            $categories = $responseXml;
        }
        $xml = new \SimpleXMLElement($categories);
        $categories = [];
        foreach ($xml->xpath('//Table') as $elem) {
            $categories[(string) $elem->CategoryID] = [
                'name' => (string) $elem->CategoryName,
                'parent' => (string) $elem->ParentCategoryID,
            ];
        }
        return $categories;
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    private function getPrices(): array
    {
        $productPriceUrlBase = 'https://b2b.kimtec.rs/B2BService/HTTP/Product/GetProductsPriceList.aspx';
        $response = $this->httpClient->get($productPriceUrlBase, $this->connectionConfig);
        $responseXml = $response->getBody()->getContents();
        $getPrices = false;
        if (str_contains($responseXml,'0010 - Too many request.') || str_contains($responseXml,'<b2bexception>')){
            //We are blocked
//            var_dump('blocked product price list, using old data');
        } else {
            file_put_contents(DATA_PATH . '/feed/kimtekPrice.xml', $responseXml);
            $getPrices = true;
        }
        //no need to read from file if we got good response
        if (!$getPrices) {
            $pricesXml = file_get_contents(DATA_PATH . '/feed/kimtekPrice.xml');
        } else {
            $pricesXml = $responseXml;
        }
        $pricesXml = new \SimpleXMLElement($pricesXml);
        $prices = [];
        foreach ($pricesXml->xpath('//Table') as $elem) {
            $prices[(string) $elem->ProductCode] = [
                'price' => (int) (string) $elem->ProductPartnerPrice,
                'stockStatus' => ((int) (string) $elem->ProductAvailability > 0) ? 1 : 0,
            ];
        }
        return $prices;
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     * @throws \Exception
     */
    private function fetchProductCats(string $productCode): bool|array|null
    {
        $catXml = $this->getRedis()->get($productCode . '-catInfo');
        if ($catXml === false) {
            $productCatUrlBase = 'https://b2b.kimtec.rs/B2BService/HTTP/Product/GetProductsCategory.aspx?CategoryTypeID=1&ProductCode=' . $productCode;
            $response = $this->httpClient->get($productCatUrlBase, $this->connectionConfig);
            $catXml = $response->getBody()->getContents();
            if (str_contains($catXml,'0010 - Too many request.') || str_contains($catXml,'<b2bexception>')){
//                var_dump('blocked get categories, using old data');
            } else {
                $this->getRedis()->set($productCode . '-catInfo', $catXml, 60*60*8);
            }
        }
        $catXml = new \SimpleXMLElement($catXml);
        return $catXml->xpath('//Table');
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    private function getProductCats($productCode): array
    {
        $productCats = [];
        foreach ($this->fetchProductCats($productCode) as $elem) {
            if (!isset($this->categories[(string) $elem->CategoryID])) {;
                continue;
            }
            $category = $this->categories[(string) $elem->CategoryID];
            array_unshift($productCats, $category['name']);
            if (!isset($this->categories[$category['parent']])) {
                continue;
            }
            $catParent = $this->categories[$category['parent']];
            array_unshift($productCats, $catParent['name']);
            if ($catParent['parent']) {
                if (!isset($this->categories[$catParent['parent']])) {
                    continue;
                }
                $catGrandParent = $this->categories[$catParent['parent']];
                array_unshift($productCats, $catGrandParent['name']);
            }
        }
        return $productCats;
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    private function getAttributes(): bool|array|null
    {
        $attributesUrl = 'https://b2b.kimtec.rs/B2BService/HTTP/Product/GetProductsSpecification.aspx';
        $response = $this->httpClient->get($attributesUrl, $this->connectionConfig);
        $responseXml = $response->getBody()->getContents();
        $attributesReq = false;
        if (str_contains($responseXml,'0010 - Too many request.') || str_contains($responseXml,'<b2bexception>')){
            //We are blocked
//            var_dump('blocked product specification list, using old data');
        } else {
            file_put_contents(DATA_PATH . '/feed/kimtekSpecification.xml', $responseXml);
            $attributesReq = true;
        }
        if ($attributesReq === false) {
            $responseXml = file_get_contents(DATA_PATH . '/feed/kimtekSpecification.xml');
        }
        $attributesXml = new \SimpleXMLElement($responseXml);
        return $attributesXml->xpath('//Table');
    }

    /**
     * @throws \Exception
     */
    private function groupAttributesByProductId(): array
    {
        $attributes = $this->getAttributes();
        $attributesByProductId = [];
        foreach ($attributes as $attribute) {;
            $valuesXml = new \SimpleXMLElement($attribute->SpecificationItemValues);
            $values = [];
            foreach ($valuesXml->Value as $value) {
                $values[] = (string) $value;
            }
            $attributesByProductId[(string) $attribute->ProductCode][] = [
                'name' => (string) $attribute->SpecificationItemName,
                'values' => $values,
            ];
        }
        return $attributesByProductId;
    }

    private function generateDescription($productElement): string
    {
        return $productElement->MarketingDescription . '<br /><br />' . $productElement->TechnicalDescription;
    }
}