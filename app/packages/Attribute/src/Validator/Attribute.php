<?php
namespace EcomHelper\Attribute\Validator;

use EcomHelper\Attribute\Repository\AttributeValues;
use Skeletor\Core\Validator\ValidatorInterface;
use Volnix\CSRF\CSRF;

class Attribute implements ValidatorInterface
{
    private $messages = [];
    private array $data;
    //@todo this whole validator is fix to prevent duplicate attribute values do this better
    public function __construct(private AttributeValues $attributeValuesRepo, private CSRF $csrf)
    {
    }

    public function isValid(array $data): bool
    {

//        if (isset($data['attributeValuesNew'])) {
//            if (isset($data['attributeId'])) {
//                //check if there is a value with the same name
//                foreach ($data['attributeValuesNew'] as $key => $attributeValue) {
//                    if ($this->attributeValuesRepo->fetchAll(['attributeValue' => $attributeValue,
//                        'attributeId' => $data['attributeId']])) {
//                        unset($data['attributeValuesNew'][$key]);
//                        $this->messages['attributes'][] = 'Attribute value ' . $attributeValue . ' already exists';
//                        $valid = false;
//                    }
//                }
//            }
//        }
        if (!$this->csrf->validate($data)) {
            $this->messages['general'][] = 'Invalid form key.';
            $valid = false;
        }
        $this->data = $data;
        return $valid ?? true;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getData(): array
    {
        return $this->data;
    }
}