<?php

namespace EcomHelper\Feeder\Filter;

use EcomHelper\Feeder\Validator\CategoryParsingRules;
use Psr\Http\Message\ServerRequestInterface as Request;
use Skeletor\Filter\FilterInterface;
use Skeletor\Validator\ValidatorException;
use Volnix\CSRF\CSRF;

class SupplierAttributeMapping implements FilterInterface
{
    public function __construct(private \EcomHelper\Feeder\Validator\SupplierAttributeMapping $validator)
    {
    }

    public function getErrors(): array
    {
       return $this->validator->getMessages();
    }

    public function filter(Request $request): array
    {
        $postData = $request->getParsedBody();
        if (!$this->validator->isValid($postData)) {
            throw new ValidatorException();
        }
        unset($postData[CSRF::TOKEN_NAME]);
        return $postData;
    }
}