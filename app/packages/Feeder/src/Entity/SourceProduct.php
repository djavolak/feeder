<?php
namespace EcomHelper\Feeder\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Product\Entity\Supplier;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Feeder\Model\SourceProduct as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'sourceProduct')]
class SourceProduct
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $sourceSku;

    #[ORM\Column(type: Types::JSON)]
    private string $sourceData;

    #[ORM\Column(type: Types::STRING)]
    private string $sourceCat1;
    #[ORM\Column(type: Types::STRING)]
    private string $sourceCat2;
    #[ORM\Column(type: Types::STRING)]
    private string $sourceCat3;

    #[ORM\ManyToOne(targetEntity: Supplier::class)]
    #[ORM\JoinColumn(name: 'supplierId', referencedColumnName: 'id')]
    private Supplier $supplier;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->sourceSku = $dto->getSourceSku();
        $this->sourceData = $dto->getSourceData();
        $this->sourceCat1 = $dto->getSourceCat1();
        $this->sourceCat2 = $dto->getSourceCat2();
        $this->sourceCat3 = $dto->getSourceCat3();
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getId()
    {
        return $this->id;
    }
}