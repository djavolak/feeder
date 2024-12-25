<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Mapper\AttributeValues;
use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Feeder\Service\ImageFetcher;
use EcomHelper\Image\Repository\ImageRepository;
use EcomHelper\Image\Service\Processor;
use EcomHelper\Product\Mapper\Image;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Service\Product;
use EcomHelper\Product\Service\Supplier;
use GuzzleHttp\Psr7\UploadedFile;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class ProductImportController extends AjaxCrudController
{
    const TITLE_VIEW = "View product";
    const TITLE_CREATE = "Create new product";
    const TITLE_UPDATE = "Edit product: ";
    const TITLE_UPDATE_SUCCESS = "product updated successfully.";
    const TITLE_CREATE_SUCCESS = "product created successfully.";
    const TITLE_DELETE_SUCCESS = "product deleted successfully.";
    const PATH = 'product';

    protected $tableViewConfig = [
        'writePermissions' => true,
        'useModal' => true,
        'draftable' => true,
        'bulkEditable' => true
    ];

    private $image;

    private $imageMapper;

    private $attribute;

    private $imageService;

    private $processor;

    private $imageRepository;

    /**
     * @param ProductRepository $repo
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     * @param Logger $logger
     */
    public function __construct(
        Product $service, Session $session, Config $config, Flash $flash, Engine $template, protected Category $categoryService,
        protected Supplier $supplierService, ImageFetcher $image, Image $imageMapper, Attribute $attribute, \EcomHelper\Image\Repository\ImageRepository $imageService,
        ImageRepository $imageRepository, Processor $processor,
        private \EcomHelper\Attribute\Repository\Attribute $attrRepo,
        private AttributeValues $attrValuesMapper
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
        $this->image = $image;
        $this->imageMapper = $imageMapper;
        $this->attribute = $attribute;
        $this->imageService = $imageService;
        $this->imageRepository = $imageRepository;
        $this->processor = $processor;
    }

    public function migrateImages()
    {
        ini_set('memory_limit', '1G');
        $limit = ['offset' => 0, 'limit' => 15000];
//        foreach ($this->service->getEntities([], $limit) as $product) {
        foreach ($this->service->getEntities(['supplierId' => 11]) as $product) {
            foreach ($this->imageMapper->fetchAll(['productId' => $product->getId()]) as $key => $row) {
                if ((int)$row['imageId'] > 0) {
                    continue;
                }
//                var_dump($row['main']);
//                var_dump($row['file']);
//                var_dump($row['productImagesId']);
//                die();
//                $filename = IMAGES_PATH . '/product/' . $product->getId() . substr($row['file'], strpos($row['file'], 'images/') + 6);
                $filename = IMAGES_PATH . '/product' . substr($row['file'], strpos($row['file'], 'images/') + 6);
                $file = new UploadedFile(fopen($filename, 'r'), filesize($filename), 0);

                $image = $this->imageRepository->create([
                    'type' => 1,
                    'mimeType' => 'jpg/jpeg',
                    'filename' => $this->processor->processUploadedFile($file),
                    'alt' => $data['alt'] ?? '',
                ]);
                $this->imageMapper->updateField('imageId', $image->getId(), $row['productImagesId']);

                var_dump($product->getId());
                var_dump($filename);


//                var_dump($this->service->getById($row['productId']));
//                die('done one');
            }
        }

        die('evo');
    }

    public function importAttributes($productId = null, $supplierId = 0)
    {
        $args = ['supplierId' => $supplierId];
        if ($productId) {
            $args['productId'] = $productId;
        }
        foreach ($this->service->getEntities($args) as $product) {
            foreach ($this->service->fetchAttributesForSku($product->getSku()) as $attribData) {
                $attributeName = $attribData['label'];
                $attribute = $this->attribute->getEntities(['attributeName' => $attributeName]);
                if (!isset($attribute[0])) {
                    $attribute = $this->attrRepo->create(['attributeName' => $attributeName]);
                } else {
                    $attribute = $attribute[0];
                }
                $value = $this->attribute->getAttributeValue($attribData['value'], $attribute->getId());;
                if (!isset($value[0])) {
                    $id = $this->attrValuesMapper->insert([
                        'attributeId' => $attribute->getId(),
                        'attributeValue' => $attribData['value']
                    ]);
                    $value = [
                        'attributeValue' => $attribData['value'],
                        'attributeValueId' => $id
                    ];
                } else {
                    $value = $value[0];
                }
                $this->service->saveExistingAttributes($product->getId(), $attribute->getId(),
                    $attribute->getAttributeName(), $value['attributeValue'], $value['attributeValueId']);
            }
        }
    }

    public function updateKnvItems()
    {
        foreach ($this->service->fetchOldData(0, 200, 'KNV') as $data) {
            $sku = $stockStatus = '';
            $regularPrice = $price = 0;
            foreach ($this->service->fetchOldMeta($data['ID']) as $meta) {
                if ($meta['meta_key'] === '_sku') {
                    $sku = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_regular_price') {
                    $regularPrice = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_price') {
                    $price = (int)$meta['meta_value'];
                }
                if ($meta['meta_key'] === '_stock_status') {
                    $stockStatus = $meta['meta_value'];
                }
            }
            $specialPrice = 0;
            if ($price !== $regularPrice) {
                $specialPrice = $price;
                $price = (int)$regularPrice;
            }
            $product = $this->service->getEntities(['sku' => $sku]);
            if (!isset($product[0])) { // delta items
//                echo $sku . ',';
                continue;
            }
            $product = $product[0];

            if ($product->getStockStatus() === 0 && $stockStatus === 'instock') {
                var_dump($sku);
                var_dump($stockStatus);
                die();
            }

            if ($product->getPrice() !== $price) {
                var_dump($specialPrice);
                var_dump($price);
                die();
            }
        }
    }

    public function createImage(\EcomHelper\Product\Model\Product $product, $postId)
    {
        foreach ($this->service->fetchOldMeta($postId) as $meta) {
            try {
                if ($meta['meta_key'] === '_thumbnail_id') {
                    $source = $this->service->getOldImagePath($meta['meta_value'])[0]['meta_value'];
                    $source = 'https://cdn.knv.rs/' . $source;
                    $path = $this->image->fetch($source, $product->getId());
//                $path = strstr($path, '/images');
                    $main = 1;

//                $file = new UploadedFile(fopen($filename, 'r'), filesize($filename), 0);

//                var_dump('main');
//                var_dump($path);
                    $image = $this->imageRepository->create([
                        'type' => 1,
                        'mimeType' => 'jpg/jpeg',
                        'filename' => $this->processor->processImage($path),
                        'alt' => $data['alt'] ?? '',
                    ]);
                    $this->imageMapper->insert([
                        'productId' => $product->getId(),
                        'main' => $main,
                        'imageId' => $image->getId(),
                        'file' => '',
                    ]);

//                var_dump($product->getId());
//                var_dump($path);
//                die();
                }
            } catch (\Exception $e) {
                var_dump('cannot process thumbnail with source: ' . $source);
            }

            if ($meta['meta_key'] === '_product_image_gallery') {
                foreach (explode(',', $meta['meta_value']) as $imageId) {
                    foreach ($this->service->getOldImagePath($imageId) as $source) {
//                        $source = $this->service->getOldImagePath($meta['meta_value'])[0]['meta_value'];
                        try {
                            $source = 'https://cdn.knv.rs/' . $source['meta_value'];
                            $path = $this->image->fetch($source, $product->getId());
//                        $path = strstr($path, '/images');
                            $main = 0;
//                        var_dump('gallery');
//                        var_dump($path);

//                        $filename = IMAGES_PATH . '/product' . substr($path, strpos($path, 'images/') + 6);
//                        var_dump($path);
//                        var_dump(substr($path, strpos($path, 'images/') + 6));
//                        var_dump(IMAGES_PATH . $path);
//                        die();

                            $image = $this->imageRepository->create([
                                'type' => 1,
                                'mimeType' => 'jpg/jpeg',
                                'filename' => $this->processor->processImage($path),
                                'alt' => $data['alt'] ?? '',
                            ]);
                            $this->imageMapper->insert([
                                'productId' => $product->getId(),
                                'main' => $main,
                                'imageId' => $image->getId(),
                                'file' => '',
                            ]);
                        } catch (\Exception $e) {
                            var_dump('cannot process gallery image with source: ' . $source);
                        }
                    }
                }
            }
        }
//        die('done one');

    }

    /**
     * imports konovo items from old wp tables; no attributes, no tags
     *
     * @return void
     */
    public function importData()
    {
        ini_set('max_execution_time', 1200);
        ini_set('max_input_time', 1200);
        foreach ($this->service->fetchOldData(0, 200, 'KNV') as $data) {
//            $category = $this->categoryService->getEntities(['title' => $this->service->fetchOldCategory($data['ID'])[0]['name'],
//                'tenantId' => 1]);
//            if (!isset($category[0])) {
//                var_dump($data['ID']);
//                die('no category');
//            }

            $sku = $stockStatus = $price = '';
            $regularPrice = '';
            $images = '';
            foreach ($this->service->fetchOldMeta($data['ID']) as $meta) {
                if ($meta['meta_key'] === '_sku') {
                    $sku = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_regular_price') {
                    $regularPrice = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_price') {
                    $price = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_stock_status') {
                    $stockStatus = $meta['meta_value'];
                }
            }

//            if (count($this->service->getEntities(['sku' => $sku]))) {
//                continue;
//            }

            $product = $this->service->getEntities(['sku' => $sku])[0] ?? null;
//            var_dump($product->getId());
//            if (!count($product->getImages()) > 0) {
//                $this->createImage($product, $data['ID']);
//            }

//            $deltaItems = 'KNV-910313,KNV-910312,KNV-910311,KNV-910310,KNV-910309,KNV-910308,KNV-910306,KNV-910305,KNV-910304,KNV-910303,KNV-910302,KNV-910301,KNV-910300';
//            $deltaItems = explode(',', $deltaItems);
//            if (!in_array($sku, $deltaItems)) {
//                continue;
//            }

            $specialPrice = 0;
            if ($price !== $regularPrice) {
                $specialPrice = $price;
                $price = $regularPrice;
            }

            $itemData = [
                'productId' => null,
                'title' => $data['post_title'],
                'slug' => $data['post_name'],
                'supplierId' => 11,
                'description' => $data['post_content'],
                'shortDescription' => $data['post_excerpt'],
                'inputPrice' => $price,
                'price' => $price,
                'specialPrice' => $specialPrice,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'status' => ($data['post_status'] === 'publish') ? \EcomHelper\Product\Model\Product::STATUS_PUBLISH : \EcomHelper\Product\Model\Product::STATUS_DRAFT,
                'stockStatus' => ($stockStatus === 'instock') ? \EcomHelper\Product\Model\Product::STOCK_STATUS_INSTOCK : \EcomHelper\Product\Model\Product::STOCK_STATUS_OUTSTOCK,
                'quantity' => 0,
                'weight' => 0,
                'supplierProductId' => $sku,
                'category' => 1,
                'barcode' => '',
                'ean' => '',
                'sku' => $sku,
                'attributes' => [],
                'newAttributes' => []
            ];
            //create product only if we didn't find it
            if (!$product) {
                $product = $this->service->createFromData($itemData);
                var_dump('---------inserted product ' . $product->getSku());
                $this->createImage($product, $data['ID']);
                $this->importAttributes($product->getId(), 11);
            }
//            var_dump($itemData);
//            die();


//            if (count($product)) {
//                $product = $product[0];
//
////                var_dump($data['post_title']);
////                var_dump($product->getTitle());
////                var_dump($data['post_title'] === $product->getTitle());
////                die();
//                if (false !== strpos($data['post_title'], $product->getTitle())) {
//                    if (!$product->getSlug()) {
//                        $this->service->updateField('slug', $data['post_name'], $product->getId());
//                        echo 'updated slug';
//                    }
////                    if ($product->getStatus() === \EcomHelper\Product\Model\Product::STATUS_PUBLISH
////                        && $data['post_status'] !== 'publish') {
////                        $this->service->updateField('status', 3, $product->getId()); // draft
////                    }
//                } else {
//                    var_dump($data['post_title']);
//                    var_dump($product->getTitle());
//                    die('mismatch ?');
//                }
//            }
        }
        die('done');
    }

    public function loopProducts()
    {
//        $filter = ['supplierId' => 10, 'status' => \EcomHelper\Product\Model\Product::STATUS_SOURCE_REMOVED];
//        $filter = ['supplierId' => 10, 'stockStatus' => \EcomHelper\Product\Model\Product::STOCK_STATUS_OUTSTOCK];
//        $filter = ['status' => \EcomHelper\Product\Model\Product::STATUS_PUBLISH];
        $filter = [];
        $items = $this->service->getEntities($filter);
        echo 'found total: ' . count($items);
        foreach ($items as $product) {
//            if ($product->getSupplier()->getId() === 11) {
//                continue;
//            }

//            $this->service->delete($product->getId());
        }

        die('done');
    }

    public function updateKnvAttributes()
    {
        ini_set('max_execution_time', 1200);
        ini_set('max_input_time', 1200);
        foreach ($this->service->fetchOldData(0, 300, 'KNV') as $data) {
            foreach ($this->service->fetchOldMeta($data['ID']) as $meta) {
                if ($meta['meta_key'] === '_sku') {
                    $sku = $meta['meta_value'];
                }
            }
            $product = $this->service->getEntities(['sku' => $sku])[0] ?? null;
            if ($product) {
                $this->importAttributes($product->getId());
            }
        }
    }

    public function importNewKnvItems()
    {
        ini_set('max_execution_time', 1200);
        ini_set('max_input_time', 1200);
        $supplierId = 11;
        foreach ($this->service->fetchOldData(0, 200, 'KNV') as $data) {
            $sku = $stockStatus = $price = '';
            $regularPrice = '';
            foreach ($this->service->fetchOldMeta($data['ID']) as $meta) {
                if ($meta['meta_key'] === '_sku') {
                    $sku = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_regular_price') {
                    $regularPrice = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_price') {
                    $price = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_stock_status') {
                    $stockStatus = $meta['meta_value'];
                }
            }
            $product = $this->service->getEntities(['sku' => $sku])[0] ?? null;
            if ($product) {
                continue;
            }
            $specialPrice = 0;
            if ($price !== $regularPrice) {
                $specialPrice = $price;
                $price = $regularPrice;
            }

            $itemData = [
                'productId' => null,
                'title' => $data['post_title'],
                'slug' => $data['post_name'],
                'supplierId' =>$supplierId,
                'description' => $data['post_content'],
                'shortDescription' => $data['post_excerpt'],
                'inputPrice' => $price,
                'price' => $price,
                'specialPrice' => $specialPrice,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'status' => ($data['post_status'] === 'publish') ? \EcomHelper\Product\Model\Product::STATUS_PUBLISH : \EcomHelper\Product\Model\Product::STATUS_DRAFT,
                'stockStatus' => ($stockStatus === 'instock') ? \EcomHelper\Product\Model\Product::STOCK_STATUS_INSTOCK : \EcomHelper\Product\Model\Product::STOCK_STATUS_OUTSTOCK,
                'quantity' => 0,
                'weight' => 0,
                'supplierProductId' => $sku,
                'category' => 1,
                'barcode' => '',
                'ean' => '',
                'sku' => $sku,
                'attributes' => [],
                'newAttributes' => []
            ];
                $product = $this->service->createFromData($itemData);
                var_dump('---------inserted product ' . $product->getSku());
                $this->createImage($product, $data['ID']);
                $this->importAttributes($product->getId(), 11);
        }
        die('done');
    }

    public function importNewPlItems()
    {
        ini_set('max_execution_time', 1200);
        ini_set('max_input_time', 1200);
        $supplierId = 12;
        foreach ($this->service->fetchOldData(0, 200, 'PL') as $data) {
            $sku = $stockStatus = $price = '';
            $regularPrice = '';
            foreach ($this->service->fetchOldMeta($data['ID']) as $meta) {
                if ($meta['meta_key'] === '_sku') {
                    $sku = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_regular_price') {
                    $regularPrice = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_price') {
                    $price = $meta['meta_value'];
                }
                if ($meta['meta_key'] === '_stock_status') {
                    $stockStatus = $meta['meta_value'];
                }
            }
            $product = $this->service->getEntities(['sku' => $sku])[0] ?? null;
            if ($product) {
                continue;
            }
            $specialPrice = 0;
            if ($price !== $regularPrice) {
                $specialPrice = $price;
                $price = $regularPrice;
            }

            $itemData = [
                'productId' => null,
                'title' => $data['post_title'],
                'slug' => $data['post_name'],
                'supplierId' => $supplierId,
                'description' => $data['post_content'],
                'shortDescription' => $data['post_excerpt'],
                'inputPrice' => $price,
                'price' => $price,
                'specialPrice' => $specialPrice,
                'specialPriceFrom' => null,
                'specialPriceTo' => null,
                'status' => ($data['post_status'] === 'publish') ? \EcomHelper\Product\Model\Product::STATUS_PUBLISH : \EcomHelper\Product\Model\Product::STATUS_DRAFT,
                'stockStatus' => ($stockStatus === 'instock') ? \EcomHelper\Product\Model\Product::STOCK_STATUS_INSTOCK : \EcomHelper\Product\Model\Product::STOCK_STATUS_OUTSTOCK,
                'quantity' => 0,
                'weight' => 0,
                'supplierProductId' => $sku,
                'category' => 1,
                'barcode' => '',
                'ean' => '',
                'sku' => $sku,
                'attributes' => [],
                'newAttributes' => []
            ];
                $product = $this->service->createFromData($itemData);
                var_dump('---------inserted product ' . $product->getSku());
                $this->importAttributes($product->getId(), 12);
                $this->createImage($product, $data['ID']);
        }
        die('done');
    }
}