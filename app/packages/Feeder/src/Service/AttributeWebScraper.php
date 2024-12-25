<?php

namespace EcomHelper\Feeder\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use stringEncode\Exception;
use Symfony\Component\DomCrawler\Crawler;
use function PHPUnit\Framework\containsOnlyInstancesOf;

class AttributeWebScraper
{
    public function __construct(private \Redis $cache) {}

    /**
     * @throws GuzzleException
     * @throws \RedisException
     * @throws Exception
     */
    public function __invoke(string|int $supplier, string $productSupplierId): array
    {
        return match ($supplier) {
            'irismega', 2, 6 => $this->getAttributesForIrisMega($productSupplierId),
            'roaming', 10, 7 => $this->getAttributesForRoaming($productSupplierId),
            'uspon', 9, 3 => $this->getAttributesForUspon($productSupplierId),
            default => [],
        };
    }

    /**
     * @param $productSupplierId
     * @return array
     * @throws GuzzleException
     * @throws \RedisException
     */
    private function getAttributesForIrisMega($productSupplierId): array
    {
        $key = 'jar:' . $productSupplierId;
        $ttl = 60 * 30;
        $jar = unserialize($this->cache->get($key));
        if (!$jar) {
            $jar = new CookieJar;
            $client = new Client(['cookies' => $jar]);
            $response = $client->request('GET', 'https://b2b.irismega.rs/sr/login', [
                'timeout' => 30,
            ]);
            $html = $response->getBody()->getContents();
            $crawler = new Crawler($html);
            $token = $crawler->filter("input[name='_csrf_shop_security_token']")->attr('value');
            $loginResp = $client->request('POST', 'https://b2b.irismega.rs/sr/login-check', [
                'timeout' => 30,
                'form_params' => [
                    '_username' => 'office@konovo.rs',
                    '_password' => 'konovo123',
                    '_csrf_shop_security_token' => $token,
                ]
            ]);
            if ($loginResp->getStatusCode() === 200) {
                $this->cache->set($key, serialize($jar), $ttl);
            }
        }
        if (!isset($client)) {
            $client = new Client(['cookies' => $jar]);
        }

        $searchResp = $client->request('GET', 'https://b2b.irismega.rs/sr/pretraga?term=' . $productSupplierId, [
            'timeout' => 30,
        ]);
        $searchRespHtml = $searchResp->getBody()->getContents();
        $searchRespCrawler = new Crawler($searchRespHtml);
        $productUrl = $searchRespCrawler->filter('.im-image')->attr('href');
        $singleProductResp = $client->request('GET', 'https://b2b.irismega.rs' . $productUrl, [
            'timeout' => 30,
        ]);
        $singleProductRespHtml = $singleProductResp->getBody()->getContents();
        $singleProductRespCrawler = new Crawler($singleProductRespHtml);
        $attributes = $singleProductRespCrawler->filter('#content-1')->filter('.specs-table');
        $attributesArray = [];
        $crawler = new Crawler($attributes->html());
        $crawler->filter('div')->each(function (Crawler $node) use (&$attributesArray) {
            $attributeName = $node->filter('span')->eq(0)->text();
            $attributeValue = $node->filter('span')->eq(1)->text();
            if (array_key_exists($attributeName, $attributesArray)) {
                if (!in_array($attributeValue, $attributesArray[$attributeName]['values'])) {
                    $attributesArray[$attributeName]['values'][] = $attributeValue;
                }
            } else {
                $attributesArray[$attributeName] = [
                    'name' => $attributeName,
                    'values' => [$attributeValue]
                ];
            }
        });
        return $attributesArray;
    }

    /**
     * @param string $productSupplierId
     * @return array
     * @throws Exception
     * @throws GuzzleException
     * @throws \RedisException
     */
    private function getAttributesForRoaming(string $productSupplierId): array
    {
        $key = 'jar:' . $productSupplierId;
        $ttl = 60 * 30;
        $jar = unserialize($this->cache->get($key));
        if (!$jar) {
            $jar = new CookieJar;
            $client = new Client(['cookies' => $jar]);
            $loginResp = $client->request('POST', 'https://www.roaming.rs/b2b/login/store', [
                'timeout' => 30,
                'form_params' => [
                    'username' => 'konovo',
                    'password' => 'konovo011',
                ]
            ]);
            if ($loginResp->getStatusCode() === 200) {
                $this->cache->set($key, serialize($jar), $ttl);
            }
        }
        if (!isset($client)) {
            $client = new Client(['cookies' => $jar]);
        }
        $searchResp = $client->request('GET', 'https://www.roaming.rs/b2b/pretraga?q=' . $productSupplierId, [
            'timeout' => 30,
        ]);
        $searchRespHtml = $searchResp->getBody()->getContents();
        $searchRespCrawler = new Crawler($searchRespHtml);
        $productUrl = $searchRespCrawler->filter('.product-title')->attr('href');
        $singleProductResp = $client->request('GET', $productUrl, [
            'timeout' => 30,
        ]);
        $singleProductRespHtml = $singleProductResp->getBody()->getContents();
        try {
            $attributes = $this->parseRoamingFromTable($singleProductRespHtml);
        } catch (\Exception $e) {
            $attributes = $this->parseRoamingFromList($singleProductRespHtml);
        }

        return $attributes;
    }

