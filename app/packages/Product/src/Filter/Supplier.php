<?php
namespace EcomHelper\Product\Filter;

use Skeletor\Core\Filter\FilterInterface;
use Volnix\CSRF\CSRF;
use EcomHelper\Product\Validator\Supplier as SupplierValidator;
use Laminas\I18n\Filter\Alnum;
use Skeletor\Core\Validator\ValidatorException;

class Supplier implements FilterInterface
{
    /**
     * @var SupplierValidator
     */
    private $validator;

    /**
     * @param SupplierValidator $validator
     */
    public function __construct(SupplierValidator $validator)
    {
        $this->validator = $validator;
    }

    public function getErrors()
    {
        return $this->validator->getMessages();
    }

    public function filter(array $postData): array
    {
        $alnum = new Alnum(true);
        $data = [
            'id' => $postData['id'],
            'name' => $postData['name'],
            'code' => $postData['code'],
            'skuPrefix' => $alnum->filter($postData['skuPrefix']),
            'status' => $postData['status'],
            'feedSource' => $postData['feedSource'],
            'feedUsername' => $postData['feedUsername'],
            'feedPassword' => $postData['feedPassword'],
            'sourceType' => $postData['sourceType'],
            CSRF::TOKEN_NAME => $postData[CSRF::TOKEN_NAME],
        ];
        if (!$this->validator->isValid($data)) {
            throw new ValidatorException();
        }
        unset($data[CSRF::TOKEN_NAME]);

        return $data;
    }
}