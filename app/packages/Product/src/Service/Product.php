<?php
namespace EcomHelper\Product\Service;

use EcomHelper\Attribute\Mapper\SecondDescriptionAttributeCategory;
use EcomHelper\Category\Repository\TenantCategoryMapperRepository;
use EcomHelper\Feeder\Mapper\ProductSupplierAttributes;
use EcomHelper\Tenant\Service\Tenant;
use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Model\Product as ProductModel;
use EcomHelper\Product\Repository\ProductRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Tag\Service\Tag;
use Skeletor\User\Service\Session;
use EcomHelper\Product\Filter\Product as ProductFilter;

class Product extends TableView
{
    /**
     * @param ProductRepository $repo
     * @param User $user
     * @param Logger $logger
     * @param ProductFilter $filter
     * @param ActivityRepository $activity
     * @param Supplier $supplier
     * @param Attribute $attributeService
     * @param Category $category
     * @param Image $imageService
     * @param \EcomHelper\Product\Mapper\Image $imageMapper
     * @param Config $config
     */
    public function __construct(
        ProductRepository $repo, Session $user, Logger $logger, ProductFilter $filter, private Supplier $supplier,
        private Category $category, private Tag $tag,
        private Config $config, private Tenant $tenantService

//        private Attribute $attributeService,
//        private Image $imageService,
//        private \EcomHelper\Product\Mapper\Image $imageMapper,
//        private TenantCategoryMapperRepository $tenantCategoryMapperRepository,
//        private SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper,
//        private ProductSupplierAttributes $productSupplierAttributes
    ) {
        parent::__construct($repo, $user, $logger, $filter);
    }

    public function getEntityData($id)
    {
        return $this->formatEntityData($this->repo->getById($id));
    }


    protected function formatEntityData(ProductModel $product)
    {
        $img = '';
        if (isset($product->getImages()[0])) {
            $image = $product->getImages()[0];
            $img = sprintf('<img alt="featured image" width="50px" src="/images/%s" />', $image->getFile());
        }
        // @todo move outside the loop
        $feederUrl = '';
        $environment = $this->config->get('environment') ?? null;
        $productSyncUrl = $this->config->get('productSyncUrl') ?? null;
        if ($environment === 'production' && $productSyncUrl) {
            if ($this->config->get('environment') === 'production') {
                $feederUrl = "$productSyncUrl?id=" . $product->getId();
                $feederUrl = ''; //@todo fix this
            }
        }

        $price = $product->getPrice();
        $specialPrice = $product->getSpecialPrice();
        $inputPrice = $product->getInputPrice();
        $priceHtml = $product->getSpecialPrice() !== 0 ?
            '<span><del style="opacity:0.7">' . $price .'</del><p>' . $specialPrice .'</p></span>' :
            $price;
        $fictionalPriceHtml = null;
        if ($product->getFictionalDiscountPercentage()) {
            $price = $product->getPrice() / ((100-$product->getFictionalDiscountPercentage())/100);
            $price = ceil($price / 10) * 10;
            $fictionalPriceHtml = '<span><del style="opacity:0.7">' . $price .'</del><p>' . $product->getPrice() .'</p></span>';
            $profit = $product->getPrice() - $inputPrice;
        } else {
            $profit = $product->getSpecialPrice() === 0 ? $price -  $inputPrice :
                $specialPrice - $inputPrice;
        }
        return [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'supplierId' => sprintf('<a target="_blank" href="%s">%s</a>', '/supplier/view/?supplierId=' . $product->getSupplier()->getId(),
                                    $product->getSupplier()->getName()),
            'fictionalDiscountPercentage' => $fictionalPriceHtml ?? 0,
            'price' => $priceHtml,
            'inputPrice' => $inputPrice,
            'profit' => $profit,
            'image' => $img,
            'status' => ProductModel::getHRStatus($product->getStatus()),
            'stockStatus' => sprintf('<span class="%s">%s</span>',strtolower(ProductModel::getHRStockStatus($product->getStockStatus())),ProductModel::getHRStockStatus($product->getStockStatus())),
            'category' => '<a data-cat-id="' .  $product->getCategory()->getId() .'" href="" class="categoryLink">' . $product->getCategory()->getName()  . '</a>',
            'description' => $product->getDescription(),
            'barcode' => $product->getBarcode(),
            'ean' => $product->getEan(),
            'slug' => $product->getSlug(),
            'sku' => $product->getSku(),
            'viewMap' => sprintf('<a target="_blank" href="/category-mapper/internalMapping/%s/%s/" title="View map">Map</a>',
                $product->getSupplier()->getId(), $product->getMappingId()),
            'updatedAt' => $product->getUpdatedAt()->format('m.d.Y'),
            'createdAt' => $product->getCreatedAt()->format('m.d.Y'),
            'tags' => '',
            'mappingId' => $product->getMappingId()
        ];
    }

