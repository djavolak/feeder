<?php
namespace EcomHelper\Backend\Action\Feeder;

use EcomHelper\Feeder\Service\Updater;
use EcomHelper\Product\Service\Product;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Config\Config;
use League\Plates\Engine;

class Update
{
    /**
     * Handler for feed update action
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(private Updater $updater, private Product $productService) {
        ini_set('max_execution_time', 1200);
    }

    /**
     *
     * @param ServerRequestInterface $request request
     * @param ResponseInterface $response response
     *
     * @return ResponseInterface
     * @throws \Exception
     * @throws GuzzleException
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $this->updater->process($request->getAttribute('supplier'));

        return $response->withStatus(201);
    }

}