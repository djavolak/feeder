<?php

namespace EcomHelper\Post\Factory;

use EcomHelper\Category\Factory\CategoryFactory;

class PostFactory
{
    public static function compileEntityForUpdate($data, $entityManager)
    {
        $post = $entityManager->getRepository(\EcomHelper\Post\Entity\Post::class)->find($data['id']);

        if ($data['categoryId']) {
            $category = $entityManager->getRepository(\EcomHelper\Category\Entity\Category::class)->find($data['categoryId']);
            $post->setCategory($category);
        }

        unset($data['categoryId']);
        $postDto = new \EcomHelper\Post\Model\Post(...$data);
        $post->populateFromDto($postDto);

        return $post->getId();
    }

    public static function compileEntityForCreate($data, $entityManager)
    {
        $post = new \EcomHelper\Post\Entity\Post();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();

        if ($data['categoryId']) {
            $category = $entityManager->getRepository(\EcomHelper\Category\Entity\Category::class)->find($data['categoryId']);
            $post->setCategory($category);
        }

        unset($data['categoryId']);

        $postDto = new \EcomHelper\Post\Model\Post(...$data);
        $post->populateFromDto($postDto);
        $entityManager->persist($post);

        return $post->getId();
    }

    public static function make($itemData, $entityManager): \EcomHelper\Post\Model\Post
    {
        if (isset($itemData['categoryId']) && $itemData['categoryId']) {
            $itemData['category'] = CategoryFactory::make($entityManager->getUnitOfWork()->getOriginalEntityData($itemData['category']), $entityManager);
        } else {
            $itemData['category'] = null;
        }

        unset($itemData['categoryId']);

        return new \EcomHelper\Post\Model\Post(...$itemData);
    }
}