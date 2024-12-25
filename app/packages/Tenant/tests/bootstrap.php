<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include(__DIR__ . '/../../../vendor/dj_avolak/skeletor/tests/Stubs/CsrfStub.php');
include(__DIR__ . '/../../../vendor/dj_avolak/skeletor/tests/Stubs/UserStub.php');
include(__DIR__ . '/../../../vendor/dj_avolak/skeletor/tests/Stubs/TemplateStub.php');

require __DIR__ . '/../../../config/constants.php';
require __DIR__ . '/../../../vendor/autoload.php';
