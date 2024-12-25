<?php

require __DIR__ . '/../../vendor/autoload.php';
use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1');

$pheanstalk
    ->useTube('testtube')
    ->put(
        json_encode(['test' => 'data']),  // encode data in payload
        Pheanstalk::DEFAULT_PRIORITY,     // default priority
//        30, // delay by 30s
//        60  // beanstalk will retry job after 60s
    );

