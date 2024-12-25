<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Product\Service\SupplierFieldsMapping as Mapping;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class SupplierFieldsMappingController extends AjaxCrudController
{
    const TITLE_VIEW = "View mapping";
    const TITLE_CREATE = "Create new mapping";
    const TITLE_UPDATE = "Edit mapping: ";
    const TITLE_UPDATE_SUCCESS = "mapping updated successfully.";
    const TITLE_CREATE_SUCCESS = "mapping created successfully.";
    const TITLE_DELETE_SUCCESS = "mapping deleted successfully.";
    const PATH = 'supplier-field-mapping';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    /**
     * @param Mapping $service
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     */
    public function __construct(
        Mapping $service, Session $session, Config $config, protected Flash $flash, Engine $template,
        private \Redis $redis
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }


}