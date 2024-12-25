<?php
namespace EcomHelper\Feeder\Service\Parser;

use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Category\Model\Category;
use EcomHelper\Category\Model\CategoryMapper;
use EcomHelper\Category\Model\IgnoredCategory;
use EcomHelper\Category\Repository\CategoryMapperRepository as CatMapperRepo;
use EcomHelper\Feeder\Model\Product;
use EcomHelper\Feeder\Repository\SupplierAttributeMapping;
use EcomHelper\Product\Model\Supplier;
use EcomHelper\Product\Repository\ProductRepository as ProductRepo;
use EcomHelper\Product\Repository\SupplierRepository as SupplierRepo;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;

abstract class AbstractParser implements ParserInterface
{
    const CACHE_KEY_CREATE = 'feed#create#';
    const CACHE_KEY_UPDATE = 'feed#update#';

    protected $httpClient;

    protected $logger;

    protected $config;

    private $redis;

    private $productRepo;

    private $supplierRepo;

    protected $catMapperRepo;


    protected Supplier $supplier;

    public function __construct(
        Client $client, Logger $logger, Config $config, \Redis $redis, ProductRepo $productRepo, SupplierRepo $supplierRepo,
        CatMapperRepo $catMapperRepo, private \EcomHelper\Product\Service\Product $productService,
    ) {
        $this->httpClient = $client;
        $this->logger = $logger;
        $this->config = $config;
        $this->redis = $redis;
        $this->productRepo = $productRepo;
        $this->catMapperRepo = $catMapperRepo;
        $this->supplierRepo = $supplierRepo;
    }

    /**
     * @param $itemData
     *
     * @throws CategoryNotMappedException
     *
     * @return Product
     */
    abstract protected function parseItem($itemData): Product;

    abstract protected function fetchData(): array;

    public function parse()
    {
//        $this->supplier = $this->supplierRepo->getById($this->supplierId);
        $this->supplier = $this->supplierRepo->fetchAll(['code' => strtolower(explode('\\', get_class($this))[4])])[0];
        $msg = sprintf('Started parser for %s.', $this->supplier->getName());
        $this->logger->debug($msg);

        $startTime = $this->getMicrotime();
        $this->items = $this->fetchData();
        $this->logger->info(sprintf('items for %s fetched in %s seconds',
            $this->supplier->getName(), $this->getMicrotime() - $startTime
        ));

        $existingCount = 0;
        $newCount = 0;
        $errors = ['notMapped' => [], 'general' => [], 'ignored' => [], 'notFound' => []];
        $supplierCodes = [];
        $startTime = $this->getMicrotime();
//        $i = 0;
        foreach ($this->items as $itemData) {
//            $i++;
//            if ($i > 500) {
//                break;
//            }
            try {
                $product = $this->parseItem($itemData);
            } catch (CategoryIgnoredException $e) {
                $errors['ignored'][] = $e->getMessage();
                continue;
            } catch (MapNotExistingException $e) {
                if (!in_array($e->getMessage(), $errors['notFound'])) {
                    $errors['notFound'][$e->getMessage()][] = $e->getMessage();
                }
                continue;
            } catch (CategoryNotMappedException $e) {
                if (!in_array($e->getMessage(), $errors['notMapped'])) {
                    $errors['notMapped'][$e->getMessage()][] = $e->getMessage();
                }
                continue;
            } catch (\Exception $e) {
                $errors['general'][] = $e->getMessage();
                if (false !== strpos($e->getMessage(), 'MySQL server has gone away')) {
                    break;
                }
                continue;
            }
            $cacheKey = static::CACHE_KEY_CREATE . $this->supplier->getCode();
            $existing = $this->productRepo->getBySupplierId($product->getSupplierId(), $product->getSupplierProductId());
            if ($existing) {
                $cacheKey = static::CACHE_KEY_UPDATE . $this->supplier->getCode();
                $product->setId($existing->getId());
                $existingCount++;
            } else {
                if ($product->getStockStatus() === 0) {
                    continue;
                }
                $newCount++;
            }
            $supplierCodes[$product->getSupplierProductId()] = '';

            $serializedProduct = serialize($product);
            $key = md5($serializedProduct);
//            $this->redis->del($cacheKey . $key);
            $this->redis->set($cacheKey . $key, $serializedProduct);
            $this->redis->sAdd($cacheKey . 'index', $key);
        }
        $endTime = $this->getMicrotime();
        $msg = sprintf(
            'feed for %s parsed in %s seconds. | %s items parsed | %s existing items found | %s new items found',
            $this->supplier->getName(), round($endTime - $startTime), count($this->items), $existingCount, $newCount);
        $this->logger->info($msg);

        //$errors mappings ...
        $this->parseErrors($errors);

        $this->parseLeftovers($supplierCodes);

//        $this->updateCategoryMapping();
    }

