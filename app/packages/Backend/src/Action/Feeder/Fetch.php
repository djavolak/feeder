<?php
namespace EcomHelper\Backend\Action\Feeder;

use EcomHelper\Feeder\Factory\FetcherFactory;
use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Fetch
{
    /**
     * Handler for feed parse action
     *
     * @param FetcherFactory $fetcherFactory
     */
    public function __construct(
        private FetcherFactory $fetcherFactory, private Logger $logger
    ) {
        ini_set('max_execution_time', 1200);
    }

    /**
     *
     * @param ServerRequestInterface $request request
     * @param ResponseInterface $response response
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $supplier = $request->getAttribute('supplier');
        try {

            $this->fetcherFactory->make($supplier)->fetch();

        } catch (\Exception $e) {
            $error = sprintf('Could not fetch feed for %s: %s', $supplier, $e->getMessage());
            $this->logger->error($error, ['exception' => $e, 'trace' => $e->getTraceAsString()]);
            echo $error;
        }

        return $response->withStatus(200);
    }
}