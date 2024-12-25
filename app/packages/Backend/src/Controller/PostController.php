<?php
namespace EcomHelper\Backend\Controller;

use EcomHelper\Post\Service\Post;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Core\Controller\AjaxCrudController;
use Tamtamchik\SimpleFlash\Flash;

class PostController extends AjaxCrudController
{
    const TITLE_VIEW = "View post";
    const TITLE_CREATE = "Create new post";
    const TITLE_UPDATE = "Edit post: ";
    const TITLE_UPDATE_SUCCESS = "post updated successfully.";
    const TITLE_CREATE_SUCCESS = "post created successfully.";
    const TITLE_DELETE_SUCCESS = "post deleted successfully.";
    const PATH = 'post';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(
        Post $service, Session $session, Config $config, Flash $flash, Engine $template
    )
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }

}