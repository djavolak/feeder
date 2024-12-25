<?php
namespace EcomHelper\Feeder\Model;

use Skeletor\Core\Model\Model;

class SourceProduct extends Model
{
    public function __construct(
        private ?string $id, private string $sourceSku, private string $sourceData, private string $sourceCat1,
        private string $sourceCat2, private string $sourceCat3, $createdAt = null, $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSourceSku()
    {
        return $this->sourceSku;
    }

    /**
     * @return string
     */
    public function getSourceData(): string
    {
        return $this->sourceData;
    }

    /**
     * @return string
     */
    public function getSourceCat1(): string
    {
        return $this->sourceCat1;
    }

    /**
     * @return string
     */
    public function getSourceCat2(): string
    {
        return $this->sourceCat2;
    }

    /**
     * @return string
     */
    public function getSourceCat3(): string
    {
        return $this->sourceCat3;
    }

}