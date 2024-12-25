<?php
namespace EcomHelper\Product\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Product\Model\SourceProduct as DtoModel;
use Skeletor\Core\Entity\Timestampable;

#[ORM\Entity]
#[ORM\Table(name: 'sourceProduct')]
class SourceProduct
{
    use Timestampable;

    #[ORM\Column(type: Types::JSON)]
    public array $productData;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public string $cat1;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public string $cat2;
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    public string $cat3;
    #[ORM\Column(type: Types::STRING)]
    public string $supplierProductId;

    #[ORM\ManyToOne(targetEntity: Supplier::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'supplierId', referencedColumnName: 'id', unique: false)]
    private Supplier $supplier;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->productData = $dto->getProductData();
        $this->supplierProductId = $dto->getSupplierProductIt();
        $this->cat1 = $dto->getCat1();
        $this->cat2 = $dto->getCat2();
        $this->cat3 = $dto->getCat3();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }
}