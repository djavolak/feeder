<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SyncItemsForTenant
{
    public function __construct(private CategoryRepository $catRepo, private Product $productService)
    {
    }

    /**
     * @throws \Exception
     * @throws GuzzleException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $tenantId = $request->getAttribute('supplier');
        $categories = $this->catRepo->fetchAll(['tenantId' => $tenantId]);
        foreach ($categories as $category) {
            $this->productService->syncProductsForCat($category->getId());
        }
        return $response->withStatus(200, 'Done');
    }
}