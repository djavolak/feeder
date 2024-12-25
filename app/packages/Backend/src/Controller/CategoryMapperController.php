<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Category\Repository\TenantCategoryMapperRepository;
use EcomHelper\Category\Model\Category as CategoryModel;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Category\Service\CategoryMapper;
use EcomHelper\Feeder\Mapper\ParsingRuleCategory;
use EcomHelper\Feeder\Service\CategoryParsingRule;
use EcomHelper\Product\Repository\SupplierRepository;
use EcomHelper\Product\Service\Product;
use EcomHelper\Product\Service\Supplier;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class CategoryMapperController extends AjaxCrudController
{
    const TITLE_VIEW = "View map";
    const TITLE_CREATE = "Create new map";
    const TITLE_UPDATE = "Edit map: ";
    const TITLE_UPDATE_SUCCESS = "Map updated successfully.";
    const TITLE_CREATE_SUCCESS = "Map created successfully.";
    const TITLE_DELETE_SUCCESS = "Map deleted successfully.";
    const PATH = 'category-mapper';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];


    private $supplierRepo;

    private $categoryRepo;

    private $tenantCategoryMapper;

    /**
     * @param CategoryMapper $service
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     * @param Logger $logger
     * @param SupplierRepository $supplierRepo
     * @param CategoryRepository $categoryRepo
     * @param TenantCategoryMapperRepository $tenantCategoryMapper
     */
    public function __construct(
        CategoryMapper $service, Session $session, Config $config, Flash $flash, Engine $template,
        Logger $logger, private Supplier $supplier, private Category $category, private Product $product,
        TenantCategoryMapperRepository $tenantCategoryMapper,
        private \EcomHelper\Tenant\Service\Tenant $tenantService
//        private ParsingRuleCategory $parsingRuleCategory,
//        private CategoryParsingRule $categoryParsingRuleService, \EcomHelper\MarginGroups\Service\MarginGroups $marginGroupsService,
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
        $this->tenantCategoryMapper = $tenantCategoryMapper;
        $this->tableViewConfig['createAction'] = sprintf('/%s/form/', static::PATH);
        $this->tableViewConfig['entityPath'] =  'category-mapper';
//        $this->marginGroupsService = $marginGroupsService;
    }

    /**
     * Page wrapper for internal (1st level) mapping
     *
     * @return Response
     */
    public function internalMapping(): Response
    {
        $title = self::TITLE_VIEW;
        $filter = [];
        $supplierId = $this->getRequest()->getAttribute('id');
        if ($supplierId) {
            $filter['supplier'] = $supplierId;
            $title .= ' for supplier ' . $supplierId;
        }
        $mappingId = $this->getRequest()->getAttribute('mappingId');
        if ($mappingId) {
            $filter['id'] = $mappingId;
            $title .= ' and map id:' . $mappingId;
        }
        $this->setGlobalVariable('pageTitle', $title);

        $countData = $this->service->getCountPerType($supplierId);
//        if ($mappingId) {
//            $countData['mapped'] = [1];
//        }

        $page = $this->getRequest()->getQueryParams()['page'] ?? null;
        return $this->respond('editMapping', [
            'models' => $this->service->getEntities($filter),
            'filters' => $filter,
            'suppliers' => $this->supplier->getEntities(),
            'countData' => $countData,
            'page' => $page,
            'mappingId' => $mappingId ?? null,
        ]);
    }

    /**
     * mapping form for external maps
     *
     * @return Response
     * @throws \Exception
     */
    public function externalMapping(): Response
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $filter = [];
        $tenantId = $this->getRequest()->getAttribute('id');
        $page = $this->getRequest()->getQueryParams()['page'] ?? null;
        $countData = [];
        if($tenantId) {
            $countData = $this->tenantCategoryMapper->getCountPerType($tenantId);
            $filter['tenant'] = $tenantId;
        }
        $tenants = $this->tenantService->getEntities();
        $tenantName = '';
        foreach ($tenants as $tenant) {
            if ($tenant->getId() === $tenantId) {
                $tenantName = $tenant->getName();
            }
        }
        return $this->respond('editExternalMapping', [
            'models' => $this->tenantCategoryMapper->fetchAll($filter),
            'filters' => $filter,
            'tenantId' => $tenantId ?? '',
            'tenants' => $tenants,
            'page' => $page,
            'countData' => $countData,
            'tenantName' => $tenantName
        ]);
    }


    /**
     * mapping form for existing maps
     *
     * @return Response
     * @throws \Exception
     */
    public function mapped(): Response
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $filter = [];
        $supplierId = $this->getRequest()->getAttribute('id');
        $limit = $this->getRequest()->getQueryParams()['limit'] ?? 25;
        $page = $this->getRequest()->getQueryParams()['page'] ?? 1;
        $order = ['orderBy' => 'updatedAt', 'dir' => 'DESC'];
        $offset = $limit * ($page - 1);
        if ($supplierId) {
            $filter['supplierId'] = $supplierId;
        }

        $mappingId = $this->getRequest()->getAttribute('mappingId') ?? null;
        if(!$mappingId) {
            $models = $this->service->getMapped($supplierId, $limit, $offset, $order);
        } else {
            $filter['categoryMappingId'] = $mappingId;
            $models = $this->service->getEntities($filter, null, $order);
        }
        $catsWithExistingRules = [];
