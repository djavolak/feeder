<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Category\Repository\TenantCategoryMapperRepository;
use EcomHelper\Category\Service\Category;
use Laminas\Config\Config;
use League\Plates\Engine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Mapper\PDOWrite;

class CategoryMapping
{
    /**
     * HomeAction constructor.
     * @param Logger $logger
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(private Logger $logger,  private Category $category,
        private TenantCategoryMapperRepository $tenantCategoryMapperRepo, private PDOWrite $pdo) {
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
            $tenantId = $request->getAttribute('supplier');
            $existingMappedTenantCategories = $this->tenantCategoryMapperRepo->fetchAll(['tenantId' => $tenantId]);
            $idsArray = [];
            foreach($existingMappedTenantCategories as $existingMappedTenantCategory) {
                $idsArray[] = $existingMappedTenantCategory->getLocalCategory()->getId();
            }
            $idsString = implode(',', $idsArray);

            $sql = "SELECT categoryId FROM category WHERE categoryId";
            if (count($idsArray) > 0) {
                $sql .= " NOT IN ({$idsString}";
            }
            $sql.= " AND tenantId = 0";
            if (count($idsArray) > 0) {
                $sql .= ")";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll(\PDO::FETCH_COLUMN);

            foreach($results as $result) {
                $this->tenantCategoryMapperRepo->create([
                    'categoryId' => $result,
                    'tenantId' => $tenantId,
                    'mappedToId' => 0
                ]);
            }
            $data = ['message' => 'success'];
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        return $response->withHeader('Content-Type', 'application/json');
    }
}