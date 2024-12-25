<?php
namespace EcomHelper\Category\Filter;

use EcomHelper\Product\Service\UrlHelper;
use Laminas\Filter\ToInt;
use Skeletor\Core\Filter\FilterInterface;
use Skeletor\Core\Service\BlocksParser;
use Volnix\CSRF\CSRF;
use EcomHelper\Category\Validator\Category as CategoryValidator;
use Laminas\I18n\Filter\Alnum;
use Skeletor\Core\Validator\ValidatorException;

class Category implements FilterInterface
{
    /**
     * @var CategoryValidator
     */
    private $validator;

    /**
     * @param CategoryValidator $validator
     */
    public function __construct(CategoryValidator $validator, private BlocksParser $blocksParser)
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
        $int = new ToInt();
        $slug = UrlHelper::slugify($postData['title']);
        if ($postData['slug']) {
            $slug = $postData['slug'];
        }
        $description = '';
        if (isset($postData['blocks'])) {
            $description = json_encode($this->blocksParser->parse((array) $postData['blocks']));
        }
        $data = [
            'id' => $postData['id'],
            'title' => $postData['title'],
            'count' => $postData['count'] ?? 0,
            'description' => $description,
            'slug' => $slug,
            'status' => $postData['status'],
            'tenantId' => $postData['tenantId'],
            'parent' => $postData['parent'],
            'imageId' => $postData['imageId'] ?? '',
            'secondDescription' => $postData['secondDescription'],
            CSRF::TOKEN_NAME => $postData[CSRF::TOKEN_NAME],
        ];
        if (!$this->validator->isValid($data)) {
            throw new ValidatorException();
        }

        unset($data[CSRF::TOKEN_NAME]);

        return $data;
    }
}