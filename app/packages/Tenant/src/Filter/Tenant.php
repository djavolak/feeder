<?php
namespace EcomHelper\Tenant\Filter;

use Laminas\Filter\ToInt;
use Skeletor\Core\Filter\FilterInterface;
use Volnix\CSRF\CSRF;
use EcomHelper\Tenant\Validator\Tenant as Validator;
use Laminas\I18n\Filter\Alnum;
use Skeletor\Core\Validator\ValidatorException;

class Tenant implements FilterInterface
{
    public function __construct(
//        private Validator $validator
    )
    { }

    public function getErrors()
    {
//        return $this->validator->getMessages();
    }

    public function filter(array $postData): array
    {
        $alnum = new Alnum(true);
        $int = new ToInt();
        $data = [
            'id' => (isset($postData['id'])) ? $postData['id'] : null,
            'name' => $postData['name'],
            'productionUrl' => $postData['productionUrl'],
            'developmentUrl' => $postData['developmentUrl'],
            'prodAuthToken' => $postData['prodAuthToken'],
            'devAuthToken' => $postData['devAuthToken'],
            CSRF::TOKEN_NAME => $postData[CSRF::TOKEN_NAME],
        ];
//        if (!$this->validator->isValid($data)) {
//            throw new ValidatorException();
//        }
        unset($data[CSRF::TOKEN_NAME]);

        return $data;
    }
}