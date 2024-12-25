<?php
declare(strict_types=1);
namespace EcomHelper\Backend\Controller;

use Doctrine\ORM\EntityManagerInterface;
use EcomHelper\Category\Service\Category;
use EcomHelper\Category\Service\CategoryMapper;
use EcomHelper\Product\Repository\SupplierRepository;
use EcomHelper\Product\Service\Supplier;
use EcomHelper\Tenant\Service\Tenant;
use Skeletor\Image\Service\Image;
use GuzzleHttp\Client;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Tag\Service\Tag;
use Tamtamchik\SimpleFlash\Flash;

/**
 * Class IndexController
 * @package Fakture\Backend\Controller
 */
class IndexController extends \Skeletor\Core\Controller\Controller
{
    public function __construct(
        Session $session, Config $config, Flash $flash, Engine $template, private \PDO $pdo, private Image $image,
        private Tag $tag, private Client $httpClient, private Category $category, private Tenant $tenant,
        private SupplierRepository $supplierRepo, private CategoryMapper $categoryMapper
    ) {
        parent::__construct($template, $config, $session, $flash);
    }

    public function index()
    {
        return $this->redirect('/login/loginForm/');
    }

    public function importSupplierMapping()
    {
        ini_set('max_execution_time', '3600');
        $stmt = $this->pdo->prepare("SELECT * FROM categoryMapping");
        $stmt->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $stmt2 = $this->pdo->prepare("SELECT * FROM supplier where supplierId = " . $row['supplierId']);
            $stmt2->execute();
            $supplier = $this->supplierRepo->fetchAll(['name' => $stmt2->fetch(\PDO::FETCH_ASSOC)['name']])[0];
            $filter = ['source1' => $row['source1'], 'source2' => $row['source2'], 'source3' => $row['source3'],
                'supplier' => $supplier->getId()];
            $existing = $this->categoryMapper->getEntities($filter);
            try {
                if (empty($existing)) {
                    $categoryId = null; // not mapped
                    $ignored = 0;
                    if ($row['categoryId'] == -1) {
                        $ignored = 1;
                    } else if ($row['categoryId'] == 0) {

                    } else {
                        $stmt2 = $this->pdo->prepare("SELECT * FROM category where categoryId = " . $row['categoryId']);
                        $stmt2->execute();
                        $catTitle = $stmt2->fetch(\PDO::FETCH_ASSOC)['title'];
                        $filter = ['title' => $catTitle, 'tenant' => null];
                        $category = $this->category->getEntities($filter)[0];
                        if (!$category) {
                            var_dump($catTitle . ' not found in new db');
                            continue;
                        }
                        $categoryId = $category->getId();
                    }
                    $data = [
                        'source1' => $row['source1'],
                        'source2' => $row['source2'],
                        'source3' => $row['source3'],
                        'status' => $row['status'],
                        'ignored' => $ignored,
                        'categoryId' => $categoryId,
                        'supplierId' => $supplier->getId(),
                    ];
                    $this->categoryMapper->create($data);
                } else {
//                    var_dump($filter);
//                    die();
                }
            } catch (\Exception $e) {

            }
        }

