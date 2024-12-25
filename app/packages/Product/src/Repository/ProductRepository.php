<?php
namespace EcomHelper\Product\Repository;

use Doctrine\ORM\EntityManagerInterface;
use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Category\Mapper\CategoryMapper;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Model\Product as Model;
use Skeletor\Core\TableView\Repository\TableViewRepository;
use Skeletor\Core\Mapper\NotFoundException;

class ProductRepository extends TableViewRepository
{
    const ENTITY = \EcomHelper\Product\Entity\Product::class;
    const FACTORY = \EcomHelper\Product\Factory\ProductFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getBySupplierId($supplierId, $supplierProductId)
    {
        $items = $this->fetchAll(['supplierId' => $supplierId, 'supplierProductId' => $supplierProductId]);
        if (count($items)) {
            return $items[0];
        }
        return false;
    }

    public function getSupplierCodes(int $supplierId): array
    {
        $data = [];
        foreach ($this->mapper->fetchSupplierCodesExcludingStatus($supplierId, Product::STATUS_SOURCE_REMOVED) as $product) {
            $data[] = $product['supplierProductId'];
        }

        return $data;
    }

    public function removedFromFeed($supplierProductId, $supplierId)
    {
        $item = $this->mapper->fetchAll(['supplierProductId' => $supplierProductId, 'supplierId' => $supplierId]);
        if (!isset($item[0])) {
            throw new \Exception('Could not find product for supplierProductId: ' . $supplierProductId);
        }
        $id = $item[0]['productId'];
        $this->mapper->updateField('status', \EcomHelper\Product\Model\Product::STATUS_SOURCE_REMOVED, $id);
        $this->mapper->updateField('stockStatus', 0, $id);

        return $id;
    }

    public function updateStatus($supplierProductId, $status)
    {
        $item = $this->mapper->fetchAll(['supplierProductId' => $supplierProductId]);
        if (!isset($item[0])) {
            throw new \Exception('Could not find product for supplierProductId: ' . $supplierProductId);
        }
        $id = $item[0]['productId'];
        $this->mapper->updateField('status', $status, $id);

        return $id;
    }

    public function make($itemData): Model
    {
        $data = [];
        foreach ($itemData as $name => $value) {
            if (in_array($name, ['createdAt', 'updatedAt'])) {
                $data[$name] = null;
                if ($value) {
                    if (strtotime($value)) {
                        $dt = clone $this->dt;
                        $dt->setTimestamp(strtotime($value));
                        $data[$name] = $dt;
                    } else {
                        $data[$name] = null;
                    }
                }
            } else {
                $data[$name] = $value;
            }
        }

        $data['attributes'] = [];
        if (isset($data['productId'])) {
            $images = [];
            $imageRows = [];
            if (in_array('mainImage', $this->returnRelations) || count($this->returnRelations) === 0) {
                $imageRows = $this->imageMapper->fetchMainImage($data['productId']);
            }
            if (in_array('galleryImages', $this->returnRelations) || count($this->returnRelations) === 0) {
                $imageRows = array_merge($imageRows, $this->imageMapper->fetchGalleryImages($data['productId']));
            }
            if (count($imageRows)) {
                foreach ($imageRows as $imageData) {
                    $image = new \EcomHelper\Product\Model\Image(...[
                        'productImageId' => $imageData['productImagesId'],
                        'imageId' => $imageData['imageId'],
                        'productId' => $imageData['productId'],
                        'file' => $imageData['filename'],
                        'isMain' => $imageData['main'],
                        'sort' => 0,
                    ]);
                    $images[] = $image;
                }
            }
            $data['productId'] = (int) $data['productId'];
            if (in_array('attributes', $this->returnRelations) || count($this->returnRelations) === 0) {
                $data['attributes'] = $this->productAttributeValuesMapper->getOrderedAttributes($data['productId']);
            }

        }
        $data['supplier'] = $this->supplierRepo->getById($data['supplierId']);
        unset($data['supplierId']);
        $data['images'] = $images;
        $data['category'] = $this->catRepo->getById($data['category']);
        $data['price'] = (int) $data['price'];
        $data['specialPrice'] = (int) $data['specialPrice'];
        $data['inputPrice'] = (int) $data['inputPrice'];

        return new Model(...$data);
    }

