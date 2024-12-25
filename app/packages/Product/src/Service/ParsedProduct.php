<?php
namespace EcomHelper\Product\Service;

use EcomHelper\Category\Service\CategoryMapper;
use EcomHelper\Feeder\Service\CategoryIgnoredException;
use EcomHelper\Product\Repository\ParsedProductRepository as Repository;
use EcomHelper\Product\Repository\ProductRepository;
use Skeletor\Blog\Service\UrlHelper;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\Session;
use EcomHelper\Product\Filter\Supplier as SupplierFilter;

class ParsedProduct extends TableView
{
    /**
     * @param Repository $clientRepo
     * @param Session $user
     * @param Logger $logger
     * @param SupplierFilter $filter
     */
    public function __construct(
        Repository $clientRepo, Session $user, Logger $logger, private Supplier $supplier, private ProductRepository $productRepo,
        private CategoryMapper $categoryMapper
    ) {
        parent::__construct($clientRepo, $user, $logger);
    }

    public function parse(\EcomHelper\Product\Model\ParsedProduct $parsedProduct, \EcomHelper\Product\Model\Supplier $supplier)
    {
        try {
            $productData = $this->parseProductData($parsedProduct, $supplier);
            if (!$productData['id']) {
                $productData['id'] = \Ramsey\Uuid\Uuid::uuid4();
                $product = $this->productRepo->create($productData);
            } else {
                $product = $this->productRepo->update($productData);
            }
        } catch (CategoryIgnoredException $e) {

        }

        var_dump('saved ' . $product->getTitle());
        die();
    }

    private function parseCategory(\EcomHelper\Product\Model\ParsedProduct $parsedProduct, \EcomHelper\Product\Model\Supplier $supplier)
    {
        $map = $this->categoryMapper->getEntities([
            'supplier' => $supplier->getId(),
            'source1' => $parsedProduct->getCat1() ?? '',
            'source2' => $parsedProduct->getCat2() ?? '',
            'source3' => $parsedProduct->getCat3() ?? '',
        ]);
        if (empty($map)) {
            var_dump('map not found');
            die();
        } else {
            if ($map[0]->getIgnored()) {
                throw new CategoryIgnoredException();
            }
            return [
                'categoryId' => $map[0]->getCategory()->getId(),
                'mappingId' => $map[0]->getId()
            ];
        }
        die('map not found');
    }

    private function parseProductData(\EcomHelper\Product\Model\ParsedProduct $parsedProduct, \EcomHelper\Product\Model\Supplier $supplier)
    {
        $product = $this->productRepo->fetchAll(['parsedProductId' => $parsedProduct->getId()]);
        if (empty($product)) {
            $catMapInfo = $this->parseCategory($parsedProduct, $supplier);
            $data = [
                'id' => null,
                'title' => $parsedProduct->getTitle(),
                'slug' => UrlHelper::slugify($parsedProduct->getTitle(), '-'),
                'price' => $parsedProduct->getInputPrice(),
                'category' => $catMapInfo['categoryId'],
                'mappingId' => $catMapInfo['mappingId'],
                'description' => $parsedProduct->getDescription(),
                'shortDescription' => '',
                'attributes' => [],
                'images' => [],
                'status' => 3,
                'stockStatus' => 0,
                'quantity' => $parsedProduct->getQuantity(),
                'specialPrice' => 0,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'salePriceLoop' => 0,
                'barcode' => $parsedProduct->getBarcode(),
                'sku' => $supplier->getSkuPrefix() . $parsedProduct->getSku(),
//                'ean' => $parsedProduct->getEan(),
                'ean' => '',
                'supplierProductId' => $parsedProduct->getSku(),
                'inputPrice' => $parsedProduct->getInputPrice(),
                'parsedProductId' => $parsedProduct->getId(),
                'supplierCategory' => $parsedProduct->getSupplierCategory(),
                'supplierId' => $parsedProduct->getSupplier()->getId(),
            ];
        } else {
            $product = $product[0];
            $data = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'slug' => $product->getSlug(),
                'price' => $product->getPrice(),
                'category' => null,
                'description' => $product->getDescription(),
                'shortDescription' => $product->getShortDescription(),
                'attributes' => [],
                'images' => [],
                'status' => $product->getStatus(),
                'stockStatus' => $product->getStockStatus(),
                'quantity' => $product->getQuantity(),
                'specialPrice' => $product->getSpecialPrice(),
                'specialPriceFrom' => $product->getSpecialPriceFrom(),
                'specialPriceTo' => $product->getSpecialPriceTo(),
                'salePriceLoop' => $product->getSalePriceLoop(),
                'barcode' => $product->getBarcode(),
                'sku' => $product->getSku(),
                'ean' => $product->getEan(),
                'supplierProductId' => $product->getSourceProductId(),
                'inputPrice' => $product->getInputPrice(),
                'parsedProductId' => $product->getParsedProductId(),
                'supplierCategory' => $product->getSupplierCategory(),
                'supplierId' => $product->getSupplier()->getId(),
            ];
        }

        return $data;
    }

    public function compileTableColumns()
    {
        $suppliers = $this->supplier->getEntities();
        $supplierFilter = [];
        foreach ($suppliers as $supplier) {
            $supplierFilter[$supplier->getId()] = $supplier->getName();
        }
        return [
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'inputPrice', 'label' => 'Input price'],
            ['name' => 'supplierCategory', 'label' => 'Supplier categories'],
            ['name' => 'barcode', 'label' => 'Barcode'],
            ['name' => 'quantity', 'label' => 'Quantity'],
            ['name' => 'supplier', 'label' => 'Supplier', 'filterData' => $supplierFilter],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at']
        ];
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $product) {
            $itemData = [
                'id' => $product->getId(),
                'title' => $product->getTitle(),
                'inputPrice' => $product->getInputPrice(),
                'supplierCategory' => $product->getSupplierCategory(),
                'barcode' => $product->getBarcode() ?? '',
                'quantity' => $product->getQuantity(),
                'supplier' => $product->getSupplier()->getName(),
                'createdAt' => $product->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $product->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $product->getId(),
            ];
        }
        return $items;
    }
}