<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Mapper\AttributeGroupAttributeValue;
use EcomHelper\Attribute\Mapper\SecondDescriptionAttributeCategory;
use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Attribute\Service\AttributeGroup;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Service\Product;
use EcomHelper\Product\Service\Supplier;
use Skeletor\Tag\Service\Tag;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class ProductController extends AjaxCrudController
{
    const TITLE_VIEW = "View product";
    const TITLE_CREATE = "Create new product";
    const TITLE_UPDATE = "Edit product: ";
    const TITLE_UPDATE_SUCCESS = "product updated successfully.";
    const TITLE_CREATE_SUCCESS = "product created successfully.";
    const TITLE_DELETE_SUCCESS = "product deleted successfully.";
    const PATH = 'product';

    protected $tableViewConfig = [
        'writePermissions' => true,
        'useModal' => true,
        'draftable' => true,
        'bulkEditable' => true
    ];

    /**
     * @param ProductRepository $repo
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     * @param Logger $logger
     */
    public function __construct(
        Product $service, Session $session, Config $config, Flash $flash, Engine $template, protected Category $categoryService,
        protected Supplier $supplierService,
//        protected Attribute $attributeService,
//        protected SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper,
//        private AttributeGroup $attributeGroupService,
//        private AttributeGroupAttributeValue $attributeGroupAttributeValueMapper,
        private Tag $tag
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    public function form(): Response
    {
        $id = $this->getRequest()->getAttribute('id');
        $product = null;
        $productAttributeValues = [];
//        $attributes = $this->service->getAttributesWithoutValues();
        $this->setGlobalVariable('pageTitle', self::TITLE_CREATE);
        $allowedAttributeIdsForSecondDescription = null;
        $productTags = [];
        if ($id) {
            $product = $this->service->getById($id);
//            $productTags = $this->tagService->getTagsForProductByProductId($id);
//            $productAttributeValuesNoFormat = $this->attributeService->getOrderedAttributesForProduct($id);
            $allowedAttributeIdsForSecondDescription = [];
//            $allowedAttributeIdsForSecondDescriptionData = $this->secondDescriptionAttributeCategoryMapper->getAttributeIdsByCategoryId($product->getCategory()->getId());
//            foreach($allowedAttributeIdsForSecondDescriptionData as $allowedAttributeId) {
//                $allowedAttributeIdsForSecondDescription[] = $allowedAttributeId['attributeId'];
//            }
            $allowedAttributeIdsForSecondDescription = implode(',', $allowedAttributeIdsForSecondDescription);
//            foreach($productAttributeValuesNoFormat as $productAttributeValue) {
//                $productAttributeValues[$productAttributeValue['attributeId']][] = $productAttributeValue;
//            }
            $this->setGlobalVariable('pageTitle', self::TITLE_UPDATE . $product->getTitle());
        }
        $attributeGroups = [];
        $tags = $this->tag->getEntities();
//        foreach ($this->attributeGroupService->getEntities() as $attributeGroup){
//            $groupValues = $this->attributeGroupAttributeValueMapper->getAttributeIdsByGroupId($attributeGroup->getId());
//            $formattedValues = [];
//            foreach ($groupValues as $groupValue) {
//                $formattedValues[] = $groupValue['attributeId'];
//            }
//            $attributeGroups[$attributeGroup->getId()] = [
//                'name' => $attributeGroup->getName(),
//                'values' => implode(',',$formattedValues)
//            ];
//        }
        return $this->respondPartial('form', [
            'model' => $product,
            'categories' => $this->categoryService->getCategoryHierarchy(),
            'suppliers' => $this->supplierService->getEntities(),
            'productAttributeValues' => $productAttributeValues,
            'attributes' => [],
            'allowedAttributeIdsForSecondDescription' => $allowedAttributeIdsForSecondDescription,
            'attributeGroups' => $attributeGroups ?? [],
            'tags' => $tags ?? [],
            'productTags' => $productTags
        ]);
    }

    public function json()
    {
        $this->getResponse()->getBody()->write(json_encode($this->service->getEntityData(
            (int) $this->getRequest()->getAttribute('id')
        )));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function getAttributes()
    {
        $limit['limit'] = 10;
        $limit['offset'] =  $this->getRequest()->getQueryParams()['offset'];
        $query = $this->getRequest()->getQueryParams()['query'];
        $search =  $query === 'null' ? null : $query;
        $this->getResponse()->getBody()->write(json_encode($this->service->getAttributes($search, $limit)));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function getGroupAttributesByGroupId()
    {
        $groupId = $this->getRequest()->getQueryParams()['id'];
        $this->getResponse()->getBody()->write(json_encode($this->service->getGroupAttributesByGroupId($groupId)));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return \Psr\Http\Message\MessageInterface
     */
    public function copyAsDraft()
    {
        $errors = [];
        $status = false;
        $message = 'An error occurred';
        try {
            $newProduct = $this->service->copyAsDraft((int)$this->getRequest()->getAttribute('id'));
            $status = true;
            $message = 'Product successfully copied as draft';
        } catch(\Exception $e) {
            $errors['general'][] = $e->getMessage();
        }
        $this->getResponse()->getBody()->write(json_encode([
           'errors' => $errors,
           'message' => $message,
           'status' => $status,
            'productId' => $newProduct->getId()
       ]));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    /**
     * @return Response
     */
    public function bulkEditForm()
    {
        $data = $this->getRequest()->getParsedBody();
        $productIds = $data['ids'];
        $names = $data['names'];
        $attributes = [];
        $groups = $this->attributeService->getGroups();
        $tags = $this->tagService->getEntities();
        $existingTags = $this->tagService->getTagsForProducts($productIds);
        if($productIds !== '') {
            $productIds = explode(',',$productIds);
            foreach($productIds as $productId) {
                $attributes[] = $this->service->getProductAttributeValues($productId);
            }
        }
        if($names !== '') {
            $names = explode(',', $names);
        }
        return $this->respondPartial('bulkEditForm', [
            'ids' => $productIds,
            'names' => $names,
            'categories' => $this->categoryService->getCategoryHierarchy(),
            'attributes' => $attributes,
            'groups' => $groups,
            'existingTags' => $existingTags,
            'tags' => $tags
        ]);
    }

    /**
     * @return \Psr\Http\Message\MessageInterface
     */
    public function bulkEditSubmit()
    {
        $errors = [];
        $status = false;
        $message = 'An error occurred';
        try {
            $status = true;
            $ids = $this->service->bulkEdit($this->getRequest()->getParsedBody());
            $this->service->syncProducts($ids);
            $message = 'Products updated sucessfully';
        } catch (\Exception $e) {
            $errors['general'][] = $e->getMessage();
        }
        $this->getResponse()->getBody()->write(json_encode([
               'errors' => $errors,
               'message' => $message,
               'status' => $status,
               'token' => ($errors !== []) ? \Volnix\CSRF\CSRF::getHiddenInputString() : null
       ]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return \Psr\Http\Message\MessageInterface
     */
    public function bulkDelete()
    {
        $errors = [];
        $status = false;
        $message = 'An error occurred';
        try {
            $idsFromBody = $this->getRequest()->getParsedBody()['ids'];
            $ids = $idsFromBody !== '' ? explode(',', $idsFromBody) : null;
            if ($ids) {
                foreach ($ids as $id) {
                    $this->service->delete((int)$id);
                }
                $this->service->syncProducts($ids);
            }
            $message = 'Products Successfully deleted';
            $status = true;
        } catch(\Exception $e) {
            $errors['general'][] = $e->getMessage();
        }
        $this->getResponse()->getBody()->write(json_encode([
           'errors' => $errors,
           'message' => $message,
           'status' => $status,
           'token' => ($errors !== []) ? \Volnix\CSRF\CSRF::getHiddenInputString() : null
       ]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }
    public function bulkSync()
    {
        $errors = [];
        $status = false;
        $message = 'An error occurred';
        try {
            $idsFromBody = $this->getRequest()->getParsedBody()['ids'];
            $ids = $idsFromBody !== '' ? explode(',', $idsFromBody) : null;
            $this->service->syncProducts($ids);
            $message = 'Products Successfully Synced';
            $status = true;
        } catch(\Exception $e) {
            $errors['general'][] = $e->getMessage();
        }
        $this->getResponse()->getBody()->write(json_encode([
               'errors' => $errors,
               'message' => $message,
               'status' => $status,
               'token' => ($errors !== []) ? \Volnix\CSRF\CSRF::getHiddenInputString() : null
           ]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }
}