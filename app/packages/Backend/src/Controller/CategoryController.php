<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Category\Model\CategoryMapper;
use EcomHelper\Category\Repository\CategoryMapperRepository;
use EcomHelper\Category\Mapper\UnMappedCategory;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Service\Supplier;
use EcomHelper\Tenant\Service\Tenant;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class CategoryController extends AjaxCrudController
{
    const TITLE_VIEW = "View category";
    const TITLE_CREATE = "Create new category";
    const TITLE_UPDATE = "Edit category: ";
    const TITLE_UPDATE_SUCCESS = "Category updated successfully.";
    const TITLE_CREATE_SUCCESS = "Category created successfully.";
    const TITLE_DELETE_SUCCESS = "Category deleted successfully.";
    const PATH = 'category';

    private $categoryMapperRepo;

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    /**
     * @param CategoryRepository $repo
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     * @param Logger $logger
     * @param CategoryMapperRepository $categoryMapperRepo
     * @param UnMappedCategory $unMappedCategory
     */
    public function __construct(
        Category $service, Session $session, Config $config, Flash $flash, Engine $template,
//        CategoryMapperRepository $categoryMapperRepo, UnMappedCategory $unMappedCategory,
        private Supplier $supplier, private Tenant $tenantService,
//        private Product $productService
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    /**
     * @throws \Exception
     */
    public function viewStructure(): Response
    {
//        ini_set('memory_limit', '768M');
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
//        $savedHierarchy = $this->categoryHierarchyRepo->fetchAll(['tenantId'=> 1]);

        return $this->respond('viewStructure', [
            'catData' => $this->prepareCatsForViewStructure(),
            'tenantId' => 1,
            'hierarchyId' => null
        ]);
    }

    public function prepareCatsForViewStructure(): array
    {
        $array = [];
        $topLevel = $this->service->getEntities(['level' => 1]);
        foreach ($topLevel as $key => $cat) {
            $array[$key]['topLevelCategory'] = $cat;
            $secondLevel = $this->service->getEntities(['parent' => $cat->getId()]);
            if ($secondLevel !== []) {
                foreach ($secondLevel as $secLevelKey => $secondCat) {
                    $array[$key]['secondLevelCategories'][$secLevelKey][] = $secondCat;
                    $thirdLevel = $this->service->getEntities(['parent' => $secondCat->getId()]);
                    if ($thirdLevel !== []) {
                        foreach ($thirdLevel as $thirdCat) {
                            $array[$key]['secondLevelCategories'][$secLevelKey]['thirdLevelCategories'][] = $thirdCat;
                        }
                    }
                }
            }
        }
        return $array;
    }

    public function getParentableCategories()
    {
        $tenantId = $this->getRequest()->getAttribute('id');
        $tenantId = ($tenantId != '0') ? $tenantId : null;
        $categories = $this->service->getEntities(['level' => 1, 'tenant' => $tenantId]);
        $categories = array_merge($categories, $this->service->getEntities(['level' => 2, 'tenant' => $tenantId]));
        $data = [];
        foreach ($categories as $category) {
            $data[] = $category->toArray();
        }
        $this->getResponse()->getBody()->write(json_encode($data));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    public function form(): Response
    {
        $id = $this->getRequest()->getAttribute('id');
        $model = null;
        $tenantId = null;
        //@todo do this better
        $this->setGlobalVariable('pageTitle', static::TITLE_CREATE);
        if ($id) {
            $model = $this->service->getById($id);
            $title = $model->getId();
            if (method_exists($model, 'getName')) {
                $title = $model->getName();
            }
            $tenantId = $model->getTenant()?->getId() ?? null;
            $this->setGlobalVariable('pageTitle', static::TITLE_UPDATE . $title);
        }
        $categories = $this->service->getEntities(['tenant' => $tenantId, 'level' => 1]);
        $categories = array_merge($categories, $this->service->getEntities(['tenant' => $tenantId, 'level' => 2]));
        // @TODO add category ajax loader when tenant is selected

        //@todo do this better this is quick hack to prevent infinite loop
        if ($model) {
//            $childCats = $this->service->getEntities(['parent' => $model->getId()]);
//            foreach ($childCats as $childCat) {
//                foreach ($categories as $key => $category) {
//                    if ($category->getId() === $childCat->getId()) {
//                        unset($categories[$key]);
//                    }
//                }
//                if ($childCat->getLevel() === 2) {
//                    $grandChildCats = $this->service->getEntities(['parent' => $childCat->getId()]);
//                    foreach ($grandChildCats as $grandChildCat) {
//                        foreach ($categories as $key => $category) {
//                            if ($category->getId() === $grandChildCat->getId()) {
//                                unset($categories[$key]);
//                            }
//                        }
//                    }
//                }
//            }
        }
        return $this->respondPartial('form', [
            'model' => $model,
            'tenants' => $this->tenantService->getEntities(),
            'categories' => $categories,
        ]);
    }

    public function getMasterCats()
    {
        $items = [];
        $cats = $this->service->getEntities(['tenant' => null]);
        foreach($cats as $cat) {
            $items[] = ['value' => $cat->getTitle(), 'id' => $cat->getId()];
        }
        $this->getResponse()->getBody()->write(json_encode($items));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }

    /**
     * @throws GuzzleException
     */
    public function syncProducts()
    {
        $catId = $this->getRequest()->getAttribute('id');
        $this->productService->syncProductsForCat($catId);
        $this->getResponse()->getBody()->write(json_encode(['status' => 'ok']));
        $this->getResponse()->getBody()->rewind();
        return $this->getResponse();
    }
}