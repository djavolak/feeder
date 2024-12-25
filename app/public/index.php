<?php

use Psr\Log\LoggerInterface;
use Skeletor\Core\App\WebSkeletor;
use Tracy\Debugger;

error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../config/constants.php");
include(APP_PATH . "/vendor/autoload.php");
//$path = getenv('APPLICATION');
$path = 'backend';
if (defined(PROJECT_NAME)) {
    Tracy\Debugger::$editor = 'phpstorm://open?file=%file&line=%line&project=' . PROJECT_NAME;
}
\Tracy\Debugger::enable(Debugger::Development);

try {
    /* @var \DI\Container $container */
    $container = require sprintf('%s/config/bootstrap.php', APP_PATH);
    $app = new WebSkeletor($container, $container->get(LoggerInterface::class));
} catch (\Exception $e) {
    if (isset($app) && $app) {
        $app->handleErrors($e);
        exit();
    }
    // @TODO handle better
    echo 'There was an unknown error with application. More info: ' . $e->getMessage() . PHP_EOL;
    echo '********************* Stack trace **********************************' . PHP_EOL;
    var_dump($e->getTrace());
    exit();
}
$app->respond();