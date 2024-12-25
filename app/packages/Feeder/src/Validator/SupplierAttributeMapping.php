<?php

namespace EcomHelper\Feeder\Validator;

use EcomHelper\Feeder\ParsingActions\AddAttributeBasedOnString;
use EcomHelper\Feeder\ParsingActions\ChangeCategoryBasedOnString;
use EcomHelper\Feeder\ParsingActions\SearchReplace;
use Skeletor\Validator\ValidatorInterface;
use Volnix\CSRF\CSRF;

class SupplierAttributeMapping implements ValidatorInterface
{
    private $messages = [];
    private $csrf;
    public function __construct(CSRF $csrf)
    {
        $this->csrf = $csrf;
    }

    public function isValid(array $data): bool
    {
        $valid = true;
        if (!$this->csrf->validate($data)) {
            $this->messages['general'][] = 'Invalid form key.';
            $valid = false;
        }
        if (isset($data['localAttributes'])) {
            if (count($data['localAttributes']) === 0) {
                $this->messages['localAttributes'][] = 'Local attribute must be selected.';
                return false;
            }
            if (count($data['localAttributeValues']) === 0) {
                $this->messages['localAttributeValues'][] = 'Local attribute value must be selected.';
                return false;
            }
            foreach ($data['localAttributeValues'] as $localAttribute) {
                if ($localAttribute === '-1') {
                    $this->messages['localAttributeValues'][] = 'Local attribute value must be selected.';
                    return false;
                }
            }
            $validationData = [];
            foreach($data['localAttributes'] as $attributeId) {
                if(isset($validationData[$attributeId])) {
                    $this->messages['localAttributes'][] = 'Local attribute must be unique.';
                    return false;
                }
                $validationData[$attributeId] = true;
            }
        }

        return $valid;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

}