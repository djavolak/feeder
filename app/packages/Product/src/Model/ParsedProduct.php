<?php
namespace EcomHelper\Product\Model;

use EcomHelper\Category\Model\Category;
use Skeletor\Core\Model\Model;

class ParsedProduct extends Model
{
    public function __construct(
        private string $id,
        private string $title,
        private array|string $description,
        private string $sku,
        private int $inputPrice,
        private string $quantity,
        private string $supplierCategory,
        private array $meta,
        private string $sourceProductId,
        private ?string $barcode = null,
        private ?string $cat1 = null,
        private ?string $cat2 = null,
        private ?string $cat3 = null,
        private ?string $ean = null,
        private ?array $attributes = null,
        private ?array $images = null,
        private ?Supplier $supplier = null,
        ?\Datetime $createdAt = null,
        ?\Datetime $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * @return string
     */
    public function getSupplierCategory(): ?string
    {
        if (count(explode('-', $this->supplierCategory)) === 2) {
            $this->supplierCategory .= '-';
        }

        return $this->supplierCategory;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return string
     */
    public function getDescription(): array|string
    {
        return $this->description;
    }

    /**
     * @return int
     */
    public function getQuantity(): string
    {
        return $this->quantity;
    }

    /**
     * @return string
     */
    public function getBarcode(): string|null
    {
        return $this->barcode;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @return string
     */
    public function getEan(): string|null
    {
        return $this->ean;
    }

    /**
     * @return Supplier
     */
    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

    /**
     * @return int
     */
    public function getInputPrice(): int
    {
        return $this->inputPrice;
    }

    /**
     * @return string
     */
    public function getSku(): string
    {
        return $this->sku;
    }

    public function getCat1(): string|null
    {
        return $this->cat1;
    }

    public function getCat2(): string|null
    {
        return $this->cat2;
    }

    public function getCat3(): string|null
    {
        return $this->cat3;
    }

    public function getSourceProductId(): string
    {
        return $this->sourceProductId;
    }

}