    /**
     * @param $search
     * @param $filter
     * @param $offset
     * @param $limit
     * @param $order
     * @param null $uncountableFilter
     * @param array $idsToInclude
     * @param array $idsToExclude
     * @return array
     */
    public function fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter = null, $idsToInclude = [], $idsToExclude = [])
    {
        if (!$order) {
            $order = ['createdAt' => 'DESC'];
        }
        $idsToInclude = [];
        $idsToExclude = [];
        if(isset($filter['includeAttributes'])) {
            $includeAttributes = $filter['includeAttributes'];
            unset($filter['includeAttributes']);
            foreach ($this->productAttributeValuesMapper->fetchProductIdsIncludingAttributes($includeAttributes) as $productId) {
                $idsToInclude[] = $productId;
            }
        }
        if(isset($filter['excludeAttributes'])) {
            $excludeAttributes = $filter['excludeAttributes'];
            unset($filter['excludeAttributes']);
            foreach ($this->productAttributeValuesMapper->fetchProductIdsIncludingAttributes($excludeAttributes) as $productId) {
                $idsToExclude[] = $productId;
            }
        }
        $data = $this->repo->fetchTableData($search, $filter, $offset, $limit, $order, null, $idsToInclude, $idsToExclude);
        $items = [];
        if(isset($data['items'])) {
            foreach ($data['items'] as $product) {
                $columns = $this->formatEntityData($product);
                $columns['title'] = [
                    'value' => $product->getTitle(),
                    'editColumn' => true
                ];
                $items[] = [
                    'columns' => $columns,
                    'id' => $product->getId()
                ];
            }
        }

        return [
            'count' => $data['count'],
            'entities' => $items,
        ];
    }

    public function compileTableColumns()
    {
        $supplierFilter = [];
        foreach ($this->supplier->getEntities() as $supplier) {
            $supplierFilter[$supplier->getId()] = $supplier->getName();
        }
        $tagFilter = [];
        foreach ($this->tag->getEntities() as $tag) {
            $tagFilter[$tag->getId()] = $tag->getTitle();
        }
        $stockFilter = [
            ProductModel::STOCK_STATUS_INSTOCK => 'Instock',
            ProductModel::STOCK_STATUS_OUTSTOCK => 'Outofstock',
        ];
        $categories = [];
        foreach ($this->category->getEntities(['tenant' => null], null, ['title' => 'asc']) as $category) {
            $categories[$category->getId()] = $category->getName();
        }
//        $mappingIdsFilter = $this->repo->getMappingIds();
        $statusFilter = [];
        $numberOfProductsPerStatus = $this->repo->getNumberOfProductsPerStatus();
        $statusFilter['0'] = 'All'; // 0 will be used to exclude removed status from search
        $filterCountValues[] = 0;
        foreach($numberOfProductsPerStatus as $numberOfProducts) {
            $filterCountValues[0] += $numberOfProducts['count'];
            $filterCountValues[] = $numberOfProducts['count'];
            $statusFilter[$numberOfProducts['status']] = ProductModel::getHRStatus($numberOfProducts['status']);
            // if status removed reduce all count by removed count
            if($statusFilter[$numberOfProducts['status']] === ProductModel::getHRStatus(ProductModel::STATUS_SOURCE_REMOVED)) {
                $filterCountValues[0] -= $numberOfProducts['count'];
            }
        }

        $columnDefinitions = [
            ['name' => 'image', 'label' => 'Image'],
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'sku', 'label' => 'Sku'],
            ['name' => 'stockStatus', 'label' => 'Stock', 'filterData' => $stockFilter],
            ['name' => 'status', 'label' => 'Status', 'filterData' => $statusFilter],
            ['name' => 'fictionalDiscountPercentage', 'label' => 'Fict Price'],
            ['name' => 'price', 'label' => 'Price'],
            ['name' => 'inputPrice', 'label' => 'Input price'],
            ['name' => 'profit', 'label' => 'Profit'],
            ['name' => 'supplierId', 'label' => 'Supplier', 'filterData' => $supplierFilter],
            ['name' => 'category', 'label' => 'Category', 'filterData' => $categories],
            ['name' => 'updatedAt', 'label' => 'Updated'],
            ['name' => 'createdAt', 'label' => 'Created'],
            ['name' => 'tags', 'label' => 'Tag filter', 'filterData' => $tagFilter],
//            ['name' => 'mappingId', 'label' => 'Map filter', 'filterData' => $mappingIdsFilter], // @TODO should be hidden ?
        ];

