<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Service\ParsedProduct;
use EcomHelper\Product\Service\Product;
use EcomHelper\Product\Service\Supplier;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Blog\Service\UrlHelper;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class ParsedProductController extends AjaxCrudController
{
    const TITLE_VIEW = "View parsed products";
    const TITLE_CREATE = "";
    const TITLE_UPDATE = "View parsed product: ";
//    const TITLE_UPDATE_SUCCESS = "mapping updated successfully.";
//    const TITLE_CREATE_SUCCESS = "mapping created successfully.";
    const TITLE_DELETE_SUCCESS = "parsed product deleted successfully.";
    const PATH = 'parsed-product';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    /**
     * @param ParsedProduct $service
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     */
    public function __construct(
        ParsedProduct $service, Session $session, Config $config, protected Flash $flash, Engine $template,
        private \Redis $redis, private Supplier $supplier, private Product $product, private ProductRepository $productRepo
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    public function parse()
    {
        $supplier = $this->supplier->getEntities(['name' => 'Dsc'])[0];
        foreach ($this->service->getEntities(['supplier' => $supplier->getId()]) as $parsedProduct) {
            $this->service->parse($parsedProduct, $supplier);
        }
    }

    public function create(): Response
    {
        throw new \Exception('not used');
    }

    public function update(): Response
    {
        throw new \Exception('not used');
    }
}