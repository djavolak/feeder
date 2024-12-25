<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Attribute\Service\AttributeGroup as AttributeGroupService;
use EcomHelper\Category\Service\Category;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class AttributeGroupController extends AjaxCrudController
{
    const TITLE_VIEW = "View attribute group";
    const TITLE_CREATE = "Create new attribute group";
    const TITLE_UPDATE = "Edit attribute group: ";
    const TITLE_UPDATE_SUCCESS = "attribute group updated successfully.";
    const TITLE_CREATE_SUCCESS = "attribute group created successfully.";
    const TITLE_DELETE_SUCCESS = "attribute group deleted successfully.";
    const PATH = 'attribute-group';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        AttributeGroupService $service, Session $session, Config $config, Flash $flash, Engine $template,
        private Category $categoryService, private Attribute $attribute,
//        private AttributeGroupAttributeValue $attributeGroupAttributeValueMapper,
    )
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    /**
     * @return \GuzzleHttp\Psr7\Response
     */
    public function form(): Response
    {
//        $id = (int) $this->getRequest()->getAttribute('id');
        $this->formData['categories'] = $this->categoryService->getMasterCategories();
        $this->formData['attributes'] = $this->attribute->getEntities();

//        $attributeGroup = null;
//        $this->setGlobalVariable('pageTitle', self::TITLE_CREATE);
        $existingAttributesWithNames = [];
//        if ($id) {
//            $existingAttributes = $this->attributeGroupAttributeValueMapper->fetchAll(['attributeGroupId' => $id]);
//            foreach($existingAttributes as $existingAttribute) {
//                $existingAttributesWithNames[] = [
//                    'id' => $existingAttribute['attributeId'],
//                    'name' => $this->attributeService->getAttributeName($existingAttribute['attributeId']),  //@todo add attribute name accessible from the table
//                ];
//            }
//            $attributeGroup = $this->service->getById($id);
//            $this->setGlobalVariable('pageTitle', self::TITLE_UPDATE . $attributeGroup->getName());
//        }
        $this->formData['existingAttributesWithNames'] = $existingAttributesWithNames;

        return parent::form();
    }

    public function getAttributesFromGroupByCategory()
    {
        //@todo make this better ASAP
        $data = $this->getRequest()->getParsedBody();
        $categoryId = $data['categoryId'];
        $result = [];
        if($categoryId) {
            $entities = [];
            $attributeGroups = $this->service->getEntities(['categoryId' => $categoryId]);
            foreach($attributeGroups as $attributeGroup) {
                $entities[] = $this->attributeGroupAttributeValueMapper->getAttributeIdsByGroupId($attributeGroup->getId());
            }
            $attributeIds = [];
            foreach($entities as $attributeIdArray) {
                foreach($attributeIdArray as $attributeIdData) {
                    $attributeIds[] = $attributeIdData['attributeId'];
                }
            }
            if(count($attributeIds) > 0) {
                $attributeIds = implode(',', $attributeIds);
                $result = $this->attributeService->getAttributesSorted($attributeIds);
            }
        }
        $this->getResponse()->getBody()->write(json_encode([
           'entities' => $result
       ]));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function getAttributeWithValuesByGroup()
    {
        $data = $this->getRequest()->getParsedBody();
        $groupId = $data['groupId'];
        if($groupId) {
            $attributes = [];
            $attributeGroupAttributeId = $this->attributeGroupAttributeValueMapper->fetchAll(['attributeGroupId' => $groupId]);
            foreach($attributeGroupAttributeId as $arrData) {
                $attributes[] = [
                    'attributeId' => $arrData['attributeId'],
                    'values' => $this->attributeService->getAttributeValues($arrData['attributeId']),
                    'attributeName' => $this->attributeService->getAttributeName($arrData['attributeId']) //@todo make better, we have info in values
                ];
            }
        }
        $this->getResponse()->getBody()->write(json_encode([
           'attributes' => $attributes
       ]));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }
}