    public function fetchFromCategories($categoryIds, $tenantId)
    {
        $key = sprintf('productList#%s#%s',  $tenantId, serialize($categoryIds));
        $ttl = 60 * 30;
        $items = $this->cache->get($key);
        // @TODO disabled cache
        $items = false;
        if ($items === false) {
            $items = [];
            $children = $this->catRepo->fetchChildrenIds($categoryIds);
            $categoryIds = array_merge($categoryIds, $children, ($children) ? $this->catRepo->fetchChildrenIds($children) : []);
            $categoryIds = $this->categoryMapper->getSourceIds($categoryIds, $tenantId);
            foreach ($this->mapper->fetchFromCategories($categoryIds) as $data) {
                $items[] = $this->make($data);
            }
            $this->cache->set($key, serialize($items), $ttl);
        } else {
            $items = unserialize($items);
        }

        return $items;
    }

    public function getSearchableColumns(): array
    {
        return ['title', 'sku', 'ean', 'barcode', 'productId'];
    }

    public function fetchTableDataExtended($search, $filter, $offset, $limit, $order, $includeAttributes = null, $excludeAttributes = null)
    {
        if(isset($order['orderBy']) && $order['orderBy'] === 'id') { // @todo fix this, should be ID in model/DB etc...
            $order['orderBy'] = 'productId';
        }

        $idsToIncludeActive = false;

        if (isset($filter['tags'])) { // @TODO filter by tag ids
            $tagId = (int)$filter['tags'];
            $idsToIncludeActive = true;
            unset($filter['tags']);
        }

        if(isset($filter['includeChildCategories'], $filter['category'])) {
            $categoryId = (int)$filter['category'];
            unset($filter['category']);
            unset($filter['includeChildCategories']);
            $categoryIds = [];
            $childCategories = $this->catRepo->getChildCategories($categoryId);
            foreach($childCategories as $childCategory) {
                $categoryIds[] = $childCategory->getId();
            }
            $categoryIds[] = $categoryId;
            $categoryIds = implode(',',$categoryIds);
            $filter['categoryTreeFilterIds'] = $categoryIds;
        }
        if(!isset($filter['categoryTreeFilterIds'])) {
            unset($filter['includeChildCategories']);
        }

        $idsToInclude = implode(',', $idsToInclude);
        $idsToExclude = implode(',', $idsToExclude);
        if ($idsToIncludeActive && $idsToInclude === '') {
            $idsToInclude = '-1';
        }
        return $this->fetchTableData($search, $filter, $this->getSearchableColumns(),
            $offset, $limit, $order, $idsToInclude, $idsToExclude);
    }

