<?php

namespace EcomHelper\Feeder\Validator;

use EcomHelper\Feeder\ParsingActions\AddAttributeBasedOnString;
use EcomHelper\Feeder\ParsingActions\ChangeCategoryBasedOnString;
use EcomHelper\Feeder\ParsingActions\SearchReplace;
use Skeletor\Validator\ValidatorInterface;
use Volnix\CSRF\CSRF;

class CategoryParsingRules implements ValidatorInterface
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
        if (!isset($data['categories'])) {
            $this->messages['categories'][] = 'Category must be selected.';
            $valid = false;
        }

        if (!$data['action']) {
            $this->messages['action'][] =  'Action must be selected.';
            $valid = false;
        }
        if (!$this->csrf->validate($data)) {
            $this->messages['general'][] = 'Invalid form key.';
            $valid = false;
        }
        if (!$this->validateFieldsBasedOnAction($data)){
            $valid = false;
        };
        return $valid;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function validateFieldsBasedOnAction($data)
    {
        switch ($data['action']) {
            case SearchReplace::class:
                return $this->validateSearchAndReplaceInputs($data);
            case ChangeCategoryBasedOnString::class:
                return $this->validateChangeCategoryBasedOnStringInputs($data);
            case AddAttributeBasedOnString::class:
                return $this->validateAddAttributeBasedOnStringInputs($data);
            default:
                return false;
        }

    }
    public function validateSearchAndReplaceInputs(array $data):bool
    {
        $inputs = unserialize($data['data']);
        if (strlen($inputs['search']) < 2) {
            $this->messages['search'][] = 'Search string must be at least 3 characters long.';
            return false;
        }
        if ($inputs['subject'] == -1) {
            $this->messages['subject'][] = 'Search where must be selected.';
            return false;
        }
        return true;
    }
    private function validateChangeCategoryBasedOnStringInputs(array $data):bool
    {
        $inputs = unserialize($data['data']);
        if (strlen($inputs['search']) < 2) {
            $this->messages['search'][] = 'Search string must be at least 3 characters long.';
            return false;
        }
        if ($inputs['subject'] == -1) {
            $this->messages['subject'][] = 'Search where must be selected.';
            return false;
        }
        if (!$inputs['category']) {
            $this->messages['category'][] = 'Category must be selected.';
            return false;
        }
        return true;
    }

    private function validateAddAttributeBasedOnStringInputs($data)
    {
        $inputs = unserialize($data['data']);
        if (strlen($inputs['search']) < 2) {
            $this->messages['search'][] = 'Search string must be at least 2 characters long.';
            return false;
        }
        if ($inputs['subject'] == -1) {
            $this->messages['subject'][] = 'Search where must be selected.';
            return false;
        }
        if (!$inputs['attributeId'] || $inputs['attributeId'] === '-1') {
            $this->messages['attribute'][] = 'Attribute must be selected.';
            return false;
        }
        if (!$inputs['attributeValueId'] || $inputs['attributeValueId'] === '-1') {
            $this->messages['attributeValue'][] = 'Value must be selected.';
            return false;
        }
        return true;
    }


}