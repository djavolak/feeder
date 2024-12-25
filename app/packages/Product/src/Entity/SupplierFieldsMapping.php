<?php
namespace EcomHelper\Product\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Product\Model\SupplierFieldsMapping as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'supplierFieldsMapping')]
class SupplierFieldsMapping
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $sourceFieldName;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private string $productFieldName;

    #[ORM\ManyToOne(targetEntity: Supplier::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'supplierId', referencedColumnName: 'id', unique: false)]
    private Supplier $supplier;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->productFieldName = $dto->getProductFieldName();
        $this->sourceFieldName = $dto->getSourceFieldName();
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