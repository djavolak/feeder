<?php
namespace EcomHelper\Product\Filter;

use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Service\UrlHelper;
use Laminas\Filter\ToInt;
use Skeletor\Core\Filter\FilterInterface;
use Skeletor\Core\Service\BlocksParser;
use Volnix\CSRF\CSRF;
use EcomHelper\Product\Validator\Product as ProductValidator;
use Laminas\I18n\Filter\Alnum;
use Skeletor\Core\Validator\ValidatorException;

class Product implements FilterInterface
{
    /**
     * @var ProductValidator
     */
    private $validator;

    /**
     * @param ProductValidator $validator
     */
    public function __construct(
        ProductValidator $validator, private BlocksParser $blocksParser, private ProductRepository $productRepo
    ) {
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
        $productId = (isset($postData['id'])) ? $postData['id'] : null;
        $slug = $postData['title'];
        if ($postData['slug']) {
            $slug = $postData['slug']; // always slugify slug
        }
        $slug = $this->productRepo->generateNonDuplicateSlug(UrlHelper::slugify($slug, $productId));
        $sku = $this->productRepo->generateNonDuplicateSku($postData['sku'], $productId);

        if(!isset($postData['attribute'])) {
            $postData['attribute'] = null;
        }
       if (isset($postData['ignoreStatusChange']) && $postData['ignoreStatusChange'] == 'on') {
            $postData['ignoreStatusChange'] = 1;
        } else {
            $postData['ignoreStatusChange'] = 0;
        }
        if (isset($postData['salePriceLoop']) && $postData['salePriceLoop'] == 'on') {
            $postData['salePriceLoop'] = 1;
        } else {
            $postData['salePriceLoop'] = 0;
        }

        if(!isset($postData['newAttributes'])) {
            $postData['newAttributes'] = [];
        }
        foreach($postData['newAttributes'] as $key => $newAttribute) {
           if(!isset($newAttribute['name']) || $newAttribute['name'] === '') {
               unset($postData['newAttributes'][$key]);
           }
            if(!isset($newAttribute['values'])) {
                unset($postData['newAttributes'][$key]);
            }
        }
        $description = '';
        if (isset($postData['postEditor']['descriptionContainer']['blocks'])) {
            $description = json_encode($this->blocksParser->parse((array) $postData['postEditor']['descriptionContainer']['blocks']));
        }
        $shortDescription = '';
        if (isset($postData['postEditor']['shortDescriptionContainer']['blocks'])) {
            $shortDescription = json_encode($this->blocksParser->parse((array) $postData['postEditor']['shortDescriptionContainer']['blocks']));
        }

//        $existingImages = $postData['existingImages'] ?? [];
//        unset($postData['existingImages']);
//        $newImages = $postData['newImages'] ?? [];
//        unset($postData['newImages']);

        unset($postData['existingTags']);
//        $uploadedFiles = $request->getUploadedFiles();
//        $images['galleryImages'] = $uploadedFiles['galleryImages'];
//        if($images['galleryImages'][0]->getError() === 4) {
//            $images['galleryImages'] = [];
//        }
        $data = [
            'id' => $productId,
            'title' => $postData['title'],
            'slug' => $slug,
            'supplierId' => $postData['supplierId'],
            'description' => $description,
            'shortDescription' => $shortDescription,
            'inputPrice' => $int->filter($postData['inputPrice']),
            'price' => $int->filter($postData['price']),
            'specialPrice' => $int->filter($postData['specialPrice']),
            'specialPriceFrom' => strlen($postData['specialPriceFrom']) > 0 ? $postData['specialPriceFrom'] : null,
            'specialPriceTo' => strlen($postData['specialPriceTo']) > 0 ? $postData['specialPriceTo'] : null,
            'status' => $postData['status'],
            'stockStatus' => $postData['stockStatus'],
            'quantity' => $int->filter($postData['quantity']),
            'supplierProductId' => $postData['supplierProductId'] ?? $postData['sku'],
//            'images' => $images,
            'category' => $postData['category'] ?? null,
            'barcode' => $postData['barcode'],
            'ean' => $postData['ean'],
            'sku' => $sku,
            CSRF::TOKEN_NAME => $postData[CSRF::TOKEN_NAME],
            'attributes' => $postData['attribute'],
            'newAttributes' => $postData['newAttributes'],
            'tags' => $postData['tags'] ?? [],
            'ignoreStatusChange' => $postData['ignoreStatusChange'],
            'fictionalDiscountPercentage' => $int->filter($postData['fictionalDiscountPercentage']),
            'salePriceLoop' => $postData['salePriceLoop'],
        ];
        if (!$this->validator->isValid($data)) {
            throw new ValidatorException();
        }
        unset($data[CSRF::TOKEN_NAME]);

        return $data;
    }
}