<?php
namespace EcomHelper\Backend\Action;

use EcomHelper\Category\Service\Category;
use EcomHelper\Product\Repository\CategoryRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;
use League\Plates\Engine;

class Categories
{

    private $logger;

    private $category;

    private $config;

    /**
     * HomeAction constructor.
     * @param Logger $logger
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(Logger $logger, Category $category, Config $config) {
        $this->logger = $logger;
        $this->category = $category;
        $this->config = $config;
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
            $tenantId = (int) $request->getAttribute('tenantId');
            if (!$tenantId) {
                throw new \InvalidArgumentException('Tenant id not provided.');
            }
            $data = [];
            foreach ($this->category->fetchForApi($tenantId) as $category) {
                $parents = [];
                if ($category->getLevel() !== 1) {
                    if ($category->getParent()->getLevel() === 2) {
                        $parents[] = $category->getParent()->getParent()->getId();
                    }
                    $parents[] = $category->getParent()->getId();
                }
                if (!$category->getStatus()) {
                    continue;
                }
                $image = $category->getImage();
                if (is_object($image)) {
                    $image = $this->config->offsetGet('baseUrl') .'/images'. $image->getFilename();
                }
                $data[] = [
                    'id' => $category->getId(),
                    'title' => $category->getTitle(),
                    'description' => $category->getDescription(),
                    'slug' => $category->getSlug(),
                    'image' => $image,
                    'secondDescription' => $category->getSecondDescription(),
                    'level' => $category->getLevel(),
                    'parents' => count($parents) > 0 ? implode(',', $parents) : null,
                ];
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }
        $response->getBody()->write(json_encode($data));
        $response->getBody()->rewind();

        return $response->withHeader('Content-Type', 'application/json');
    }

}