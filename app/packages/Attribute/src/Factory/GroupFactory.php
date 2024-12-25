<?php
namespace EcomHelper\Attribute\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use EcomHelper\Attribute\Entity\Attribute;
use EcomHelper\Attribute\Entity\Group;
use EcomHelper\Category\Entity\Category;

class GroupFactory
{
    public static function compileEntityForUpdate($data, $em)
    {
        $group = $em->getRepository(\EcomHelper\Attribute\Entity\Group::class)->find($data['id']);
        $category = null;
        if ($data['categoryId']) {
            $category = $em->getRepository(Category::class)->find($data['categoryId']);
        }
        unset($data['categoryId']);
        $attributes = new ArrayCollection();
        foreach ($data['attributes'] as $attributeId) {
            $attributes[] = $em->getRepository(Attribute::class)->find($attributeId);
        }
        $data['attributes'] = new ArrayCollection(); // compatibility, do not pass entities to model
        $group->populateFromDto(new \EcomHelper\Attribute\Model\AttributeGroup(...$data));
        if ($category) {
            $group->setCategory($category);
        }
        $group->setAttributes($attributes);

        return $group->getId();
    }

    public static function compileEntityForCreate($data, $em)
    {
        $group = new Group();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $category = null;
        if ($data['categoryId']) {
            $category = $em->getRepository(Category::class)->find($data['categoryId']);
        }
        $attributes = new ArrayCollection();
        foreach ($data['attributes'] as $attributeId) {
            $attributes[] = $em->getRepository(Attribute::class)->find($attributeId);
        }
        unset($data['categoryId']);
        unset($data['attributes']);
        $group->populateFromDto(new \EcomHelper\Attribute\Model\AttributeGroup(...$data));
        if ($category) {
            $group->setCategory($category);
        }
        $group->setAttributes($attributes);
//        var_dump($data['values']);
        $em->persist($group);

        return $group->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Attribute\Model\AttributeGroup
    {
        if (isset($itemData['category']) && $itemData['category']) {
            $itemData['category'] = new \EcomHelper\Category\Model\Category(...$em->getUnitOfWork()->getOriginalEntityData($itemData['category']));
        } else {
            $itemData['category'] = null;
        }
        $attributes = new ArrayCollection();
        foreach ($itemData['attributes'] as $attribute) {
            $attributes[] = AttributeFactory::make($em->getUnitOfWork()->getOriginalEntityData($attribute), $em);
        }
        $itemData['attributes'] = $attributes;
        unset($itemData['category']);

        return new \EcomHelper\Attribute\Model\AttributeGroup(...$itemData);
    }
}