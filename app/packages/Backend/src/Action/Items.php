<?php
namespace EcomHelper\Backend\Action;

use EcomHelper\Category\Mapper\TenantCategoryMapper;
use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Repository\ProductRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;
use League\Plates\Engine;

class Items
{
    private $productRepo;

    private $categoryMapper;

    private $category;

    /**
     * Handler for item list json
     * @param Logger $logger
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(
        Logger $logger, ProductRepository $productRepo, TenantCategoryMapper $categoryMapper, Category $category
    ) {
        $this->logger = $logger;
        $this->productRepo = $productRepo;
        $this->categoryMapper = $categoryMapper;
        $this->category = $category;
    }

    /**
     *
     * @param ServerRequestInterface $request request
     * @param ResponseInterface $response response
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        try {
            $tenantId = 1;
            $items = $this->productRepo->fetchFromCategories(explode(',', $request->getAttribute('categoryIds')), $tenantId);
            $data = [];
            foreach ($items as $item) {
                $attributes = $item->getAttributes();
                $mappedCategory = $this->category->getById(
                    $this->categoryMapper->fetchAll(['categoryId' => $item->getCategory()->getId()])[0]['mappedToId']
                );
                $item = $this->removeUnwantedFieldsForWp($item->toArray());
                $item['attributes'] = json_encode($attributes);
                $item['category'] = $mappedCategory->toArray();
                $item['categories'] = (int) $item['category']['id'];
                if ($item['category']['level'] !== 1) {
                    if ($item['category']['parent']['level'] === 2) {
                        $item['categories'] .= ',' . (int) $item['category']['parent']['parent']['id'];
                    }
                    $item['categories'] .= ',' . (int) $item['category']['parent']['id'];
                }
                foreach($item['images'] as $key => $image) {
                    $item['images'][$key] = '/images'. $image['file'];
                }
                unset($item['category']);
                $data[] = $item;
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        return $response->withHeader('Content-Type', 'application/json');
    }

    private function removeUnwantedFieldsForWp($item)
    {
        //@todo add rest of the fields that are handled inside plugin container here
        unset($item['salePriceLoop']);
        return $item;
    }

}