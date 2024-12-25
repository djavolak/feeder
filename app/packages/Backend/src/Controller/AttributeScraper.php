<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Feeder\Service\AttributeWebScraper;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Skeletor\Controller\Controller;
use Tamtamchik\SimpleFlash\Flash;

class AttributeScraper extends Controller
{
    public function __construct(
        Session $session, Config $config, Flash $flash, Engine $template, private \Redis $cache
    )
    {
        parent::__construct($template, $config, $session, $flash, );
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    public function irisMega(): void
    {
        $attributes = (new AttributeWebScraper($this->cache))(6, '004914');
        var_dump($attributes);
        die();
    }

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    public function roaming(): void
    {
        $attributes = (new AttributeWebScraper($this->cache))(7, '194888');
        var_dump($attributes);
        die();
    }

    public function asbis(): void
    {
        $attributes = (new AttributeWebScraper($this->cache))(8, 'WD8003FFBX');
        var_dump($attributes);
        die();
    }

    public function uspon()
    {
        $attributes = (new AttributeWebScraper($this->cache))(3, '4047443480521');
        var_dump($attributes);
        die();
    }

    public function ewe()
    {
        $attributes = (new AttributeWebScraper($this->cache))(9, 'sca00501');
        var_dump($attributes);
        die();
    }
}