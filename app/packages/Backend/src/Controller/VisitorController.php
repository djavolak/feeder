<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Visitor\Service\Visitor;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class VisitorController extends AjaxCrudController
{
    const TITLE_VIEW = "View visitor";
    const TITLE_CREATE = "Create new visitor";
    const TITLE_UPDATE = "Edit visitor: ";
    const TITLE_UPDATE_SUCCESS = "visitor updated successfully.";
    const TITLE_CREATE_SUCCESS = "visitor created successfully.";
    const TITLE_DELETE_SUCCESS = "visitor deleted successfully.";
    const PATH = 'visitor';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        Visitor $service,
        Session $session,
        Config $config,
        Flash $flash,
        Engine $template
    ) {
        parent::__construct($service, $session, $config, $flash, $template);
    }
}