        die('done');
    }

    public function importSuppliers()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM supplier");
        $stmt->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $existing = $this->supplierRepo->fetchAll(['name' => $row['name']]);
            try {
                if (empty($existing)) {
                    $data = [
                        'id' => null,
                        'name' => $row['name'],
                        'code' => $row['code'],
                        'skuPrefix' => $row['skuPrefix'],
                        'status' => $row['status'],
                        'feedSource' => $row['feedSource'],
                        'feedUsername' => $row['feedUsername'],
                        'feedPassword' => $row['feedPassword'],
                        'sourceType' => $row['sourceType'],
                    ];
                    $this->supplierRepo->create($data);
                } else {
                    // no need to update anything for now
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        die('done');
    }

    public function importTenants()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tenant");
        $stmt->execute();
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $existing = $this->tenant->getEntities(['name' => $row['name']]);
            try {
                if (empty($existing)) {
                    $data = [
                        'id' => null,
                        'name' => $row['name'],
                        'productionUrl' => $row['productionUrl'],
                        'developmentUrl' => $row['developmentUrl'],
                        'prodAuthToken' => $row['prodAuthToken'],
                        'devAuthToken' => $row['devAuthToken'],
                    ];
                    $this->tenant->create($data);
                } else {
                    // no need to update anything for now
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        die('done');
    }

    public function importCategories()
    {
        ini_set('max_execution_time', '1200');
        // have to be imported in order by level 1,2,3, sql order does not work ?
        $stmt = $this->pdo->prepare("SELECT * FROM category LEFT JOIN image ON (category.image = image.imageId)
WHERE level = 3");
        $stmt->execute();
        $items = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $existingCount = 0;
        foreach ($items as $row) {
            // tenantId 3 is not existing
            if ($row['tenantId'] == 3) {
                continue;
            }
            $tenantId = null;
            if ($row['tenantId']) {
                $stmt2 = $this->pdo->prepare("SELECT * FROM tenant WHERE tenantId = " . $row['tenantId']);
                $stmt2->execute();
                $name = $stmt2->fetch()['name'];
                $tenant = $this->tenant->getEntities(['name' => $name]);
                $tenantId = $tenant[0]->getId();
            }
            $filter = [
                'title' => $row['title'], 'level' => (int) $row['level'], 'tenant' => $tenantId, 'count' => (int) $row['count']
            ];
            $existing = $this->category->getEntities($filter);
            try {
                if (empty($existing)) {
                    $parentId = null;
                    if ($row['parent']) {
                        $sql = "SELECT * FROM category WHERE categoryId = " . $row['parent'];
                        $stmt3 = $this->pdo->prepare($sql);
                        $stmt3->execute();
                        $itemData = $stmt3->fetch();
                        $filter = [
                            'title' => $itemData['title'], 'level' => (int) $itemData['level'], 'count' => (int) $itemData['count']
                        ];
                        if ($tenantId) {
                            $filter['tenant'] = $tenantId;
                        }
                        $parent = $this->category->getEntities($filter);
                        if (empty($parent)) {
                            var_dump('missing: ' . $itemData['title']);
                            var_dump('for: ' . $name);
                            continue;
                            die();
                        }
                        $parentId = $parent[0]->getId();
                    }
                    $imageId = null;
                    if (isset($row['filename']) && strlen($row['filename'])) {
                        $image = $this->image->createFromUrl('https://knv.rs/images' . $row['filename']);
                        $imageId = $image->getId();
                    }
                    $desc = '';
                    if (isset($row['description']) && strlen($row['description'])) {
                        $desc = json_encode([
                            'type' => 'textEditor',
                            'value' => $row['description'],
                        ]);
                    }
                    $secondDesc = '';
                    if (isset($row['secondDescription']) && strlen($row['secondDescription'])) {
                        $secondDesc = json_encode([
                            'type' => 'textEditor',
                            'value' => $row['secondDescription'],
                        ]);
                    }
                    $catData = [
                        'id' => null,
                        'title' => $row['title'],
                        'slug' => $row['slug'],
                        'description' => $desc,
                        'secondDescription' => $secondDesc,
                        'status' => $row['status'],
                        'level' => $row['level'],
                        'count' => $row['count'],
                        'parent' => $parentId,
                        'imageId' => $imageId,
                        'tenantId' => $tenantId,
                    ];
//                    var_dump('creating ...');
//                    var_dump($catData);
//                    die();
                    $this->category->create($catData);
                } else {
                    var_dump($existing[0]->getTitle());
                    if ($existing[0]->getTenant()) {
                        var_dump($existing[0]->getTenant()->getName());
                    }
//                    var_dump($row['tenantId']);
//                    die();
                    $existingCount++;
                    // no need to update anything for now
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                die();
            }
        }
        echo 'existing: ' . $existingCount;
        die('done');
    }

    public function importTags()
    {
        $stmt = $this->pdo->prepare("SELECT * FROM tag LEFT JOIN image ON (tag.stickerImageId = image.imageId)");
        $stmt->execute();
        $tag = null;
        foreach ($stmt->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $existing = $this->tag->getEntities(['title' => $row['title']]);
            try {
                if (empty($existing)) {
                    $stickerImageId = null;
                    if (isset($row['filename']) && strlen($row['filename'])) {
                        $image = $this->image->createFromUrl('https://knv.rs/images' . $row['filename']);
                        $stickerImageId = $image->getId();
                    }
                    $tagData = [
                        'id' => null,
                        'title' => $row['title'],
                        'stickerImagePosition' => $row['stickerImagePosition'],
                        'priceLabel' => $row['title'],
                        'stickerLabel' => $row['title'],
                        'stickerImageId' => $stickerImageId,
                    ];
                    $this->tag->create($tagData);
                } else {
                    // no need to update anything for now
                }
            } catch (\Exception $e) {
                var_dump($e->getMessage());
            }
        }

        die('done');
    }
}
