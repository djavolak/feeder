<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Product\Service\Supplier;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class SupplierController extends AjaxCrudController
{
    const TITLE_VIEW = "View supplier";
    const TITLE_CREATE = "Create new supplier";
    const TITLE_UPDATE = "Edit supplier: ";
    const TITLE_UPDATE_SUCCESS = "supplier updated successfully.";
    const TITLE_CREATE_SUCCESS = "supplier created successfully.";
    const TITLE_DELETE_SUCCESS = "supplier deleted successfully.";
    const PATH = 'supplier';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    /**
     * @param Supplier $service
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     */
    public function __construct(
        Supplier $service, Session $session, Config $config, protected Flash $flash, Engine $template,
        private \Redis $redis
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }

    public function monitor()
    {
        $data = [
            'dsc' => unserialize($this->redis->get('dsc#feedMonitor')),
            'uspon' => unserialize($this->redis->get('uspon#feedMonitor'))
        ];

        return $this->respondPartial('monitor', [
            'data' => $data,
        ]);
    }
}