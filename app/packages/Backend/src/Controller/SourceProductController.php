<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Product\Service\SourceProduct;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class SourceProductController extends AjaxCrudController
{
    const TITLE_VIEW = "View source products";
    const TITLE_CREATE = "";
    const TITLE_UPDATE = "View source product: ";
//    const TITLE_UPDATE_SUCCESS = "mapping updated successfully.";
//    const TITLE_CREATE_SUCCESS = "mapping created successfully.";
    const TITLE_DELETE_SUCCESS = "source product deleted successfully.";
    const PATH = 'source-product';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    /**
     * @param SourceProduct $service
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     */
    public function __construct(
        SourceProduct $service, Session $session, Config $config, protected Flash $flash, Engine $template,
        private \Redis $redis
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
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