    /**
     * Parsing errors. added some stats, could be better.
     *
     * @param $errors
     * @return void
     * @throws \Exception
     */
    private function parseErrors($errors)
    {
        foreach ($errors['notFound'] as $entry => $errorData) {
            $sources = explode('#', stripcslashes($entry));
            $data = [
                'source1' => $sources[0] ?? '',
                'source2' => $sources[1] ?? '',
                'categoryId' => 0,
                'supplierId' => $this->supplier->getId(),
                'source3' => $sources[2] ?? '',
            ];
            $existingUnmapped = $this->catMapperRepo->fetchAll($data);
            if (count($existingUnmapped) === 0) {
                try {
                    $this->catMapperRepo->create([
                        'source1' => $sources[0],
                        'source2' => $sources[1] ?? '',
                        'categoryId' => 0,
                        'supplierId' => $this->supplier->getId(),
                        'source3' => $sources[2] ?? '',
                        'count' => count($errorData)
                    ]);
                } catch (\Exception $e) {
                    var_dump('errors');
                    var_dump($e->getMessage());
                }
            } else {
                $this->catMapperRepo->updateField('count', count($errorData), $existingUnmapped[0]->getId());
            }
        }
        if (count($errors['ignored'])) {
            var_dump('ignored ' . count($errors['ignored']));
//            foreach ($errors['ignored'] as $entry => $errorData) {
//                var_dump($errorData);
//            }
        }
        if (count($errors['notMapped'])) {
            var_dump('notMapped: '. count($errors['notMapped']));
//            foreach ($errors['notMapped'] as $entry => $errorData) {
//                var_dump($errorData[0] . ' ' . count($errorData));
//            }
        }
        if (count($errors['general'])) {
            var_dump('general');
            foreach ($errors['general'] as $entry => $errorData) {
                var_dump($errorData);
            }
        }
    }

    /**
     * find items removed from feed and change status accordingly
     *
     * @param $supplierCodes
     * @return void
     * @throws \Exception
     * @throws GuzzleException
     */
    private function parseLeftovers($supplierCodes): void
    {
        $ids = [];
        // ignore removed status
        $dbCodes = $this->productRepo->getSupplierCodes($this->supplier->getId());
        foreach ($dbCodes as $productCode) {
            if (!isset($supplierCodes[$productCode])) {
                $ids[] = $this->productRepo->removedFromFeed($productCode, $this->supplier->getId());
            }
        }
        $this->productService->syncProducts($ids, 'feed');
        $this->logger->info(sprintf('%s items was removed from feed @ source', count($ids)));
    }

    protected function getMicrotime()
    {
        [$usec, $sec] = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param $categoryMap
     * @param $inputPrice
     * @return \EcomHelper\Category\Model\MarginRule|null
     */
    protected function getPriceWithAddedMargin($categoryMap, $inputPrice): int
    {
        $inputPrice = (float) $inputPrice;
        $rules = unserialize($categoryMap->getLimiter());
        $margins = unserialize($categoryMap->getMargin());
        $marginToApply = null;
        if(!$rules) {
            return ceil($inputPrice / 10) * 10;
        }
        foreach($rules as $key => $marginRule) {
            if ($key === 0) {
                if ($marginRule === '-1') {
                    if ($margins[$key] !== '') {
                        return $this->getPriceBasedOnMargin($margins[$key], $inputPrice);
                    }
                } else {
                    if((int) $inputPrice <= (int) $marginRule) {
                        return $this->getPriceBasedOnMargin($margins[$key], $inputPrice);
                    }
                }
            }
            //for old data compatibility
            if ($key === 1) {
                if((int) $inputPrice <= (int) $marginRule) {
                    return $this->getPriceBasedOnMargin($margins[$key], $inputPrice);
                }
            }
            if((int) $inputPrice >= (int) $marginRule) {
                $marginToApply = $margins[$key];
            }
        }
        return $this->getPriceBasedOnMargin($marginToApply, $inputPrice);
    }

    private function getPriceBasedOnMargin($margin, $price): float|int
    {
        if($margin && !stripos ($margin, 'rsd')) {
            $price += ((int)$margin / 100) * $price;
        } else if ($margin && stripos($margin, 'rsd')) {
            $price += (int) str_replace(['rsd', 'RSD'], '', $margin);
        }
        return ceil($price / 10) * 10;
    }
    /**
     * @param $catString
     * @return mixed
     * @throws CategoryIgnoredException
     * @throws CategoryNotMappedException
     * @throws MapNotExistingException
     */
    protected function checkMappingStatus($catString)
    {
        $cats = explode('-', $catString);
        $mapFilter = [
            'source1' => filter_var($cats[0], FILTER_SANITIZE_ADD_SLASHES),
            'supplierId' => $this->supplier->getId()
        ];
        if(isset($cats[1])) {
            $mapFilter['source2'] = filter_var($cats[1] ?? '', FILTER_SANITIZE_ADD_SLASHES);
        }
        if (isset($cats[2])) {
            $mapFilter['source3'] = filter_var($cats[2] ?? '', FILTER_SANITIZE_ADD_SLASHES);
        }
        $categoryMap = $this->catMapperRepo->fetchAll($mapFilter);

        $catString = str_replace('-', '#', $catString);
        if (!count($categoryMap)) {
            // create new unmapped category map
            throw new MapNotExistingException($catString);
        }
        if (!$categoryMap[0]->getCategory()) {
            throw new CategoryNotMappedException($catString);
        }
        if ($categoryMap[0]->getCategory() instanceof IgnoredCategory) {
            // skip product parse
            throw new CategoryIgnoredException($catString);
        }
        return $categoryMap[0];
    }
}
