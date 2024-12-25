<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Service\AttributeValue;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class AttributeValues extends AjaxCrudController
{
    const TITLE_VIEW = "View attribute values";
    const TITLE_UPDATE = "Edit attribute value: ";
    const TITLE_UPDATE_SUCCESS = "Value updated successfully.";
    const TITLE_CREATE_SUCCESS = "Value created successfully.";
    const TITLE_DELETE_SUCCESS = "Value deleted successfully.";
    const PATH = 'attribute-values';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(AttributeValue $service, Session $session, Config $config, Flash $flash, Engine $template)
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    public function fetchForAjaxSearch()
    {
        $attributeId = $this->getRequest()->getQueryParams()['attributeId'];
        $attributeValues = $this->service->getEntities(['attributeId' => $attributeId]);
        $attributeValues = array_map(function ($attributeValue) {
            return [
                'id' => $attributeValue->getId(),
                'value' => $attributeValue->getAttributeValue(),
            ];
        }, $attributeValues);
        $this->getResponse()->getBody()->write(json_encode($attributeValues));
        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

}