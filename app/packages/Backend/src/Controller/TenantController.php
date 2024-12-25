<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Tenant\Service\Tenant;
use Laminas\Session\SessionManager as Session;
use Laminas\Config\Config;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;
use League\Plates\Engine;

class TenantController extends AjaxCrudController
{
    const TITLE_VIEW = "View tenant";
    const TITLE_CREATE = "Create new tenant";
    const TITLE_UPDATE = "Edit tenant: ";
    const TITLE_UPDATE_SUCCESS = "tenant updated successfully.";
    const TITLE_CREATE_SUCCESS = "tenant created successfully.";
    const TITLE_DELETE_SUCCESS = "tenant deleted successfully.";
    const PATH = 'tenant';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        Tenant $service, Session $session, Config $config, Flash $flash, Engine $template
    )
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }

}