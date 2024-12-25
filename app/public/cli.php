<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', '5400');

include(__DIR__ . "/../config/constants.php");
include(APP_PATH . "/vendor/autoload.php");

try {
    if (!isset($argv[1]) || !isset($argv[2])) {
        throw new \Exception('Required arguments are missing: <Controller> <methodName>');
    }
    /* @var \DI\Container $container */
    $container = require APP_PATH . '/config/backend/bootstrap.php';
    $app = new \Skeletor\Core\App\CliSkeletor($container, $container->get(\Psr\Log\LoggerInterface::class));
} catch (\Exception $e) {
    var_dump('could not start application. ' . $e->getMessage());
    exit();
}
try {
    unset($argv[0]);
    $app(...$argv);
} catch (\Exception $e) {
    $app->handleErrors($e);
    exit();
}
$app->respond();