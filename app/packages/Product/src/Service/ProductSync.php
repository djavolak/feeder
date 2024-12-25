<?php

namespace EcomHelper\Product\Service;

use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Repository\TenantCategoryMapperRepository;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Tenant\Repository\TenantRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Mapper\NotFoundException;

class ProductSync
{

    public function __construct(private TenantRepository $tenantRepo, private Logger $logger, private Config $config,
        private CategoryRepository $categoryRepo, private TenantCategoryMapperRepository $tenantCategoryMapperRepository,
    private ProductRepository $productRepo)
    {

    }

    /**
     * @param array $ids
     * @return void
     * @throws GuzzleException
     * @throws \Exception
     */
    public function syncProducts(array $ids, $syncType = 'manual', $supplier = null): void
    {
        $environment = $this->config->get('environment') ?? null;
        if (!$environment) {
            return;
        }
        $tenants = $this->tenantRepo->fetchAll();
        $errors = [];
        /** @var \EcomHelper\Tenant\Model\Tenant $tenant */
        foreach($tenants as $tenant) {
            $url = $tenant->getProductionUrl().$this->config->get('productSyncUrl');
            if (($environment === 'local' || $environment === 'development')) {
                return;//for now
                $url = $tenant->getDevelopmentUrl().$this->config->get('productSyncUrl');
            }
            if ($url) {
                try {
                    $client = new Client();
                    $response = $client->request('post', $url , [
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Sync-Type' => $syncType,
                            'Supplier-name' => $supplier,
                        ],
                        'body' => json_encode($ids),
                        'timeout' => 0.5 //setting this low because we don't care for response
                    ]);
                } catch (\GuzzleHttp\Exception\ConnectException $e) {
                    //do nothing
                } catch (\Exception $e) {
                    var_dump($e->getMessage());
                    $this->logger->error($e->getMessage());
                    $errors[] = 'Problem in syncing product/s to tenant: '.$tenant->getName();
                }
            }
        }
        if (count($errors) > 0) {
            throw new \Exception(implode(', ', $errors));
        }
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function syncProductsForCat($catId): void
    {
        $cat = $this->categoryRepo->getById($catId);
        $ids = [];
        if ($cat->getTenant() !== 0) {
            $mapping = $this->tenantCategoryMapperRepository->fetchAll(['tenantId' => $cat->getTenant(), 'mappedToId' => $catId]);
            foreach ($mapping as $map) {
                $catId = $map->getLocalCategory()->getId();
                $products = $this->productRepo->fetchAll(['category' => $catId]);
                foreach($products as $product) {
                    $ids[] = $product->getId();
                }
            }
            if (count($ids) > 0) {
                $this->syncProducts($ids);
                return;
            };
        }
        $products = $this->productRepo->fetchAll(['category' => $catId]);
        foreach($products as $product) {
            $ids[] = $product->getId();
        }
        if (count($ids) > 0) {
            $this->syncProducts($ids);
        }
    }

    /**
     * @throws GuzzleException
     * @throws NotFoundException
     */
    public function syncAllProductsInCatTree(int $catId): void
    {
        $this->syncProductsForCat($catId);
        $catChildren = $this->categoryRepo->fetchAll(['parent' => $catId]);
        if (count($catChildren) === 0) {
            return;
        }
        foreach ($catChildren as $child) {
            $this->syncAllProductsInCatTree($child->getId());
        }
    }
}