//        foreach ($models as $category) {
//            $supplierCategories[] = $this->service->getMapCounts($this->formatCatString($category), $supplierId);
//            $catsRelation = $this->parsingRuleCategory->fetchAll(['categoryId' => $category->getCategory()->getId()]);
//            if ($catsRelation) {
//                foreach ($catsRelation as $relation) {
//                    $catsWithExistingRules[$category->getCategory()->getId()][] = $relation['parsingRuleId'];
//                }
//            }
//            $rulesAffectingCat = $this->categoryParsingRuleService->fetchAllRulesAffectingCategory($category->getCategory()->getId());
//            if ($rulesAffectingCat) {
//                foreach ($rulesAffectingCat as $rule) {
//                    $catsWithExistingRules[$category->getCategory()->getId()][] = $rule->getId();
//                }
//            }
//        }
        //Long live JS :D
        $controller = $this;
        $count = function ($mappingId) use ($controller) {
            return $controller->product->getProductCountForMap($mappingId);
        };

        return $this->respond('map', [
            'models' => $models,
            'supplierCategory' => $supplierCategories ?? [],
            'mappingId' => $mappingId,
            'filters' => $filter,
            'suppliers' => $this->supplier->getEntities(),
            'supplierId' => $filter['supplierId'] ?? '',
            'categories' => $this->getCategoriesDropDown(),
            'count' => $count,
            'catsWithExistingRules' => $catsWithExistingRules,
        ]);
    }

    public function mappedExternal()
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $limit = $this->getRequest()->getQueryParams()['limit'] ?? 25;
        $page = $this->getRequest()->getQueryParams()['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $filter = [];
        $tenantId = $this->getRequest()->getAttribute('id');
        $tenants = $this->tenantService->getEntities();
        $tenantName = 'No tenant selected';
        foreach ($tenants as $tenant) {
            if ($tenant->getId() === (int)$tenantId) {
                $tenantName = $tenant->getName();
            }
        }
        if ($tenantId) {
            $filter['tenantId'] = $tenantId;
        }
        $data = $this->tenantCategoryMapper->getMapped($tenantId, $limit, $offset);
        return $this->respond('mapExternal', [
            'models' => $data,
            'filters' => $filter,
            'tenantId' => $filter['tenantId'] ?? '',
            'categories' => $this->getCategoriesDropDown($tenantId),
            'tenantName' => $tenantName
        ]);
    }

    public function marginForm(): Response
    {
        $id = $_GET['id'] ?? null;
        $data = [];
        $marginGroups = $this->marginGroupsService->getGroups();
        if($id) {
            $data = $this->service->getMargins($id);
            $marginGroupId = $this->service->getMarginGroupId($id);
        }
        $fixedMargin = null;
        if (count($data) > 0) {
            if ($data[0]->getPrice() === -1) {
                $fixedMargin = $data[0];
                unset($data[0]);
            }
        }
        return $this->respondPartial('marginForm', [
            'fixedMargin' => $fixedMargin,
            'data' => $data,
            'mappingId' => $id,
            'marginGroups' => $marginGroups,
            'marginGroupId' => $marginGroupId
        ]);
    }

    public function saveMarginRules()
    {
        $id = $_POST['mappingId'] ?? null;
        if(isset($_POST['rules'],$_POST['prices'],$_POST['margins'])) {
            $data = [
                'rules' => serialize($_POST['rules']),
                'prices' => serialize($_POST['prices']),
                'margins' => serialize($_POST['margins'])
            ];
            $this->service->updateMargins($id, $data);
        }
        if(isset($_POST['marginGroupInputId'], $_POST['mappingId'])) {
            $this->service->updateField('marginGroupId',$_POST['marginGroupInputId'], $_POST['mappingId']);
        }
        if(!isset($_POST['marginGroupInputId']) && isset($_POST['mappingId'])) {
            $this->service->updateField('marginGroupId', null, $_POST['mappingId']);
        }
    }


    public function saveMapped()
    {
        $supplierId = $this->getRequest()->getAttribute('id');
        $data = $this->getRequest()->getParsedBody();
        if($data['categoryId'] === '') {
            $this->getResponse()->getBody()->write(json_encode(['message' => 'Category ID not present.']));
            $this->getResponse()->getBody()->rewind();
            return $this->getResponse()->withHeader('Content-Type', 'application/json');
        }
        $map = [
            'source1' => $data['source1'],
            'source2' => $data['source2'],
            'source3' => $data['source3'],
            'id' => $data['id'],
            'status' => 1,
            'ignored' => 0,
            'supplier' => $supplierId
        ];
        if (count($this->service->getEntities($map)) === 1) {
            $map['categoryId'] = $data['categoryId'];
            $map['supplierId'] = $map['supplier'];
            unset($map['supplier']);
            $this->service->updateFromArray($map);
            $this->getResponse()->getBody()->write(json_encode(['message' => 'Success']));
            $this->getResponse()->getBody()->rewind();
            return $this->getResponse()->withHeader('Content-Type', 'application/json');
        }
        // @TODO should not be
        $this->getResponse()->getBody()->write(json_encode(['message' => 'Selected category not found.']));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withHeader('Content-Type', 'application/json');

    }

    /**
     * mapping form for not mapped items
     *
     * @return Response
     * @throws \Exception
     */
    public function unmapped(): Response
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $filter = ['categoryId' => 0];
        $limit = $this->getRequest()->getQueryParams()['limit'] ?? 25;
        $page = $this->getRequest()->getQueryParams()['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $supplierId = $this->getRequest()->getAttribute('id');
        if ($supplierId) {
            $filter['supplierId'] = $supplierId;
        }
        $models = $this->service->getUnmapped($supplierId, $limit, $offset);
        $supplierCategories = [];
        foreach ($models as $category) {
            $supplierCategories[] = $this->service->getMapCounts($this->formatCatString($category), $supplierId);
        }
        return $this->respond('unmapped', [
            'models' => $models,
            'unmappedCountsPerSupplier' => $this->service->getCountForSupplier($supplierId, 'unmapped'),
            'supplierCategory' => $supplierCategories,
            'filters' => $filter,
            'suppliers' => $this->supplier->getEntities(),
            'supplierId' => $filter['supplierId'] ?? '',
            'categories' => $this->getCategoriesDropDown(),
        ]);
    }


    public function saveUnmapped()
    {
        $supplierId = $this->getRequest()->getAttribute('id');
        $data = $this->getRequest()->getParsedBody();
        if($data['categoryId'] === '') {
            $this->getResponse()->getBody()->write(json_encode(['message' => 'Category ID not present.']));
            $this->getResponse()->getBody()->rewind();
            return $this->getResponse()->withHeader('Content-Type', 'application/json');
        }
        $ignored = 0;
        if ($data['categoryId'] !== '-1') {
//            $margins = $this->service->getMargins($data['categoryMappingId']);
//            if (count($margins) === 0) {
//                $this->getResponse()->getBody()->write(json_encode(['message' => 'Margins not set for this category.']));
//                $this->getResponse()->getBody()->rewind();
//                return $this->getResponse()->withHeader('Content-Type', 'application/json');
//            }
        } else {
            $ignored = 1;
        }
        $map = [
            'id' => $data['id'],
            'status' => 1,
            'source1' => $data['source1'],
            'source2' => $data['source2'],
            'source3' => $data['source3'],
            'supplierId' => $supplierId,
            'ignored' => $ignored,
            'categoryId' => $data['categoryId'],
        ];

        $this->service->update($map);
        $this->getResponse()->getBody()->write(json_encode(['message' => 'Success']));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    private function getCategoriesDropDown($tenantId = null)
    {
        $topLevel = $this->category->getEntities(['level' => 1, 'tenant' => $tenantId]);
        $secondLevel = $this->category->getEntities(['level' => 2, 'tenant' => $tenantId]);
        $thirdLevel = $this->category->getEntities(['level' => 3, 'tenant' => $tenantId]);

        $items = [];
        /* @var CategoryModel $category */
        foreach ($thirdLevel as $category) {
            $parent = $this->category->getById($category->getParent()->getId());;
            $main = $this->category->getById($parent->getParent()->getId());
            $value = $main->getTitle(). ' / ' . $parent->getTitle() . ' / ' . $category->getTitle();
            $items[] = [
                'value' => sprintf('%s (%s)', $value, $category->getCount()),
                'id' => $category->getId(),
            ];
        }
        foreach ($secondLevel as $category) {
            $parent = $this->category->getById($category->getParent()->getId());
            $value = $parent->getTitle() .' / ' . $category->getTitle();
            $items[] = [
                'value' => sprintf('%s (%s)', $value, $category->getCount()),
                'id' => $category->getId(),
            ];
        }
        foreach ($topLevel as $category) {
            $items[] = [
                'value' => sprintf('%s (%s)', $category->getTitle(), $category->getCount()),
                'id' => $category->getId(),
            ];
        }

        return $items;
    }

    public function getMasterCategories()
    {
        $tenantId = 0;
        if ($this->getRequest()->getAttribute('id')) {
            $tenantId = $this->getRequest()->getAttribute('id');
        }
        $topLevel = $this->category->getEntities(['level' => 1, 'tenant' => $tenantId]);
        $secondLevel = $this->category->getEntities(['level' => 2, 'tenant' => $tenantId]);
        $thirdLevel = $this->category->getEntities(['level' => 3, 'tenant' => $tenantId]);

        $items = [];
        foreach ($thirdLevel as $category) {
            $parent = $this->category->getById($category->getParent()->getId());;
            $main = $this->category->getById($parent->getParent()->getId());
            $value = $main->getTitle(). ' / ' . $parent->getTitle() . ' / ' . $category->getTitle();
            $items[] = [
                'value' => sprintf('%s (%s)', $value, $category->getCount()),
                'id' => $category->getId(),
            ];
        }
        foreach ($secondLevel as $category) {
            $parent = $this->category->getById($category->getParent()->getId());
            $value = $parent->getTitle() .' / ' . $category->getTitle();
            $items[] = [
                'value' => sprintf('%s (%s)', $value, $category->getCount()),
                'id' => $category->getId(),
            ];
        }
        foreach ($topLevel as $category) {
            $items[] = [
                'value' => sprintf('%s (%s)', $category->getTitle(), $category->getCount()),
                'id' => $category->getId(),
            ];
        }
        $this->getResponse()->getBody()->write(json_encode($items));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function ignored()
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $filter = ['ignored' => '1'];
        $limit = $this->getRequest()->getQueryParams()['limit'] ?? 25;
        $page = $this->getRequest()->getQueryParams()['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $supplierId = $this->getRequest()->getAttribute('id');
        if ($supplierId) {
            $filter['supplierId'] = $supplierId;
        }
        $models = $this->service->getIgnored($supplierId, $limit, $offset);
        $supplierCategories = [];
        foreach ($models as $category) {
            $supplierCategories[] = $this->service->getMapCounts($this->formatCatString($category), $supplierId);
        }
        return $this->respond('ignored', [
            'models' => $models,
            'ignoredCountPerSupplier' => $this->service->getCountForSupplier($supplierId, 'ignored'),
            'supplierCategory' => $supplierCategories,
            'filters' => $filter,
            'suppliers' => $this->supplier->getEntities(),
            'supplierId' => $filter['supplierId'] ?? '',
            'categories' => $this->getCategoriesDropDown(),
        ]);
    }

    public function saveIgnored()
    {
        $supplierId = $this->getRequest()->getAttribute('id');
        $data = $this->getRequest()->getParsedBody();
        if($data['categoryId'] === '') {
            $this->getResponse()->getBody()->write(json_encode(['message' => 'Category ID not present']));
            $this->getResponse()->getBody()->rewind();
            return $this->getResponse()->withHeader('Content-Type', 'application/json');
        }
        $map = [
            'id' => $data['id'],
            'status' => 1,
            'source1' => $data['source1'],
            'source2' => $data['source2'],
            'source3' => $data['source3'],
            'supplierId' => $supplierId,
            'ignored' => 0,
            'categoryId' => $data['categoryId']
        ];
        $this->service->updateFromArray($map);
        $this->getResponse()->getBody()->write(json_encode(['message' => 'Success']));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withHeader('Content-Type', 'application/json');

    }

    public function saveMappedExternal()
    {
        $data = $this->getRequest()->getParsedBody();
        $this->tenantCategoryMapper->update([
            'tenantCategoryMappingId' => $data['tenantCategoryMappingId'],
            'mappedToId' => $data['mappedTo'],
            'categoryId' => $data['categoryId'],
            'tenantId' => $data['tenantId'],
        ]);
        $this->getResponse()->getBody()->write(json_encode(['message' => 'Success']));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function unmappedExternal()
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $limit = $this->getRequest()->getQueryParams()['limit'] ?? 25;
        $page = $this->getRequest()->getQueryParams()['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $filter = ['remoteCategory' => null];
        $tenantId = $this->getRequest()->getAttribute('id');
        $tenants = $this->tenantService->getEntities();
        foreach($tenants as $tenant) {
            if ($tenantId === $tenant->getId()) {
                $tenantName = $tenant->getName();
            }
        }
        if ($tenantId) {
            $filter['tenant'] = $tenantId;
        }
        $data = $this->tenantCategoryMapper->fetchAll($filter, ['limit' => $limit, 'offset' => $offset]);

        return $this->respond('mapExternal', [
            'models' => $data,
            'filters' => $filter,
            'tenantId' => $filter['tenantId'] ?? '',
            'categories' => $this->getCategoriesDropDown($tenantId),
            'tenantName' => $tenantName
        ]);
    }

    public function ignoredExternal()
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $filter = ['mappedToId' => -1];
        $tenantId = $this->getRequest()->getAttribute('id');
        if ($tenantId) {
            $filter['tenantId'] = $tenantId;
        }
        $models = $this->tenantCategoryMapper->fetchAll($filter);
        return $this->respond('ignoredExternal', [
            'models' => $models,
            'ignoredCountPerTenant' => $this->tenantCategoryMapper->getCountForTenant($tenantId, -1)[0],
            'filters' => $filter,
            'tenantId' => $filter['tenantId'] ?? '',
            'categories' => $this->getCategoriesDropDown($tenantId),
        ]);
    }

    public function deleteExternal()
    {
        $id = $this->getRequest()->getAttribute('id');
        $this->tenantCategoryMapper->delete($id);
    }

    private function formatCatString(\EcomHelper\Category\Model\CategoryMapper $category)
    {
        $catString = sprintf('%s', $category->getSource1());
        if ($category->getSource2()) {
            $catString = sprintf('%s-%s', $catString, $category->getSource2());
        }
        if ($category->getSource3()) {
            $catString = sprintf('%s-%s', $catString, $category->getSource3());
        }
        return $catString;
    }

    public function fetchResultsWithSearchSource()
    {
        $data = $this->getRequest()->getQueryParams();
        $search = $data['search'];
        $type = $data['type'];
        $supplierId = (int)$data['supplierId'];
        $mappingType = $data['mappingType'];
        $searchType = $data['searchType'];
        $limit = $data['limit'] ?? 25;
        $page = $data['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $supplierCategories = [];


        $models = $this->service->fetchResultsWithSearchSource($search, $type, $supplierId, $mappingType, $limit, $offset, $searchType);
        foreach ($models['results'] as $category) {
            $supplierCategories[] = $this->service->getMapCounts($this->formatCatString($category), $supplierId);
        }
        $template = '';
        $unmappedCountsPerSupplier = null;
        $ignoredCountPerSupplier = null;
        switch($type) {
            case 'mapped' :
                $template = 'map';
                break;
            case 'unmapped' :
                $template = 'unmapped';
                $unmappedCountsPerSupplier = $this->service->getCountForSupplier($supplierId, 'unmapped')[0];
                break;
            case 'ignored' :
                $template = 'ignored';
                $ignoredCountPerSupplier = $this->service->getCountForSupplier($supplierId, 'ignored')[0];
                break;
        }
        $controller = $this;
        $count = function ($mappingId) use ($controller) {
            return $controller->service->getProductCountForMap($mappingId);
        };

        return $this->respond($template, [
            'models' => $models['results'],
            'supplierCategory' => $supplierCategories,
            'filters' => [],
            'suppliers' => $this->supplierRepo->fetchAll(),
            'supplierId' => $supplierId,
            'categories' => $this->getCategoriesDropDown(),
            'searchCount' => $models['count'][0] ?? null,
            'unmappedCountsPerSupplier' => $unmappedCountsPerSupplier,
            'ignoredCountPerSupplier' => $ignoredCountPerSupplier,
            'count' => $count
        ]);
    }

    public function fetchResultsWithSearchExternal()
    {
        $data = $this->getRequest()->getQueryParams();
        $search = $data['search'];
        $type = $data['type'];
        $mappingType = $data['mappingType'];
        $searchType = $data['searchType'];
        $limit = $data['limit'] ?? 25;
        $page = $data['page'] ?? 1;
        $offset = $limit * ($page - 1);
        $tenantName = '';
        $tenantId = (int)$data['tenantId'];
        if ($tenantId) {
            $filter['tenantId'] = $tenantId;
            $tenants = $this->tenantService->getEntities();
            foreach ($tenants as $tenant) {
                if ($tenantId === $tenant->getId()) {
                    $tenantName = $tenant->getName();
                }
            }
        }


        $data = $this->service->fetchResultsWithSearchExternal($search, $type, $tenantId, $mappingType, $limit, $offset, $searchType);
        return $this->respond('mapExternal', [
            'models' => $data['results'],
            'filters' => $filter ?? [],
            'tenantId' => $filter['tenantId'] ?? '',
            'categories' => $this->getCategoriesDropDown($tenantId),
            'searchCount' => $data['count'][0] ?? null,
            'tenantName' => $tenantName
        ]);
    }
}