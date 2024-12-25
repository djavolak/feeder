<?php
namespace EcomHelper\Backend\Action;

use EcomHelper\Feeder\Service\ParserFactory;
use EcomHelper\Product\Service\Product;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface as Logger;

class Import
{

    private $logger;

    private $service;

    /**
     * @param Logger $logger
     * @param ParserFactory $parserFactory
     */
    public function __construct(Logger $logger, Product $product)
    {
        $this->logger = $logger;
        $this->service = $product;
        ini_set('max_execution_time', 1200);
    }

    /**
     * Import product data from wp tables
     *
     * @param ServerRequestInterface $request request
     * @param ResponseInterface $response response
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        foreach ($this->service->fetchOldData(0, 25000) as $data) {
            $product = $this->service->getEntities(['sku' => $data['meta_value']]);

            if (count($product)) {
                $product = $product[0];
                if (false !== strpos($data['post_title'], $product->getTitle())) {
                    if ($product->getDescription() === 'neki opis') {
                        $desc = '';
                        if ($data['post_content']) {
                            $desc = $data['post_content'];
                        }
                        $this->service->updateField('description', $desc, $product->getId());
                    }
                    if (!$product->getSlug()) {
                        $this->service->updateField('slug', $data['post_name'], $product->getId());
                    }

                    if ($product->getStatus() === \EcomHelper\Product\Model\Product::STATUS_PUBLISH
                        && $data['post_status'] !== 'publish') {
                        $this->service->updateField('status', 3, $product->getId()); // draft
                    }
                } else {
                    if ($product->getTitle() === 'neki naslov') {
                        $this->service->updateField('title', $data['post_title'], $product->getId());
                    } else {
                        if (false === strpos(html_entity_decode($data['post_title']), $product->getTitle())) {
                            var_dump($data['post_title']);
                            var_dump(html_entity_decode($data['post_title']));
                            var_dump($product->getTitle());
//                            die('mismatch ?');
                        }
                    }
                }
            }
        }
        die('done');


    }

}