<?php
namespace EcomHelper\Backend\Controller;

use Skeletor\Activity\Service\Activity;
use Skeletor\Controller\AjaxCrudController;
use Laminas\Session\SessionManager as Session;
use Laminas\Config\Config;
use Tamtamchik\SimpleFlash\Flash;
use League\Plates\Engine;

class ActivityController extends AjaxCrudController
{
    const TITLE_VIEW = "View activity";
    const TITLE_CREATE = "Create new activity";
    const TITLE_UPDATE = "Edit activity: ";
    const TITLE_UPDATE_SUCCESS = "Activity updated successfully.";
    const TITLE_CREATE_SUCCESS = "Activity created successfully.";
    const TITLE_DELETE_SUCCESS = "Activity deleted successfully.";
    const PATH = 'activity';

    protected $tableViewConfig = ['writePermissions' => false, 'useModal' => true];

    /**
     * @param Activity $activity
     * @param Session $session
     * @param Config $config
     * @param Flash $flash
     * @param Engine $template
     */
    public function __construct(
        Activity $activity, Session $session, Config $config, Flash $flash, Engine $template
    ) {
        parent::__construct($activity, $session, $config, $flash, $template);
    }

}