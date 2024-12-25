<?php

namespace EcomHelper\Product\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use EcomHelper\Category\Entity\Category;
use EcomHelper\Category\Factory\CategoryFactory;
use EcomHelper\Product\Entity\Product;
use EcomHelper\Product\Entity\Supplier;
use Skeletor\Tag\Entity\Tag;
use Skeletor\Tag\Model\TagFactory;

class ProductFactory
{
    public static function compileEntityForUpdate($data, $entityManager)
    {
        $dataAttributes = $data['attributes'];
        $newAttributes = $data['newAttributes'];
        unset($data['attributes'], $data['newAttributes']);
        $product = $entityManager->getRepository(Product::class)->find($data['id']);
        $tags = new ArrayCollection();
        foreach ($data['tags']  as $tagId => $value) {
            $tags[] = $entityManager->getRepository(Tag::class)->find($tagId);
        }
        $category = $entityManager->getRepository(Category::class)->find($data['category']);
        $supplier = $entityManager->getRepository(Supplier::class)->find($data['supplierId']);
        unset($data['category']);
        unset($data['tags']);
        unset($data['supplierId']);
        $data['images'] = [];
        $data['attributes'] = [];
        $product->populateFromDto(new \EcomHelper\Product\Model\Product(...$data));
        $product->setTags($tags);
        $product->setCategory($category);
        $product->setSupplier($supplier);

        return $product->getId();
    }

    public static function compileEntityForCreate($data, $entityManager)
    {
        $dataAttributes = $data['attributes'];
        $newAttributes = $data['newAttributes'];
        unset($data['attributes'], $data['newAttributes']);

//        if($dataAttributes && count($dataAttributes) > 0) {
//            $this->attributeService->saveAttributesForProduct($productId, $dataAttributes, true);
//        }
//
//        if($newAttributes && count($newAttributes) > 0) {
//            $this->attributeService->createNewAttributeForProduct($productId, $newAttributes);
//        }

//        $this->catRepo->updateProductCount($product->getCategory()->getId());
//        if ($product->getCategory()?->getParent()) {
//            $this->catRepo->updateProductCount($product->getCategory()->getParent()->getId());
//        }
//        if ($product->getCategory()?->getParent()?->getParent()) {
//            $this->catRepo->updateProductCount($product->getCategory()->getParent()->getParent()->getId());
//        }

        $product = new Product();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $tags = new ArrayCollection();
//        foreach ($data['tags']  as $tagId => $value) {
//            $tags[] = $entityManager->getRepository(Tag::class)->find($tagId);
//        }
        $category = $entityManager->getRepository(Category::class)->find($data['category']);
        $supplier = $entityManager->getRepository(Supplier::class)->find($data['supplierId']);
        unset($data['category']);
        unset($data['tags']);
        unset($data['supplierId']);
        $data['images'] = [];
        $data['attributes'] = [];
        $product->populateFromDto(new \EcomHelper\Product\Model\Product(...$data));
        $product->setTags($tags);
        $product->setCategory($category);
        $product->setSupplier($supplier);
        $entityManager->persist($product);

        return $product->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Product\Model\Product
    {
        if (isset($itemData['supplier']) && $itemData['supplier']) {
            $itemData['supplier'] = SupplierFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['supplier']), $em);
        } else {
            $itemData['supplier'] = null;
        }
        if (isset($itemData['category']) && $itemData['category']) {
            $itemData['category'] = CategoryFactory::make($em->getUnitOfWork()->getOriginalEntityData($itemData['category']), $em);
        } else {
            $itemData['category'] = null;
        }
        if (isset($itemData['tags']) && $itemData['tags']) {
            $tags = [];
            foreach ($itemData['tags'] as $tag) {
                $tags[] = TagFactory::make($em->getUnitOfWork()->getOriginalEntityData($tag), $em);
            }
            $itemData['tags'] = $tags;
        } else {
            $itemData['tags'] = [];
        }
        $blocks = [];
        foreach (json_decode($itemData['description']) as $block) {
            $blocks[] = (array) $block;
        }
        $itemData['description'] = $blocks;
        $blocks = [];
        foreach (json_decode($itemData['shortDescription']) as $block) {
            $blocks[] = (array) $block;
        }
        $itemData['shortDescription'] = $blocks;

        $itemData['images'] = []; // @TODO gallery
        $itemData['attributes'] = []; // @TODO
        $itemData['mappingId'] = ''; // @TODO
        $itemData['specialPriceFrom'] = ''; // @TODO
        $itemData['specialPriceTo'] = ''; // @TODO
        if (isset($itemData['imageId']) && $itemData['imageId']) {
            $itemData['images'][0] = new \Skeletor\Image\Model\Image(...$em->getUnitOfWork()->getOriginalEntityData($itemData['image']));
        }
        unset($itemData['imageId']);
        unset($itemData['image']);
        unset($itemData['categoryId']);
        unset($itemData['supplierId']);


        return new \EcomHelper\Product\Model\Product(...$itemData);
    }

}