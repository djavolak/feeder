<?php

$guest = [
    '/',
    '/test/',
    '/feeder/*',
    '/items/*',
    '/product/json/*',
    '/categories*',
    '/login/loginForm/',
    '/login/login/',
    '/login/resetPassword',
    '/cron/*',
    '/productImport/*',

    '/index*',
];

$level2 = [
    '/post*',
    '/',

    '/user/form/*',
    '/user/view/*',
    '/visitor/*',
    '/source-product/*',
    '/parsed-product/*',
    '/user/update/*',
    '/product/*',
    '/image/*',
    '/attribute/*',
    '/attribute-values/*',
    '/attribute-group/*',
    '/supplier/*',
    '/category/*',
    '/table-view/*',
    '/category-mapper/*',
    '/category-tenant-settings/*',
    '/login/logout/',
    '/margin-groups/*',
    '/tag/*',
    '/category-parsing-rules/*',
    '/attribute-mapping/*',
    '/supplier-field-mapping/*'
];

$level1 = [
    '/cache/*',
    '/user/*',
    '/tenant/*',
    '/template/*',
    '/translator/*',
    '/activity/*',
    '/fixImages/*',
    '/feedStart/*',
    '/scraper/*'
];

//can also see everything level2 can see
$level1 = array_merge($level2, $level1);

return [
    0 => $guest,
    1 => $level1,
    2 => $level2
];