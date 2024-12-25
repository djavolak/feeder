<?php

namespace EcomHelper\Backend\Controller;

use DI\DependencyException;
use EcomHelper\Attribute\Mapper\SecondDescriptionAttributeCategory;
use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Feeder\Mapper\ParsingRuleCategory;
use EcomHelper\Feeder\ParsingActions\AddAttributeBasedOnString;
use EcomHelper\Feeder\ParsingActions\ChangeCategoryBasedOnString;
use EcomHelper\Feeder\ParsingActions\SearchReplace;
use EcomHelper\Feeder\Repository\CategoryParsingRule;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Repository\SupplierRepository;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Http\Message\MessageInterface;
use Skeletor\Controller\AjaxCrudController;
use Skeletor\Mapper\NotFoundException;
use Skeletor\TableView\Service\TableDecorator;
use Tamtamchik\SimpleFlash\Flash;

class CategoryParsingRuleController extends AjaxCrudController
{
    const PATH = 'category-parsing-rules';
    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        \EcomHelper\Feeder\Service\CategoryParsingRule $service, Session $session, Config $config, Flash $flash, protected Engine $template,
        private CategoryParsingRule $categoryParsingRuleRepo, private SupplierRepository $supplierRepository,
        private CategoryRepository $categoryRepo, private ProductRepository $productRepo,
        private Product $productService, private Attribute $attributeRepository,
        private \EcomHelper\Attribute\Repository\AttributeValues $attributeValuesRepository,
        protected SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper
    )
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    public function view(): Response
    {
        $this->setGlobalVariable('pageTitle', static::TITLE_VIEW);
        $tableDecorator = new TableDecorator($this->service->compileTableColumns());
        return $this->respond('view', [
            'tableFilters' => $tableDecorator->generateFiltersHtml(),
            'columnHeaders' => $tableDecorator->generateColumnHeadersForView(),
            'createAction' => $this->tableViewConfig['createAction'],
            'entityPath' => $this->tableViewConfig['entityPath'],
            'adminPath' => $this->tableViewConfig['adminPath'],
        ]);
    }


    public function form(): Response
    {
        $id = (int) $this->getRequest()->getAttribute('id');
        $model = null;
        $this->setGlobalVariable('pageTitle', static::TITLE_CREATE);
        if ($id) {
            $model = $this->service->getById($id);
            $title = $model->getId();
            $this->setGlobalVariable('pageTitle', static::TITLE_UPDATE . $title);
        }
        return $this->respondPartial('form', [
            'model' => $model,
            'adminPath' => $this->tableViewConfig['adminPath'],
            'suppliers' => $this->supplierRepository->fetchAll(),
            'categories' => $this->categoryRepo->fetchAll(['tenantId' => 0]),
            'supportedActions' => \EcomHelper\Feeder\Model\CategoryParsingRule::$supportedRules,
        ]);
    }


    /**
     * //Used to get inputs when user selects rule in form
     * @return MessageInterface
     */
    public function getFormInputsForAction(): \Psr\Http\Message\MessageInterface
    {
        $action = $this->getRequest()->getQueryParams()['action'];
        $id = (int) $this->getRequest()->getQueryParams()['id'];
        $inputData = null;
        if ($id !== 0) {
            try {
                /** @var \EcomHelper\Feeder\Model\CategoryParsingRule $model */
                $model = $this->categoryParsingRuleRepo->getById($id);
                $inputData = unserialize($model->getData());
                if ($model->getAction() === AddAttributeBasedOnString::class) {
                    $attributeValues = $this->attributeValuesRepository->fetchAll(['attributeId'=>$inputData['attributeId']]);
                }
            } catch (NotFoundException $e) {
                //@todo some error handling
            }
        }
        $action = lcfirst(substr($action, strrpos($action, '\\') + 1));
        return $this->respondPartial($action, [
            'inputData' => $inputData,
            'categories' => $this->categoryRepo->fetchAll(['tenantId' => 0]),
            'attributes' => $this->attributeRepository->fetchAll(),
            'attributeValues' => $attributeValues ?? null,
        ]);
    }

    /**
     * Used when user clicks apply or undo button in the table view
     *
     * @param null $ruleId
     * @return Response
     * @throws GuzzleException
     * @throws DependencyException
     * @throws \DI\NotFoundException
     */
    public function handleRulesForExistingProducts($ruleId = null): Response
    {
        if (isset($ruleId)) {
            $id = (int)$ruleId;
            $undo = false;
        } else {
            $id = (int)$this->getRequest()->getQueryParams()['id'];
            $undo = (bool)$this->getRequest()->getQueryParams()['undo'];
        }

        $model = $this->service->getById($id);
        $action = $model->getAction();
        $categories = $model->getCategories();
        $productIdsToSync = [];
        foreach ($categories as $category) {
            $categoryId = $category->getId();
            //@todo figure better way for handling specific undo actions or just move it in a function
            if ($undo && $action === ChangeCategoryBasedOnString::class) {
                $data = unserialize($model->getData());
                $categoryId = $data['category'];
            }
            if ($undo && $action === SearchReplace::class) {
                if (unserialize($model->getData())['replace'] === '') {
                    $this->getResponse()->getBody()->write('Cannot undo this action because replace was empty string');
                    return $this->getResponse();
                }
            }
            if (!$model->getSupplier()) {
                $products = $this->productRepo->fetchAll(['category' => $categoryId]);
            } else {
                $products = $this->productRepo->fetchAll(['category' => $categoryId, 'supplierId' => $model->getSupplier()->getId()]);
            }
            foreach ($products as $product) {
                $productData = $product->toArray();
                //@todo pitati milosa kako ovo resiti najbolje, nema smisla da bude ovde
                unset($productData['id']);
                unset($productData['images']);
                unset($productData['category']);
                unset($productData['supplier']);
                unset($productData['createdAt']);
                unset($productData['updatedAt']);
                $productData['supplierId'] = $product->getSupplier()->getId();
                $productData['category'] = $product->getCategory()->getId();
                $productData['productId'] = $product->getId();
                $attributes = $productData['attributes'];
                unset($productData['attributes']);
                if (count($attributes) > 0) {
                    foreach ($attributes as $attribute) {
                        $productData['attributes'][$attribute['attributeId']][] =
                            ['name' => $attribute['attributeName']];
                        $productData['attributes'][$attribute['attributeId']][] =
                            ['value' => $attribute['attributeValue'] . '#' . $attribute['attributeValueId']];
                    }
                }
                $dataHash = md5(serialize($productData));
                global $container;
                //@todo pitati milosa sta sa ovime posto mi treba container da bih buildovao objekat
                $productData = ($container->get($action))($productData, $model, $undo);
                if ($dataHash !== md5(serialize($productData))) {
                    $updatedProduct = $this->productRepo->update($productData);
                    $this->generateNewShortDesc($updatedProduct);
                    $productIdsToSync[] = $productData['productId'];
                }
            }
        }
        $this->productService->syncProducts($productIdsToSync);
        $this->getResponse()->getBody()->write('Done');
        return $this->getResponse();
    }

    /**
     * @throws NotFoundException
     */
    public function getRulesForCat()
    {
        $categoryId = (int) $this->getRequest()->getQueryParams()['id'];
        $rules = $this->categoryParsingRuleRepo->getRulesByCatId($categoryId);
        $ids = [];
        foreach ($rules as $rule) {
            $ids[] = $rule->getId();
        }
        $this->getResponse()->getBody()->write(json_encode($ids));
        return $this->getResponse();
    }

    protected function generateNewShortDesc($product)
    {
        $allowedAttributeIdsForSecondDescription = [];
        $allowedAttributeIdsForSecondDescriptionData = $this->secondDescriptionAttributeCategoryMapper->getAttributeIdsByCategoryId($product->getCategory()->getId());
        foreach($allowedAttributeIdsForSecondDescriptionData as $allowedAttributeId) {
            $allowedAttributeIdsForSecondDescription[] = $allowedAttributeId['attributeId'];
        }
        $shortDescHtml = '<table class="woocommerce-product-attributes shop_attributes"><tbody>';
        /** @var \EcomHelper\Attribute\Model\ProductAttributeValues $attribute */
        $groupedAttributes = [];
        $attributeNameMapping = [];
        foreach ($product->getAttributes() as $attribute) {
            $groupedAttributes[$attribute['attributeId']][] = $attribute['attributeValue'];
            $attributeNameMapping[$attribute['attributeId']] = $attribute['attributeName'];
        }
        while (count($allowedAttributeIdsForSecondDescription) > 0 ) {
            $target = array_shift($allowedAttributeIdsForSecondDescription);
            foreach($groupedAttributes as $attributeId => $attributeValues) {
                if ((int)$attributeId === $target) {
                    $found = true;
                    $shortDescHtml .= "<tr><th>{$attributeNameMapping[$attributeId]}</th>";
                    $shortDescHtml .= "<td>";
                    foreach ($attributeValues as $key => $attributeValue) {
                        $shortDescHtml .= $attributeValue;
                        if (array_key_last($attributeValues) !== $key) {
                            $shortDescHtml .= ', ';
                        }
                    }
                    $shortDescHtml .= '</td></tr>';
                }
            }
        }
        $shortDescHtml .= '</tbody></table>';
        $shortDesc = '';
        if (isset($found)) {
            $shortDesc = $shortDescHtml;
        }
        $this->productService->updateField('shortDescription', $shortDesc, $product->getId());
    }
}