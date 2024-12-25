<?php
namespace EcomHelper\Backend\Action\Feeder;

use EcomHelper\Feeder\Service\ParserFactory;
use Psr\Log\LoggerInterface as Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Parse
{
    /**
     * Handler for feed parse action
     *
     * @param ParserFactory $parserFactory
     */
    public function __construct(private ParserFactory $parserFactory, private Logger $logger)
    {
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
            $this->parserFactory->make($supplier)->parse();
        } catch (\Exception $e) {
            $this->logger->error('Error in parsing feed for supplier: ' . $supplier, ['exception' => $e, 'trace' => $e->getTraceAsString()]);
        }


        return $response->withStatus(201);
    }
}