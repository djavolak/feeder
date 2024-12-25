<?php

namespace EcomHelper\Backend\Action;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;
use \League\Plates\Engine;
use Skeletor\Action\Web\Html;
use Laminas\Session\ManagerInterface as Session;
use Skeletor\Mapper\NotFoundException;
use Tamtamchik\SimpleFlash\Flash;

class Testing extends Html
{
    /**
     * @var Session
     */
    private $session;
    /**
     * @var Flash
     */
    private $flash;

    private $client;

    /**
     * HomeAction constructor.
     * @param Logger $logger
     * @param Config $config
     * @param Engine $template
     */
    public function __construct(
        Logger $logger, Config $config, Engine $template, Session $session, Flash $flash, Client $client
    ) {
        parent::__construct($logger, $config, $template);
        $this->flash = $flash;
        $this->session = $session;
        $this->client = $client;
        $this->setGlobalVariable('loggedIn', $this->session->getStorage()->offsetGet('loggedIn'));
    }

    /**
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request request
     * @param \Psr\Http\Message\ResponseInterface $response response
     *
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \Exception
     */
    public function __invoke(
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response
    ) {
        $token = '1d96d9ff3ad146f394fe604075254db40d6615ad0ba';
//        $targetUrl = 'https://shopee.co.id/ACE-Sparco-Alas-Jok-Mobil-Sporty-Hitam-i.229515338.7536182794';
        $targetUrl = 'https://www.tokopedia.com/kimkomstore/sparco-land-race-glove-fia-approved?src=topads';
//        $targetUrl = 'http://www.google.com';
//        $proxy = 'http://api.scrape.do?token=1d96d9ff3ad146f394fe604075254db40d6615ad0ba&url=https://httpbin.org/ip?json';
        $proxy = 'http://api.scrape.do';
        $url = sprintf('%s?token=%s&url=%s', $proxy, $token, urlencode($targetUrl));
        try {
            echo 'target: ' . $url . PHP_EOL;
            $headers = [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:99.0) Gecko/20100101 Firefox/99.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'DNT' => 1,
                'Connection' => 'keep-alive',
                'Upgrade-Insecure-Requests' => 1,
                'Sec-Fetch-Dest' => 'document',
                'Sec-Fetch-Mode' => 'navigate',
                'Sec-Fetch-Site' => 'same-origin',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'no-cache',
            ];
            $request = new Request('GET', $url, $headers);
            $response = $this->client->send($request);
        } catch (\Exception $e) {
            echo $e->getMessage();
            die();
        }

//        var_dump($response->getHeaders());
//        $this->getLogger()->debug($response->getBody()->getContents());
        echo $response->getBody()->getContents();
        die();

        return $this->respond('test', []);
    }

}