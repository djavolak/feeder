<?php

namespace EcomHelper\Attribute\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use EcomHelper\Attribute\Entity\Attribute;
use EcomHelper\Attribute\Entity\AttributeValue;
use EcomHelper\Attribute\Entity\Group;

class AttributeFactory
{
    public static function compileEntityForUpdate($data, $em)
    {
        $attribute = $em->getRepository(Attribute::class)->find($data['id']);
        $groupsToAdd = new ArrayCollection();
        foreach ($data['attributeGroup'] as $groupId) {
            $groupsToAdd[] = $em->getRepository(Group::class)->find($groupId);
        }
        $valuesToAdd = new ArrayCollection();
        foreach ($data['attributeValues'] as $key => $value) {
            if (is_string($key)) {
                $valueEntity = $em->getRepository(AttributeValue::class)->find($key);
            } else {
                $valueEntity = new AttributeValue();
                $valueEntity->populateFromDto(new \EcomHelper\Attribute\Model\AttributeValue(\Ramsey\Uuid\Uuid::uuid4(), $value));
                $em->persist($valueEntity);
            }
            $valuesToAdd[] = $valueEntity;
        }
        unset($data['attributeGroup']);
        unset($data['attributeValues']);
        $attribute->populateFromDto(new \EcomHelper\Attribute\Model\Attribute(...$data));
        if ($attribute->getGroups()->count()) {
            foreach ($attribute->getGroups() as $group) {
                if ($groupsToAdd->contains($group)) {
                    $group->getAttributes()->add($attribute);
                } else {
                    $group->getAttributes()->removeElement($attribute);
                }
                $groupsToAdd->removeElement($group);
            }
        }
        // make sure to keep existing and add new
        foreach ($groupsToAdd as $group) {
            $group->addAttribute($attribute);
        }
        $attribute->setValues($valuesToAdd);

        return $attribute->getId();
    }

    public static function compileEntityForCreate($data, $em)
    {
        $attribute = new Attribute();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        $groups = new ArrayCollection();
        foreach ($data['attributeGroup'] as $groupId) {
            $groups[] = $em->getRepository(Group::class)->find($groupId);
        }
        unset($data['attributeGroup']);
        unset($data['attributeValues']);
        $attribute->populateFromDto(new \EcomHelper\Attribute\Model\Attribute(...$data));
        if (count($groups) > 0) {
            foreach ($groups as $group) {
                $group->getAttributes()->add($attribute);
            }
        }
        $em->persist($attribute);

        return $attribute->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Attribute\Model\Attribute
    {
        $groups = new ArrayCollection();
        foreach ($itemData['groups'] as $group) {
            $groupData = $em->getUnitOfWork()->getOriginalEntityData($group);
            unset($groupData['category']);
            $groups[] = new \EcomHelper\Attribute\Model\AttributeGroup(...$groupData);
        }
        $itemData['groups'] = $groups;
        $values = [];
        foreach ($itemData['values'] as $value) {
            $value = $em->getUnitOfWork()->getOriginalEntityData($value);
            $value['image'] = null;
            if (isset($value['imageId']) && $value['imageId']) {
                $value['image'] = new \Skeletor\Image\Model\Image(...$em->getUnitOfWork()->getOriginalEntityData($value['image']));
            }
            unset($value['imageId']);
            unset($value['attributes']);
            unset($value['attributes_id']);
            $values[] = new \EcomHelper\Attribute\Model\AttributeValue(...$value);
        }
        $itemData['values'] = $values;

        return new \EcomHelper\Attribute\Model\Attribute(...$itemData);
    }
}