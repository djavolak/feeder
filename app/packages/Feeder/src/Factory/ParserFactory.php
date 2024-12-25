<?php
namespace EcomHelper\Feeder\Service;

class ParserFactory
{
    protected $dic;

    public function __construct(\DI\Container $container)
    {
        $this->dic = $container;
    }

    public function make(string $supplierName): ParserInterface
    {
        $className = '\\EcomHelper\\Feeder\\Service\\Supplier\\' . ucfirst($supplierName);

        return $this->dic->get($className);
    }

}
