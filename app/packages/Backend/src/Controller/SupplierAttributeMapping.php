<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Attribute\Service\AttributeValue;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Controller\AjaxCrudController;
use Skeletor\TableView\Service\TableDecorator;
use Tamtamchik\SimpleFlash\Flash;

class SupplierAttributeMapping extends AjaxCrudController
{
    const PATH = 'attribute-mapping';
    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        \EcomHelper\Feeder\Service\SupplierAttributeMapping $service, Session $session, Config $config,
        Flash                                               $flash, protected Engine $template, private Attribute $attributeService, private AttributeValue $attributeValueService
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
        $id = (int)$this->getRequest()->getAttribute('id');
        $this->setGlobalVariable('pageTitle', static::TITLE_UPDATE);
        $attributes = $this->attributeService->getEntities();
        $model = $this->service->getById($id);
        if (count($model->getLocalAttributes()) > 0) {
            foreach ($model->getLocalAttributes() as $key => $attributeData) {
                $attributeValues[$key] = $this->attributeValueService->getEntities(['attributeId' => $attributeData['localAttribute']->getId()]);
            }
        }
        return $this->respond('form', [
            'model' => $model,
            'adminPath' => $this->tableViewConfig['adminPath'],
            'attributes' => $attributes,
            'attributeValues' => $attributeValues ?? [],
        ]);
    }

    public function getAttributesBySupplierId(): \Psr\Http\Message\MessageInterface
    {
        $supplierId = $this->getRequest()->getQueryParams()['id'];
        $categoryParam = $this->getRequest()->getQueryParams()['categoryId'];
        if ($categoryParam !== '-1') {
            $catId = (int)$categoryParam;
        }
        $attributes = $this->service->getAttributesBySupplierId((int)$supplierId, null, $catId ?? null);
        $this->getResponse()->getBody()->write(json_encode($attributes));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function getCategoriesForSupplier(): \Psr\Http\Message\MessageInterface
    {
        $supplierId = $this->getRequest()->getQueryParams()['id'];
        $categories = $this->service->getCategoriesForSupplier((int)$supplierId);
        $this->getResponse()->getBody()->write(json_encode($categories));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function getAttributeValuesByAttribute()
    {
        $supplierId = $this->getRequest()->getQueryParams()['id'];
        $attributeValue = $this->getRequest()->getQueryParams()['attribute'];
        $attributeValues = $this->service->getAttributeValuesByAttribute((int)$supplierId, $attributeValue);
        $this->getResponse()->getBody()->write(json_encode($attributeValues));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function getSupplierAttributesForSearch()
    {
        $supplierId = $this->getRequest()->getQueryParams()['id'];
        $reqBody = $this->getRequest()->getParsedBody();
        $search = $reqBody['search'] ?? null;
        $attributes = $this->service->getAttributesBySupplierId((int)$supplierId, $search);
        $objects = [];
        foreach ($attributes as $attribute) {
            $obj = new \stdClass();
            $obj->attribute = $attribute;
            $objects[] = $obj;
        }
        $this->getResponse()->getBody()->write(json_encode(['body' => ['data' => $objects]])); // needs to match with fetch table data format
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function getSupplierAttributeValuesForSearch()
    {
        $supplierId = $this->getRequest()->getQueryParams()['id'];
        $reqBody = $this->getRequest()->getParsedBody();
        $search = $reqBody['search'] ?? null;
        $attributeName = $this->getRequest()->getQueryParams()['attribute'];
        $attributeValues = $this->service->getAttributeValuesByAttribute((int)$supplierId, $attributeName, $search);
        $objects = [];
        foreach ($attributeValues as $attributeValue) {
            $obj = new \stdClass();
            $obj->attributeValue = $attributeValue;
            $objects[] = $obj;
        }
        $this->getResponse()->getBody()->write(json_encode(['body' => ['data' => $objects]])); // needs to match with fetch table data format
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withAddedHeader('Content-Type', 'application/json');
    }

    public function applyMapping()
    {
        $id = (int)$this->getRequest()->getQueryParams()['id'];
        try {
            $this->service->applyMapping($id);
        } catch (\Exception $e) {
            $this->getResponse()->getBody()->write($e->getMessage());
            return $this->getResponse();
        }

        $this->getResponse()->getBody()->write('Done');
        return $this->getResponse();
    }

    public function undoMapping()
    {
        $id = (int)$this->getRequest()->getQueryParams()['id'];
        try {
            $this->service->undoMapping($id);
        } catch (\Exception $e) {
            $this->getResponse()->getBody()->write($e->getMessage());
            return $this->getResponse();
        }
        $this->getResponse()->getBody()->write('Done');
        return $this->getResponse();
    }
}