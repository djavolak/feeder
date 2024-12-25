<?php

namespace EcomHelper\Feeder\Service;

use EcomHelper\Product\Model\Supplier;

class Monitor
{
    private Supplier $supplier;

    public function __construct(protected \Redis $redis)
    {}

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function resetMonitor()
    {
        $this->redis->set($this->supplier->getCode() . '#feedMonitor', serialize([]));
    }

    public function addMonitorInfo($msg)
    {
        $dt = new \DateTime('now', new \DateTimeZone('Europe/Belgrade'));
        $key = $this->supplier->getCode() . '#feedMonitor';
        $data = unserialize($this->redis->get($key));
        $data[] = [
            'message' => $msg,
            'timestamp' => $dt->format('d. F H:i')
        ];
        $this->redis->set($key, serialize($data));
    }
}