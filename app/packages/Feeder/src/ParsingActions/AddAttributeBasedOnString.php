<?php

namespace EcomHelper\Feeder\ParsingActions;

use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Attribute\Repository\AttributeValues;
use Skeletor\Mapper\NotFoundException;

class AddAttributeBasedOnString extends BaseRule
{
    public function __construct(private Attribute $attributeRepository, private AttributeValues $attributeValuesRepository)
    {
    }

    /**
     * @throws NotFoundException
     */
    protected function applyRule(array $productData, array $data): array
    {
        $keyword = mb_strtolower($data['search']);
        /** @var \EcomHelper\Attribute\Model\Attribute $attribute */
        $attribute = $this->attributeRepository->getById($data['attributeId']);
        /** @var \EcomHelper\Attribute\Model\AttributeValue $attributeValue */
        $attributeValue = $this->attributeValuesRepository->getById($data['attributeValueId']);
        $subject = mb_strtolower($productData[$data['subject']]);
        $keyword = preg_quote($keyword, '/');
        if (isset($data['contains']) && $data['contains'] === '1'){
            if (preg_match('/'.$keyword.'/u', $subject)) {
                $productData['attributes'][$attribute->getId()][] = [
                    'name' => $attribute->getAttributeName()
                ];
                $productData['attributes'][$attribute->getId()][] = [
                    'value' => $attributeValue->getAttributeValue() . '#' . $attributeValue->getId()
                ];
            }
            return $productData;
        }
        if (preg_match('/(?<!\w)'.$keyword.'(?!\w)/', $subject)) {
            $productData['attributes'][$attribute->getId()][] = [
                'name' => $attribute->getAttributeName()
            ];
            $productData['attributes'][$attribute->getId()][] = [
                'value' => $attributeValue->getAttributeValue() . '#' . $attributeValue->getId()
            ];
        }
        return $productData;
    }

    /**
     * @throws NotFoundException
     */
    protected function undoRule(array $productData, array $data): array
    {
        $keyword = mb_strtolower($data['search']);
        $subject = mb_strtolower($productData[$data['subject']]);
        $keyword = preg_quote($keyword, '/');
        /** @var \EcomHelper\Attribute\Model\AttributeValue $attributeValue */
        $attributeValue = $this->attributeValuesRepository->getById($data['attributeValueId']);
        if (isset($data['contains']) && $data['contains'] === '1'){
            if (preg_match('/'.$keyword.'/u', $subject)) {
                if (isset($productData['attributes'][$data['attributeId']])) {
                    foreach ($productData['attributes'][$data['attributeId']] as $key => $attribute) {
                        if (!isset($attribute['value'])) {
                            continue;
                        }
                        if ($attribute['value'] === $attributeValue->getAttributeValue() . '#' . $attributeValue->getId()) {
                            unset($productData['attributes'][$data['attributeId']][$key]);
                            unset($productData['attributes'][$data['attributeId']][$key - 1]);
                        }
                    }
                }
            }
            return $productData;
        }
        if (preg_match('/(?<!\w)'.$keyword.'(?!\w)/', $subject)) {
            if (isset($productData['attributes'][$data['attributeId']])) {
                foreach ($productData['attributes'][$data['attributeId']] as $key => $attribute) {
                    if (!isset($attribute['value'])) {
                        continue;
                    }
                    if ($attribute['value'] === $attributeValue->getAttributeValue() . '#' . $attributeValue->getId()) {
                        unset($productData['attributes'][$data['attributeId']][$key]);
                        unset($productData['attributes'][$data['attributeId']][$key - 1]);
                    }
                }
            }
        }

        return $productData;
    }
}