    public function getCountBySlug($slug, $productId = null)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id) as count')
            ->from(static::ENTITY, 'a')
            ->where('a.slug = :slug');
        $qb->setParameter(':slug', $slug);
        if ($productId) {
            $qb->andWhere('a.id <> :id');
            $qb->setParameter(':id', $productId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function getCountBySku($sku, $productId)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id) as count')
            ->from(static::ENTITY, 'a')
            ->where('a.sku = :sku');
        $qb->setParameter(':sku', $sku);
        if ($productId) {
            $qb->andWhere('a.id <> :id');
            $qb->setParameter(':id', $productId);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NotFoundException
     * @throws \Exception
     */
    public function copyAsDraft($id)
    {
        $dummy = $this->getById($id)->toArray();

        $catId = $dummy['category']['id'];
        $dummy['category'] = $catId;

        $dummy['productId'] = null;
        unset($dummy['id']);

        $dummy['status'] = Model::STATUS_DRAFT;

        $dummy['title'] .= '-copy';
        $dummy['slug'] .= '-copy';

        $supplierId = $dummy['supplier']['id'];
        unset($dummy['supplier']);
        $dummy['supplierId'] = $supplierId;

        unset($dummy['createdAt'], $dummy['updatedAt']);

        // Attributes
        $attributes = $dummy['attributes'];
        unset($dummy['attributes']);

        $dummyImages = $dummy['images'];

        $dummy['images'] = ''; /** @depreciated**/


        $dummy['sku'] = $this->generateNonDuplicateSku($dummy['sku'], $dummy['productId']);

        $productId = $this->mapper->insert($dummy);
        if (count($dummyImages) > 0) {
            foreach ($dummyImages as $image) {
                $imageData = [
                    'imageId' => $image['imageId'],
                    'productId' => $productId,
                    'file' => $image['file'],
                    'main' => $image['isMain'],
                    'sort' => 0,
                ];
                $this->imageMapper->insert($imageData);
            }
        }

        $dataAttributes = [];
        foreach($attributes as $attribute) {
            $dataAttributes[$attribute['attributeId']][]['name'] = $attribute['attributeName'];
            $dataAttributes[$attribute['attributeId']][]['value'] = $attribute['attributeValue'] . '#' . $attribute['attributeValueId'];
        }


        $this->attributeService->saveAttributesForProduct($productId, $dataAttributes);


        return $this->getById($productId);
    }

    public function generateNonDuplicateSlug($slug, $productId = null)
    {
        $i = 1;
        $slugDummy = $slug;
        while($this->getCountBySlug($slug, $productId) > 0) {
            $slug = $slugDummy . '-' . $i++;
        }
        return $slug;
    }

    public function generateNonDuplicateSku($sku, $productId = null)
    {
        $i = 1;
        $skuDummy = $sku;
        while($this->getCountBySku($sku, $productId) > 0) {
            $sku = $skuDummy . '-' . $i++;
        }
        return $sku;
    }


    public function fetchAttributesForSku($sku)
    {
        return $this->mapper->fetchAttributesForSku($sku);
    }

    public function getProductAttributeValues($id)
    {
        return $this->productAttributeValuesMapper->fetchAll(['productId' => $id]);
    }

    /**
     * used for migration atm
     *
     *
     * @return void
     */
    public function saveExistingAttributes($productId, $attributeId, $attributeName, $attributeValue, $attributeValueId)
    {
        $attDataFormatted = [];
        $attDataFormatted['productId'] = $productId;
        $attDataFormatted['attributeId'] = $attributeId;
        $attDataFormatted['attributeName'] = $attributeName;
        $attDataFormatted['attributeValue'] = $attributeValue;
        $attDataFormatted['attributeValueId'] = $attributeValueId;

        $this->productAttributeValuesMapper->insert($attDataFormatted);
    }

    public function getMappingIds()
    {
        $data = [];
        foreach ($this->mapper->getMappingIds() as $row) {
            $data[$row['mappingId']] = $row['mappingId'];
        }

        return $data;
    }

    public function getNumberOfProductsPerStatus()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id) as count, a.status')
            ->from(static::ENTITY, 'a')
            ->groupBy('a.status');
        $query = $qb->getQuery();
//        var_dump($query->getSQL());
        $counts = [];
        foreach ($query->getResult() as $entity) {
            $counts[] = [
                'status' => $entity['status'],
                'count' => $entity['count']
            ];
        }
        return $counts;
    }

    public function getProductIdsByCategoryId($categoryId)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a.id')
            ->from(static::ENTITY, 'a')
            ->where('a.categoryId = :categoryId');
        $qb->setParameter(':categoryId', $categoryId);

        var_dump($qb->getQuery()->getResult());
        die();

        return $this->mapper->getProductIdsByCategoryId((int)$categoryId);
    }

    public function getProductsByIds(array $ids)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from(static::ENTITY, 'a')
            ->where('a.id IN (:ids)');
        $qb->setParameter(':ids', $ids);
        $items= [];
        foreach ($qb->getQuery()->getResult() as $entity) {
            $items[] = static::FACTORY::make(
                $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity), $this->entityManager
            );
        }

        return $items;
    }

    public function getProductCountForMap($mapId): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('COUNT(a.id)')
            ->from(static::ENTITY, 'a')
            ->where('a.mappingId IN (:mapId)');
        $qb->setParameter(':mapId', $mapId);

        return $qb->getQuery()->getSingleScalarResult();
    }
}
