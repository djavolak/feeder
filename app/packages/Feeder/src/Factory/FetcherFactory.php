<?php
namespace EcomHelper\Feeder\Factory;

class FetcherFactory
{
    protected $dic;

    public function __construct(\DI\Container $container)
    {
        $this->dic = $container;
    }

    public function make(string $supplierName)
    {
        $className = '\\EcomHelper\\Feeder\\Service\\Fetcher\\' . ucfirst($supplierName);

        return $this->dic->get($className);
    }

}
