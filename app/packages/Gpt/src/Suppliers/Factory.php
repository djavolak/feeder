<?php

namespace EcomHelper\Gpt\Suppliers;

use EcomHelper\Gpt\Service\GptApi;
use Laminas\Config\Config;

class Factory
{
    /**
     * @throws \Exception
     */
    public function __Invoke(Config $config, $supplierId): GptApi
    {
        match ($supplierId) {
            9, 5 => $gptForSupplier = new Ewe\Ewe($config),
            default => throw new \Exception('Supplier not found')
        };
        return $gptForSupplier;
    }
}