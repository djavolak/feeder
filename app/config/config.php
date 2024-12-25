<?php

date_default_timezone_set('Europe/Belgrade');

return [
    'appName' => 'knvrs',
    'timezone' => 'Europe/Belgrade',
    'adminPath' => '',
    'mailer' => [
        'from' => '',
        'fromName' => 'knv',
        'recipients' => [
            'errorNotice' => [
                'djavolak@mail.ru',
            ],
            'comments' => [
            ],
        ],
        'server' => [],
    ],
];