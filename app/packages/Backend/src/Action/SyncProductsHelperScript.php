<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Product\Service\Product;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\PDOWrite;

class SyncProductsHelperScript
{
    public function __construct(private Product $productService, private PDOWrite $pdo)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $this->syncRemovedProducts();
//        $this->changeSalePriceDateForProducts();
        return $response->withStatus(200, 'Done');
    }

    private function syncRemovedProducts()
    {
        ini_set('memory_limit', '-1');
        $products = $this->productService->getEntities(['status' => \EcomHelper\Product\Model\Product::STATUS_SOURCE_REMOVED]);
        $ids = array_map(fn($product) => $product->getId(), $products);
        $this->productService->syncProducts($ids,  'feed');
    }

    private function changeSalePriceDateForProducts()
    {
        $sql = "SELECT productId FROM product WHERE specialPriceTo IS NOT NULL";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $products = $stmt->fetchAll();
        $sql2 = "UPDATE product set salePriceLoop = 1, specialPriceFrom = '2023-04-02 00:00:00', specialPriceTo = '2023-04-09 00:00:00'
where specialPriceFrom is not null;";
        $stmt2 = $this->pdo->prepare($sql2);
        $stmt2->execute();
        $ids = array_map(fn($product) => $product['productId'], $products);
        $this->productService->syncProducts($ids);
    }
}