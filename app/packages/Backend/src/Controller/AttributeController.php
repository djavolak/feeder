<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Attribute\Service\AttributeGroup;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Psr7\Response;
use Skeletor\Core\Controller\AjaxCrudController;
use Laminas\Session\SessionManager as Session;
use Laminas\Config\Config;
use Tamtamchik\SimpleFlash\Flash;
use League\Plates\Engine;

class AttributeController extends AjaxCrudController
{
    const TITLE_VIEW = "View attribute";
    const TITLE_CREATE = "Create new attribute";
    const TITLE_UPDATE = "Edit attribute: ";
    const TITLE_UPDATE_SUCCESS = "attribute updated successfully.";
    const TITLE_CREATE_SUCCESS = "attribute created successfully.";
    const TITLE_DELETE_SUCCESS = "attribute deleted successfully.";
    const PATH = 'attribute';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true, 'bulkEditable' => true];

    private $attributeRepo;

    public function __construct(
        Attribute $service, Session $session, Config $config, Flash $flash, Engine $template,
//        \EcomHelper\Attribute\Repository\Attribute $attributeRepo,
//        private AttributeGroupAttributeValue $attributeGroupAttributeValueMapper,
        private AttributeGroup $attributeGroup, private Category $categoryService,
//        private SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper,
//        private ProductAttributeValues $productAttributeValuesMapper,
//        private Product $productService,
//        private \EcomHelper\Attribute\Repository\AttributeValues $attributeValuesRepo
    )
    {
        parent::__construct($service, $session, $config, $flash, $template);
//        $this->attributeRepo = $attributeRepo;
    }

    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    public function form(): Response
    {
//        $id = (int) $this->getRequest()->getAttribute('id');
        $existingAttributeGroups = [];
//        if ($id) {
//            $existingAttributeGroupsData = $this->attributeGroupAttributeValueMapper->fetchAll(['attributeId' => $id]);
//            foreach($existingAttributeGroupsData as $data) { // @todo make this accessible through the table
//                $existingAttributeGroups[] = $this->attributeGroup->getById($data['attributeGroupId']);
//            }
//        }
        $this->formData['groups'] = $this->attributeGroup->getEntities();
        $this->formData['existingAttributeGroups'] = $existingAttributeGroups;

        return parent::form();
    }

    public function getAttributes()
    {
        $items = [];
        foreach($this->service->getEntities() as $attr) {
           $items[] = ['attributeId' => $attr->getId(), 'attributeName' => $attr->getName(), 'attributeValues'=> $attr->getValues()];
        }
        $this->getResponse()->getBody()->write(json_encode($items));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function createAttributeValue()
    {
        $data = $this->getRequest()->getParsedBody();
        $attributeId = $data['attributeId'];
        $value = $data['attributeValue'];
        $attributeValueId = null;
        if($attributeId && $value) {
            if (count($this->attributeValuesRepo->fetchAll(['attributeId' => $attributeId, 'attributeValue' => $value])) > 0) {
                $this->getResponse()->getBody()->write(json_encode(['message' => 'Value already exists']));
                $this->getResponse()->getBody()->rewind();
                return $this->getResponse()->withHeader('Content-Type', 'application/json');
            }
            $attributeValueId =  $this->attributeRepo->createAttributeValue($attributeId, $value);
        }
        $this->getResponse()->getBody()->write(json_encode(['attributeValueId' => $attributeValueId]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function getAttributeValues()
    {
        $data = $this->getRequest()->getParsedBody();
        $attributeId = $data['attributeId'];
        $attributeValues = [];
        if($attributeId) {
            $attributeValues = $this->service->getAttributeValues($attributeId);
        }
        $this->getResponse()->getBody()->write(json_encode(['attributeValues' => $attributeValues]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function secondDescriptionSettings()
    {
        $this->setGlobalVariable('pageTitle', 'Second Description Settings');
        return $this->respondPartial('secondDescriptionSettings', [
            'categories' =>  $this->categoryService->getMasterCategories(),
            'attributes' => $this->service->getAttributesWithoutValues()
        ]);
    }

    public function getAttributesForCategory()
    {
        $data = $this->getRequest()->getParsedBody();
        $categoryId = $data['categoryId'];
        $attributeIds = [];
        if($categoryId) {
            $attributeIds = $this->secondDescriptionAttributeCategoryMapper->getAttributeIdsByCategoryId((int)$categoryId);
        }
        $this->getResponse()->getBody()->write(json_encode(['attributeIds' => $attributeIds]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function saveAttributesForCategory()
    {
        $data = $this->getRequest()->getParsedBody();
        $attributeIds = $data['attributeIds'];
        $categoryId = $data['categoryId'];
        if($attributeIds !== '') {
            $attributeIds = explode(',', $attributeIds);
            if($categoryId && count($attributeIds) > 0) {
                $this->secondDescriptionAttributeCategoryMapper->deleteBy('categoryId', $categoryId);
                foreach($attributeIds as $attributeId) {
                    $this->secondDescriptionAttributeCategoryMapper->insert([
                        'categoryId' => (int)$categoryId,
                        'attributeId' => (int)$attributeId
                    ]);
                }
                $this->generateSecondDescriptionForProducts($categoryId, $attributeIds);
            }
        }

        $this->getResponse()->getBody()->write(json_encode(['message' => 'Successfully updated'])); //@todo validate above
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    private function generateSecondDescriptionForProducts($categoryId, $attributeIds)
    {
        $productIds = $this->productService->getProductIdsByCategoryId($categoryId);
        foreach($productIds as $productId) {
            $html = '<table class="woocommerce-product-attributes shop_attributes"><tbody>';
            $attributes = $this->productService->getProductAttributeValues($productId);
            $attributesFormatted = [];
            foreach($attributes as $attribute) {
                $attributesFormatted[$attribute['attributeId']][] = $attribute;
            }
            foreach($attributeIds as $attributeId) {
                if(isset($attributesFormatted[$attributeId])) {
                    $html .= '<tr><th>' . $attributesFormatted[$attributeId][0]['attributeName'] .'</th>';
                    $html .= '<td>';
                    $count = count($attributesFormatted[$attributeId]);
                    foreach($attributesFormatted[$attributeId] as $key => $att) {
                        $html .= $att['attributeValue'];
                        if ($key+1 !== $count) {
                            $html .= ', ';
                        }
                    }
                    $html .= '</td></tr>';
                }
            }
            $html .= '</tbody></table>';
            $this->productService->updateField('shortDescription', $html, $productId);
        }
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
            }
            $message = 'Attributes Successfully deleted';
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