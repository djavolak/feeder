<?php
namespace EcomHelper\Product\Model;

use Skeletor\Core\Model\Model;

class SourceProduct extends Model
{
    public function __construct(
        private string $id,
        private string $supplierProductId,
        private array $productData,
        private ?string $cat1 = null,
        private ?string $cat2 = null,
        private ?string $cat3 = null,
        private ?Supplier $supplier = null,
        ?\Datetime $createdAt = null,
        ?\Datetime $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Supplier
     */
    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    public function getCat1(): ?string
    {
        return $this->cat1;
    }

    public function getCat2(): ?string
    {
        return $this->cat2;
    }

    public function getCat3(): ?string
    {
        return $this->cat3;
    }

    public function getSupplierProductId(): string
    {
        return $this->supplierProductId;
    }

    public function getProductData(): array
    {
        return $this->productData;
    }
}