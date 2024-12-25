<?php
namespace EcomHelper\Post\Filter;

use Laminas\Filter\ToInt;
use Laminas\I18n\Filter\Alnum;
use Skeletor\Core\Filter\FilterInterface;
use Skeletor\Core\Validator\ValidatorException;
use Volnix\CSRF\CSRF;

class Post implements FilterInterface
{
    public function __construct(
        private \EcomHelper\Post\Validator\Post $validator
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
            'title' => $postData['title'],
            'description' => $postData['description'],
            'categoryId' => $postData['categoryId'] ?? null,
            CSRF::TOKEN_NAME => $postData[CSRF::TOKEN_NAME],
        ];
        if (!$this->validator->isValid($data)) {
            throw new ValidatorException();
        }
        unset($data[CSRF::TOKEN_NAME]);

        return $data;
    }
}