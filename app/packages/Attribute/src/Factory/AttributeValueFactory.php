<?php
namespace EcomHelper\Attribute\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use EcomHelper\Attribute\Entity\AttributeValue;
use EcomHelper\Attribute\Model\Attribute;
use Skeletor\Image\Entity\Image;

class AttributeValueFactory
{
    public static function compileEntityForUpdate($data, $em)
    {
        $attributeValue = $em->getRepository(AttributeValue::class)->find($data['id']);
        $image = null;
        if ($data['imageId']) {
            $image = $em->getRepository(Image::class)->find($data['imageId']);
        }
        unset($data['imageId']);
        unset($data['attributeId']);
        $attributeValue->populateFromDto(new \EcomHelper\Attribute\Model\AttributeValue(...$data));
        if ($image) {
            $attributeValue->setImage($image);
        }

        return $attributeValue->getId();
    }

    public static function compileEntityForCreate($data, $em)
    {
        throw new \Exception('not used');
        $attributeValue = new AttributeValue();
        $data['id'] = \Ramsey\Uuid\Uuid::uuid4();
        unset($data['attributes']);
        $image = null;
        if ($data['imageId']) {
            $image = $em->getRepository(Image::class)->find($data['imageId']);
        }
        unset($data['imageId']);
        $attributeValue->populateFromDto(new \EcomHelper\Attribute\Model\AttributeValue(...$data));
        if ($image) {
            $attributeValue->setImage($image);
        }
        $em->persist($attributeValue);

        return $attributeValue->getId();
    }

    public static function make($itemData, $em): \EcomHelper\Attribute\Model\AttributeValue
    {
        if (isset($itemData['image']) && $itemData['image']) {
            $itemData['image'] = new \Skeletor\Image\Model\Image(...$em->getUnitOfWork()->getOriginalEntityData($itemData['image']));
        } else {
            $itemData['image'] = null;
        }
        $itemData['attribute'] = new Attribute(...$em->getUnitOfWork()->getOriginalEntityData($itemData['attributes'][0]));
        unset($itemData['imageId']);
        unset($itemData['attributes']);

        return new \EcomHelper\Attribute\Model\AttributeValue(...$itemData);
    }
}