//        $columnDefinitions = [
//            (new Column('status', 'Status'))->addViewParam('filterable', true)
//                ->addViewParam('select', false)
//                ->addViewParam('itemCount', $filterCountValues)
//                ->addViewParam('filterValues', $statusFilter),
//        ];

        return $columnDefinitions;
    }

    public function fetchOldData($offset, $limit, $skuPrefix = '')
    {
        return $this->repo->fetchOldData($offset, $limit, $skuPrefix);
    }

    public function createFromData($data)
    {
        return $this->repo->create($data);
    }

    public function updateField($field, $value, $entityId)
    {
        return $this->repo->updateField($field, $value, $entityId);
    }

    public function updateStatus($status, $entityId)
    {
        return $this->repo->updateField('status', $status, $entityId);
    }

    public function getAttributes($search, $limit)
    {
        return $this->attributeService->getAttributesForSearch($search, $limit);
    }

    public function getAttributesWithoutValues()
    {

        return $this->attributeService->getAttributesWithoutValues();
    }

    public function getProductAttributeValues($id)
    {
        return $this->repo->getProductAttributeValues($id);
    }

    public function fetchAttributesForSku($sku)
    {
        return $this->repo->fetchAttributesForSku($sku);
    }

    public function saveExistingAttributes($productId, $attributeId, $attributeName, $attributeValue, $attributeValueId)
    {
        $this->repo->saveExistingAttributes($productId, $attributeId, $attributeName, $attributeValue, $attributeValueId);
    }

    public function getAttributeGroups()
    {
        return $this->attributeService->getAttributeGroups();
    }

    public function getGroupAttributesByGroupId($id)
    {
        return $this->attributeService->getGroupAttributesByGroupId($id);
    }

    public function copyAsDraft($id)
    {
        return $this->repo->copyAsDraft($id);
    }

    /**
     * @throws GuzzleException
     */
    public function bulkEdit($data): array
    {
        $ids = [];
        if(isset($data['ids'])) {
            $ids = $data['ids'];
            $persistentIds = $ids;
            $status = $data['status'];
            $stockStatus = $data['stockStatus'];
            $category = $data['category'];
            $salePricePercentage = $data['specialPricePercentage'];
            $fictionalSalePercentage = $data['fictionalDiscountPercentage'];
            $salePriceFrom = $data['specialPriceFrom'];
            $salePriceTo = $data['specialPriceTo'];
            if (isset($data['salePriceLoop']) && $data['salePriceLoop'] !== '-1') {
                $salePriceLoop = (int)$data['salePriceLoop'];
            } else {
                $salePriceLoop = null;
            }
            if(isset($data['deletedTagBulk']) && count($data['deletedTagBulk']) > 0 && count($persistentIds) > 0) {
                $deletedTagIds = implode(',', $data['deletedTagBulk']);
                $productIdsString = implode(',', $persistentIds);
                $this->productTagMapper->bulkDeleteByProductIdsAndTagIds($deletedTagIds, $productIdsString);
            }

            if(isset($data['tagsToBeAdded']) && count($data['tagsToBeAdded']) > 0 && count($persistentIds) > 0) {
                foreach($persistentIds as $persistentId) {
                    foreach($data['tagsToBeAdded'] as $tagIdToBeAdded => $tagName) {
                        if(!$this->productTagMapper->doesProductHaveTag($persistentId, $tagIdToBeAdded)) {
                            $this->productTagMapper->insert([
                                'productId' => $persistentId,
                                'tagId' => $tagIdToBeAdded,
                                'tagTitle' => $tagName
                            ]);
                        }
                    }
                }
            }

            foreach ($ids as $key => $id) {
                $oldModel = $this->repo->getById((int)$id);
                if ($status !== '-1') {
                    $this->repo->updateField('status', (int)$status, $id);
                }
                if ($salePriceLoop && ($salePriceLoop !== $oldModel->getSalePriceLoop())) {
                    $this->repo->updateField('salePriceLoop', (int)$salePriceLoop, $id);
                }
                if ($stockStatus !== '-1') {
                    $this->repo->updateField('stockStatus', (int)$stockStatus, $id);
                }
                if($category !== '-1') {
                    $this->repo->updateField('category', (int)$category, $id);
                }
                if(isset($data['attributeId'],$data['attributeValueId'])) {
                    $idsString = implode(',',$ids);
                    foreach($data['attributeId'] as $key => $attributeId) {
                        $attributeValueId = $data['attributeValueId'][$key];
                        $this->attributeService->bulkDeleteProductAttributes($attributeId, $attributeValueId, $idsString);
                    }
                }
                if(isset($data['attribute'])) {
                    $this->attributeService->saveAttributesForProduct($id, $data['attribute']);
                }
                // Update second description
                if($category === '-1') {
                    $categoryForSecondDesc = $oldModel->getCategory()->getId();
                }
                if(isset($categoryForSecondDesc) && !isset($attributeIds[(int)$categoryForSecondDesc])) {
                    $attributeIds[(int)$categoryForSecondDesc] = $this->secondDescriptionAttributeCategoryMapper->getAttributeIdsByCategoryId((int)$categoryForSecondDesc);
                }

                if(isset($categoryForSecondDesc) && isset($attributeIds[(int)$categoryForSecondDesc])) {
                    $dataAttIds = [];
                    foreach($attributeIds[(int)$categoryForSecondDesc] as $attId) {
                        $dataAttIds[] = $attId['attributeId'];
                    }
                    $this->saveSecondDescriptionForProduct($dataAttIds, $id);
                }
                if ($salePricePercentage !== '' ) {
                    $this->handleSalePrice((int)$id, (string)$salePricePercentage, $salePriceFrom, $salePriceTo, false);
                }
                if ($fictionalSalePercentage !== '' ) {
                    $this->handleSalePrice((int)$id, (int)$fictionalSalePercentage, $salePriceFrom,$salePriceTo, true);
                }
                if((int)$status !== ProductModel::STATUS_PUBLISH && $oldModel->getStatus() !== ProductModel::STATUS_PUBLISH) {
                    unset($ids[$key]);
                }
            }
        }
        return $ids;
    }

    private function saveSecondDescriptionForProduct($attributeIds, $productId)
    {
        $html = '<table class="woocommerce-product-attributes shop_attributes"><tbody>';
        $attributes = $this->getProductAttributeValues($productId);
        $attributesFormatted = [];
        foreach($attributes as $attribute) {
            $attributesFormatted[$attribute['attributeId']][] = $attribute;
        }
        foreach($attributeIds as $attributeId) {
            if(isset($attributesFormatted[$attributeId])) {
                $html .= '<tr><th>' . $attributesFormatted[$attributeId][0]['attributeName'] .'</th>';
                $html .= '<td>';
                $count = count($attributesFormatted[$attributeId]);
                foreach($attributesFormatted[$attributeId] as $key => $att) {
                    $html .= $att['attributeValue'];
                    if ($key+1 !== $count) {
                        $html .= ', ';
                    }
                }
                $html .= '</td></tr>';
            }
        }
        $html .= '</tbody></table>';
        $this->updateField('shortDescription', $html, $productId);
    }

    /**
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
//    public function update(Request $request)
//    {
//        if ($this->filter) {
//            $data = $this->filter->filter($request);
//        } else {
//            $data = $request->getParsedBody();
//        }
//        $oldModel = $this->repo->getById((int) $request->getAttribute('id'));
//
//        if($data['deletedImages'] !== []) {
//            foreach($data['deletedImages'] as $deletedImageId) {
//                $this->imageMapper->updateField('productId', 0, $deletedImageId);
//            }
//        }
//        unset($data['deletedImages']);
//
//        $tags = $data['tags'];
//        unset($data['tags']);
//        $deletedTags = $data['deletedTags'];
//        unset($data['deletedTags']);
//
//        if($tags) {
//            foreach($tags as $tagId => $tagTitle) {
//                $this->productTagMapper->insert(['productId' => $oldModel->getId(), 'tagId' => (int)$tagId, 'tagTitle' => $tagTitle]);
//            }
//        }
//        if($deletedTags) {
//            foreach($deletedTags as $deletedTagId) {
//                $this->productTagMapper->deleteTagProduct($oldModel->getId(), $deletedTagId);
//            }
//        }
//       if($data['images']['featuredImage']) {
//           $featuredImage = $this->imageService->createFeatured($request);
//           if($featuredImage) {
//               $oldMainImages = $this->imageMapper->fetchAll(['productId' => $data['productId'], 'main' => 1]);
//               foreach($oldMainImages as $oldMainImage) {
//                   $this->imageMapper->delete($oldMainImage['productImagesId']);
//               }
//               $imageData = [
//                   'imageId' => $featuredImage->getid(),
//                   'productId' => $data['productId'],
//                   'file' => $featuredImage->getFilename(),
//                   'main' => 1,
//                   'sort' => 0,
//               ];
//               $this->imageMapper->insert($imageData);
//           }
//
//       }
//       if(count($data['existingImages']) > 0) {
//           foreach($data['existingImages'] as $imageId => $position) {
//               $this->imageMapper->updateField('sort', $position, $imageId);
//           }
//       }
//       if(count($data['images']['galleryImages']) > 0) {
//            $images = $this->imageService->createGallery($request);
//           if (count($images) > 0) {
//               foreach ($images as  $image) {
//                   $md5 = preg_replace('/\/[\s\S]+\//', '', $image->getFileName());
//                   $md5 = str_replace(['.jpg', '.jpeg', '.png', '.webp'], '', $md5);
//                   $sort = 0;
//                   if(count($data['newImages']) > 0) {
//                       foreach($data['newImages'] as $newName => $newPosition) {
//                           if(md5($newName) === $md5) {
//                               $sort = $newPosition;
//                           }
//                       }
//                   }
//                   $imageData = [
//                       'imageId' => $image->getid(),
//                       'productId' => $data['productId'],
//                       'file' => $image->getFilename(),
//                       'main' => 0,
//                       'sort' => $sort,
//                   ];
//                   $this->imageMapper->insert($imageData);
//               }
//           }
//       }
//       unset($data['existingImages']);
//       unset($data['newImages']);
//
//        $data['images'] = ''; /** @depreciated**/
//        $model = $this->repo->update($data);
//        $this->createActivity($model, $oldModel);
//        if($oldModel->getStatus() !== ProductModel::STATUS_PUBLISH &&
//            $model->getStatus() !== ProductModel::STATUS_PUBLISH) {
//            return $model;
//        }
//        $this->syncProducts([$model->getId()]);
//        return $model;
//    }

    /**
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
//    public function create(Request $request)
//    {
//        if ($this->filter) {
//            $data = $this->filter->filter($request);
//        } else {
//            $data = $request->getParsedBody();
//        }
//        $newImages = $data['newImages'];
//        unset($data['deletedImages']);
//        unset($data['existingImages']);
//        unset($data['newImages']);
//        $tags = $data['tags'];
//        unset($data['tags']);
//        unset($data['deletedTags']);
//        $dataImages = $data['images'];
//        $data['images'] = ''; /** @depreciated**/
//        $model = $this->repo->create($data);
//
//        if($tags) {
//            foreach($tags as $tagId => $tagTitle) {
//                $this->productTagMapper->insert(['productId' => $model->getId(), 'tagId' => (int)$tagId, 'tagTitle' => $tagTitle]);
//            }
//        }
//        if($dataImages['featuredImage']) {
//            $featuredImage = $this->imageService->createFeatured($request);
//            if($featuredImage) {
//                $imageData = [
//                    'imageId' => $featuredImage->getid(),
//                    'productId' => $model->getId(),
//                    'file' => $featuredImage->getFilename(),
//                    'main' => 1,
//                    'sort' => 0,
//                ];
//                $this->imageMapper->insert($imageData);
//            }
//        }
//        if(isset($dataImages['galleryImages']) && count($dataImages['galleryImages']) > 0) {
//            $images = $this->imageService->createGallery($request);
//            if (count($images) > 0) {
//                foreach ($images as  $image) {
//                    $md5 = preg_replace('/\/[\s\S]+\//', '', $image->getFileName());
//                    $md5 = str_replace(['.jpg', '.jpeg', '.png', '.webp'], '', $md5);
//                    $sort = 0;
//                    if(count($newImages) > 0) {
//                        foreach($newImages as $newName => $newPosition) {
//                            if(md5($newName) === $md5) {
//                                $sort = $newPosition;
//                            }
//                        }
//                    }
//                    $imageData = [
//                        'imageId' => $image->getid(),
//                        'productId' => $model->getId(),
//                        'file' => $image->getFilename(),
//                        'main' => 0,
//                        'sort' => $sort,
//                    ];
//                    $this->imageMapper->insert($imageData);
//                }
//            }
//        }
//        $this->createActivity($model);
//        if($model->getStatus() === ProductModel::STATUS_PUBLISH) {
//            $this->syncProducts([$model->getId()]);
//        }
//        return $model;
//    }

    /**
     * @param array $ids
     * @return void
     * @throws GuzzleException
     * @throws \Exception
     */
    public function syncProducts(array $ids, $syncType = 'manual', $supplier = null)
    {
        $environment = $this->config->get('environment') ?? null;
        if (!$environment) {
            return;
        }
        $tenants = $this->tenantService->getEntities();
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
     */
//    public function delete(int $id)
//    {
//        $model = $this->repo->getById($id);
//        $this->productTagMapper->deleteByProductId($id);
//        $this->productSupplierAttributes->deleteBy('productId', $id);
//        $this->productSupplierAttributes->deleteBy('sku', $model->getSku());
//        $this->createActivity(null, $model);
//        $this->repo->delete($id);
//        $this->syncProducts([$id]);
//    }

    /**
     * @throws GuzzleException
     */
    public function syncProductsForCat($catId)
    {
        $cat = $this->category->getById($catId);
        $ids = [];
        if ($cat->getTenant() !== 0) {
            $mapping = $this->tenantCategoryMapperRepository->fetchAll(['tenant' => $cat->getTenant(), 'mappedToId' => $catId]);
            foreach ($mapping as $map) {
                $catId = $map->getLocalCategory()->getId();
                $products = $this->repo->fetchAll(['category' => $catId]);
                foreach($products as $product) {
                    $ids[] = $product->getId();
                }
            }
            if (count($ids) > 0) {
                $this->syncProducts($ids);
                return;
            };
        }
        $products = $this->repo->fetchAll(['category' => $catId]);
        foreach($products as $product) {
            $ids[] = $product->getId();
        }
        if (count($ids) > 0) {
            $this->syncProducts($ids);
        }
    }

    /**
     * @throws GuzzleException
     */
    public function syncAllProductsInCatTree(int $catId)
    {
        $this->syncProductsForCat($catId);
        $catChildren = $this->category->getEntities(['parent' => $catId]);
        if (count($catChildren) === 0) {
            return;
        }
        foreach ($catChildren as $child) {
            $this->syncAllProductsInCatTree($child->getId());
        }
    }

    public function getProductIdsByCategoryId($categoryId) {
        return $this->repo->getProductIdsByCategoryId((int)$categoryId);
    }

    private function handleSalePrice(int $id, int $salePricePercentage,  string $salePriceFrom,  string $salePriceTo,
        bool $fictional)
    {
        $product = $this->repo->getById($id);
        if ($fictional) {
            //if fictional discount is set we update that field and set sale price to 0
            $this->updateField('fictionalDiscountPercentage', $salePricePercentage, $id);
            $this->updateField('specialPrice', 0, $id);
        } else {
            //if fictional discount is not set we update sale price and set fictional discount to 0
            $this->updateField('fictionalDiscountPercentage', 0, $id);
            if ($salePricePercentage > 0) {
                $this->updateField('specialPrice', $product->getPrice() - ($product->getPrice() * $salePricePercentage / 100), $id);
            } else {
                $this->updateField('specialPrice', 0, $id);
            }
        }
        if ($salePriceFrom !== '') {
            $this->updateField('specialPriceFrom', $salePriceFrom, $id);
        } else {
            $this->updateField('specialPriceFrom', null, $id);
        }
        if ($salePriceTo !== '') {
            $this->updateField('specialPriceTo', $salePriceTo, $id);
        } else {
            $this->updateField('specialPriceTo', null, $id);
        }
    }

    public function getProductCountForMap($mapId)
    {
        return $this->repo->getProductCountForMap($mapId);
    }
}