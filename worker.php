<?php

require __DIR__ . '/../../vendor/autoload.php';
use Pheanstalk\Pheanstalk;

$pheanstalk = Pheanstalk::create('127.0.0.1');

$pheanstalk->watch('testtube');



for ($i=0; $i<20; $i++) {
    try {
        // this hangs until a Job is produced.
        $job = $pheanstalk->reserve();

        var_dump($job);

        $jobPayload = $job->getData();

//    var_dump($jobPayload);
        file_put_contents(microtime(true), $jobPayload);

        // If it's going to take a long time, periodically
        // tell beanstalk we're alive to stop it rescheduling the job.
//    $pheanstalk->touch($job);
//    sleep(2);

        // eventually we're done, delete job.
        $pheanstalk->delete($job);
    } catch(\Exception $e) {
        $pheanstalk->release($job);
    }
}


exit(98);