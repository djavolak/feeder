<?php

namespace EcomHelper\Attribute\Filter;

use Psr\Http\Message\ServerRequestInterface as Request;
use Skeletor\Core\Filter\FilterInterface;
use Volnix\CSRF\CSRF;

class Attribute implements FilterInterface
{
    public function __construct(private \EcomHelper\Attribute\Validator\Attribute $validator)
    {
    }

    public function getErrors()
    {
        return $this->validator->getMessages();
    }

    /**
     */
    public function filter(array $postData): array
    {
        $data['id'] = $postData['id'];
        $data['isVisible'] = isset($postData['isVisible']) ? 1 : 0;
        $data['isFilter'] = isset($postData['isFilter']) ? 1 : 0;
        $data['name'] = $postData['attributeName'];
        $data['position'] = (int) $postData['position'] ?? 0;
        $data['attributeGroup'] = $postData['attributeGroup'] ?? [];
        $data['attributeValues'] = $postData['attributeValues'] ?? [];
        if (!$this->validator->isValid($data)) {
            if (isset($this->validator->getMessages()['attributes'])) {
                $data = $this->validator->getData();
            }
        }
        unset($data[CSRF::TOKEN_NAME]);
        return $data;
    }
}