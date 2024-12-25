<?php
namespace EcomHelper\Backend\Action\Feeder;

use EcomHelper\Feeder\Service\Creator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Config\Config;
use League\Plates\Engine;

class Create
{
    /**
     * Handler for feed create action
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(private Creator $creator) {
        ini_set('max_execution_time', 60 * 60 * 2);
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
        $this->creator->process($request->getAttribute('supplier'));

        return $response->withStatus(201);
    }

}