<?php
namespace EcomHelper\Backend\Controller;

use Laminas\Session\ManagerInterface;
use Skeletor\User\Filter\Login as Filter;
use Laminas\Config\Config;
use Tamtamchik\SimpleFlash\Flash;
use League\Plates\Engine;

class LoginController extends \Skeletor\Login\Controller\LoginController
{
    const LOGGED_OUT = 'Uspešno ste se izlogovali.';

    const LOGIN_FORM_PATH = '/login/loginForm/';

}