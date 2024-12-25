<?php
namespace EcomHelper\Feeder\Service\Fetcher;

use Doctrine\ORM\EntityManagerInterface;
use EcomHelper\Feeder\Service\Monitor;
use EcomHelper\Product\Service\Supplier;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface as Logger;
use Laminas\Config\Config;

abstract class AbstractFetcher
{
    protected \EcomHelper\Product\Model\Supplier $supplier;

    public function __construct(
        protected Client $httpClient, protected Logger $logger, protected Config $config, private Monitor $monitor,
        protected EntityManagerInterface $em, private Supplier $supplierService, protected \Redis $redis
    ) {
        $parts = explode('\\', get_class($this));
        $this->supplier = $this->supplierService->getEntities(['code' => strtolower($parts[count($parts) - 1])])[0];
        $msg = sprintf("Feed for %s started.", $this->supplier->getName());
        $this->monitor->setSupplier($this->supplier);
        $this->monitor->resetMonitor();
        $this->addMonitorInfo($msg);
    }

    public function addMonitorInfo($msg)
    {
        $this->monitor->addMonitorInfo($msg);
    }

    abstract protected function fetch();

    protected function getMicrotime()
    {
        [$usec, $sec] = explode(" ", microtime());

        return ((float) $usec + (float) $sec);
    }

}
