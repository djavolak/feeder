<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Product\Service\Product;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CheckForExpiredSales
{

    public function __construct(private Product $productService)
    {}
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $currentDate = new \DateTime();
        $currentDate->setTime(0, 0, 0);
        $productsWithExpiredSales = $this->productService->getEntities(['specialPriceTo' => $currentDate->format('Y-m-d')]);
        $idsToSync = [];
        foreach ($productsWithExpiredSales as $product) {
            if (!$product->getSalePriceLoop()) {
                $this->productService->updateField('specialPriceTo', null, $product->getId());
                $this->productService->updateField('specialPriceFrom', null, $product->getId());
                $this->productService->updateField('specialPrice', 0, $product->getId());
                $this->productService->updateField('fictionalDiscountPercentage', 0, $product->getId());
                $idsToSync[] = $product->getId();
                continue;
            }
            $specialPriceFrom = new \DateTime($product->getSpecialPriceFrom());
            $specialPriceToOriginal = new \DateTime($product->getSpecialPriceTo());
            $specialPriceTo = new \DateTime($product->getSpecialPriceTo());
            $interval = $specialPriceFrom->diff($specialPriceTo);
            $newSpecialPriceTo = $specialPriceTo->add($interval);
            $this->productService->updateField('specialPriceFrom', $specialPriceToOriginal->format('Y-m-d'), $product->getId());
            $this->productService->updateField('specialPriceTo', $newSpecialPriceTo->format('Y-m-d'), $product->getId());
            $idsToSync[] = $product->getId();
        }
        $this->productService->syncProducts($idsToSync);
        return $response->withStatus(200);
    }
}