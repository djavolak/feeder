<?php

namespace EcomHelper\Backend\Action\Feeder;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class FeedStart
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        ini_set('max_execution_time', 0);
        $supplerCode = $request->getAttribute('supplier');
        if ($supplerCode) {
            exec(sprintf('php %s/cli.php parse %s', PUBLIC_PATH, $supplerCode), $output, $resultCode);
            exec(sprintf('php %s/cli.php create %s', PUBLIC_PATH, $supplerCode), $output, $resultCode);
            exec(sprintf('php %s/cli.php update %s', PUBLIC_PATH, $supplerCode), $output, $resultCode);
            if ($resultCode !== 0) {
                echo '<script>setTimeout(function(){window.close()},3000)</script>';
                die('Failed to start feed sync');
            }
            echo '<script>setTimeout(function(){window.close()}, 3000)</script>';
            die('Starting feed sync');
        }
    }

}