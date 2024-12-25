<?php
namespace EcomHelper\Feeder\Service\Fetcher;

use EcomHelper\Feeder\Entity\SourceProduct;
use EcomHelper\Product\Entity\Supplier;

class Dsc extends AbstractFetcher
{
    public function fetch()
    {
        $startTime = $this->getMicrotime();
        $supplier = $this->em->getRepository(Supplier::class)->find($this->supplier->getId());
        $key = get_class() . '#feed';
        $xml = $this->redis->get($key);
        $xml = false;
        // tmp
        if ($xml === false) {
            $response = $this->httpClient->get($this->supplier->getFeedSource());
            $xml = $response->getBody()->getContents();
            $this->redis->set($key, $xml);
        }
        $xml = new \SimpleXMLElement($xml);
        $msg = 'data fetched in ' . $this->getMicrotime() - $startTime;
        $this->addMonitorInfo($msg);
        $key = 0;
        foreach ($xml->xpath('//artikal') as $itemData) {
            $key++;
            if ($key > 500) {
                $this->em->flush();
            }
            $itemData = (array) $itemData;
            $images = [];
            foreach ($itemData['slike']->slika as $img) {
                $images[] = (string) $img;
            }
            $itemData['slike'] = $images;
            $sourceProduct = $this->em->getRepository(SourceProduct::class)->findOneBy(['sourceSku' => $itemData['sifra']]);
            $sourceProductDto = [
                'id' => null,
                'sourceSku' => $itemData['sifra'],
                'sourceData' => json_encode($itemData),
                'sourceCat1' => $itemData['nadgrupa'],
                'sourceCat2' => $itemData['grupa'],
                'sourceCat3' => ''
            ];
            if (!$sourceProduct) {
                $sourceProduct = new SourceProduct();
                $sourceProductDto['id'] = \Ramsey\Uuid\Uuid::uuid4();
            }
            $sourceProduct->populateFromDto(new \EcomHelper\Feeder\Model\SourceProduct(...$sourceProductDto));
            $sourceProduct->setSupplier($supplier);
            $this->em->persist($sourceProduct);
        }
        $this->em->flush();
        $msg = 'data saved in ' . $this->getMicrotime() - $startTime;
        $this->addMonitorInfo($msg);
        echo $msg;
    }
}