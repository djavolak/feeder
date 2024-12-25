<?php
namespace EcomHelper\Product\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Product\Model\ParsedProduct as DtoModel;
use Skeletor\Core\Entity\Timestampable;

#[ORM\Entity]
#[ORM\Table(name: 'parsedProduct')]
class ParsedProduct
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING, length: 512)]
    public string $title;
    #[ORM\Column(type: Types::TEXT)]
    public string $description;
    #[ORM\Column(type: Types::STRING)]
    public string $sku;
    #[ORM\Column(type: Types::INTEGER)]
    public int $inputPrice;
    #[ORM\Column(type: Types::STRING)]
    public string $quantity;
    #[ORM\Column(type: Types::STRING)]
    public string $supplierCategory;  // used for filter
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public string $attributes;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public string $barcode;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public string $ean;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public string $cat1;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    public string $cat2;
    #[ORM\Column(type: Types::JSON, nullable: true)]
    public array $meta;
    #[ORM\Column(type: Types::STRING, updatable: false, nullable: true)]
    public string $cat3;

    #[ORM\Column(type: Types::STRING)]
    public string $sourceProductId; // make relation

    #[ORM\ManyToOne(targetEntity: Supplier::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'supplierId', referencedColumnName: 'id', unique: false)]
    public Supplier $supplier;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->title = $dto->getTitle();
        $this->description = $dto->getDescription();
        $this->sku = $dto->getSku();
        $this->inputPrice = $dto->getInputPrice();
        $this->quantity = $dto->getQuantity();
        $this->supplierCategory = $dto->getSupplierCategory();
        $this->attributes = json_encode($dto->getAttributes());
        $this->barcode = $dto->getBarcode();
        $this->ean = $dto->getEan();
        $this->cat1 = $dto->getCat1();
        $this->cat2 = $dto->getCat2();
        $this->cat3 = $dto->getCat3();
        $this->sourceProductId = $dto->getSourceProductId();
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