    /**
     * @param $singleProductRespHtml
     * @return array
     */
    private function parseRoamingFromTable($singleProductRespHtml): array
    {
        $singleProductRespCrawler = new Crawler($singleProductRespHtml);
        $attributes = [];
        $table = $singleProductRespCrawler->filter('table')->first();
        $table->filter('tbody tr')->each(function (Crawler $row) use (&$attributes) {
            $attributeName = $row->filter('td')->eq(0)->text();
            if ($attributeName === 'EAN:' || $attributeName === 'Prava potrošača:' || $attributeName === 'Opis proizvoda:') {
                return;
            }
            $attributeValue = $row->filter('td')->eq(1)->text();
            if (array_key_exists($attributeName, $attributes)) {
                if (!in_array($attributeValue, $attributes[$attributeName]['values'])) {
                    $attributes[$attributeName]['values'][] = $attributeValue;
                }
            } else {
                $attributes[$attributeName] = [
                    'name' => $attributeName,
                    'values' => [$attributeValue]
                ];
            }
        });
        return $attributes;
    }

    /**
     * @param $singleProductRespHtml
     * @return array
     */
    private function parseRoamingFromList($singleProductRespHtml): array
    {
        $singleProductRespCrawler = new Crawler($singleProductRespHtml);
        $attributes = [];
        $container = $singleProductRespCrawler->filter('#home')->first();
        $details = $container->filter('.spec-highlight__detail');
        $details->each(function (Crawler $detail) use (&$attributes) {
            $attributeName = $detail->filter('.spec-highlight__title')->text();
            $attributeValue = $detail->filter('.spec-highlight__value')->text();
            if (array_key_exists($attributeName, $attributes)) {
                if (!in_array($attributeValue, $attributes[$attributeName]['values'])) {
                    $attributes[$attributeName]['values'][] = $attributeValue;
                }
            } else {
                $attributes[$attributeName] = [
                    'name' => $attributeName,
                    'values' => [$attributeValue]
                ];
            }
        });
        return $attributes;
    }

    private function getAttributesForUspon(string $productSupplierId)
    {
        $key = 'jar:' . $productSupplierId;
        $ttl = 60 * 30;
        $jar = unserialize($this->cache->get($key));
        if (!$jar) {
            $jar = new CookieJar;
            $client = new Client(['cookies' => $jar]);
            $formParams = [
                'username' => 'office@konovo.rs',
                'password' => 'konovo2022',
            ];
            $loginResp = $client->request('POST', 'https://www.uspon.rs/b2b/login/store', [
                'timeout' => 30,
                'form_params' => $formParams
            ]);
            if ($loginResp->getStatusCode() === 200) {
                $this->cache->set($key, serialize($jar), $ttl);
            }
        }
        if (!isset($client)) {
            $client = new Client(['cookies' => $jar]);
        }
        $searchResp = $client->request('GET',
            'https://www.uspon.rs/b2b/pretraga?search_grupa_pr_id=0&q=' . $productSupplierId, [
                'timeout' => 30,
            ]);
        $searchRespHtml = $searchResp->getBody()->getContents();
        $searchRespCrawler = new Crawler($searchRespHtml);
        $productUrl = $searchRespCrawler->filter('.product-title')->attr('href');
        $singleProductResp = $client->request('GET', $productUrl, [
            'timeout' => 30,
        ]);
        $singleProductRespHtml = $singleProductResp->getBody()->getContents();
        $singleProductRespCrawler = new Crawler($singleProductRespHtml);
        $attributes = [];
        $table = $singleProductRespCrawler->filter('table')->first();
        $table->filter('tbody tr')->each(function (Crawler $row) use (&$attributes) {
            $attributeName = $row->filter('td')->eq(0)->text();
            $attributeValue = $row->filter('td')->eq(1)->text();
            if (array_key_exists($attributeName, $attributes)) {
                if (!in_array($attributeValue, $attributes[$attributeName]['values'])) {
                    $attributes[$attributeName]['values'][] = $attributeValue;
                }
            } else {
                $attributes[$attributeName] = [
                    'name' => $attributeName,
                    'values' => [$attributeValue]
                ];
            }
        });
        return $attributes;
    }

}