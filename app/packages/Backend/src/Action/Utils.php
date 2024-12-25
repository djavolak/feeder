<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Category\Mapper\CategoryMapper;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\PDORead;

class Utils
{
    public function __construct(private Product $productService, private PDORead $pdo, private CategoryMapper $categoryMapper)
    {
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $products = $this->getProducts();
        $productIds = [];
        if ($products) {
            foreach ($products as $product) {
               $productIds[] = $product['productId'];
                $this->changeProductCategoryToOriginalMapping($product);
                $this->changeProductsStatusToPrivate($product);
            }
            //save to csv
            $fp = fopen(DATA_PATH . '/products.csv', 'w');
            fputcsv($fp, $productIds);
            $this->productService->syncProducts($productIds, 'feed');
            fclose($fp);
        }
        return $response->withStatus(200, 'Done');
    }

    /**
     * @throws \Exception
     */
    private function changeProductCategoryToOriginalMapping($product): void
    {
        $mapping = $this->categoryMapper->fetchAll(['categoryMappingId' => $product['mappingId']]);
        if ($mapping) {
            $this->productService->updateField('category', $mapping[0]['categoryId'], $product['productId']);
        }
    }

    private function changeProductsStatusToPrivate($product): void
    {
        $this->productService->updateField('status', \EcomHelper\Product\Model\Product::STATUS_PRIVATE, $product['productId']);
    }

    /**
     * @description Get products that have a category that is not in the categoryMapping table and that are not in the parsingRuleCategory table
     * @return bool|array
     */
    private function getProducts(): bool|array
    {
        $sql = "SELECT * FROM product as p where category !=
      (SELECT categoryId from categoryMapping where categoryMappingId = p.mappingId) and (status = 1 or status = 2) 
      and p.category not in (SELECT categoryId from parsingRuleCategory where categoryId = p.category)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}