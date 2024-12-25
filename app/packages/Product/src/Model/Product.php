<?php
namespace EcomHelper\Product\Model;

use EcomHelper\Category\Model\Category;
use Skeletor\Core\Model\Model;

class Product extends Model
{
    const STATUS_PRIVATE = 1;
    const STATUS_PUBLISH = 2;
    const STATUS_DRAFT = 3;
    const STATUS_SOURCE_REMOVED = 4;

    const STOCK_STATUS_INSTOCK = 1;
    const STOCK_STATUS_OUTSTOCK = 0;

    /**
     * @param int $productId
     * @param string $title
     * @param array $images
     * @param int $price
     * @param string $description
     * @param string $shortDescription
     * @param int $specialPrice
     * @param ?string $specialPriceFrom
     * @param ?string $specialPriceTo
     * @param int $status
     * @param int $stockStatus
     * @param int $quantity
     * @param Category $category
     * @param string $weight
     * @param string $barcode
     * @param array $attributes
     * @param string $ean
     * @param Supplier $supplier
     * @param string $supplierProductId
     * @param int $inputPrice
     * @param string $sku
     * @param \Datetime $createdAt
     * @param \Datetime $updatedAt
     */
    public function __construct(
        private string $id,
        private string $title,
        private ?string $slug,
        private array $images,
        private int $price,
        private array|string $description,
        private array|string $shortDescription,
        private int $specialPrice,
        private ?string $specialPriceFrom,
        private ?string $specialPriceTo,
        private int $status,
        private int $stockStatus,
        private int $quantity,
        private string $barcode,
        private array $attributes,
        private string $ean,
        private string $supplierProductId,
        private int $inputPrice,
        private string $sku,
        private string $parsedProductId,
        private int $ignoreStatusChange = 0,
        private int $fictionalDiscountPercentage = 0,
        private ?string $supplierCategory = null,
        private ?Category $category = null,
        private ?Supplier $supplier = null,
        private ?string $mappingId = null,
        private ?int $salePriceLoop = null,
        private array $tags = [],
        ?\Datetime $createdAt = null,
        ?\Datetime $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    public function getParsedProductId(): string
    {
        return $this->parsedProductId;
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public static function getHRStockStatus($status)
    {
        return static::getHRStockStatuses()[$status];
    }

    public static function getHRStockStatuses()
    {
        return [
            static::STOCK_STATUS_INSTOCK => 'Instock',
            static::STOCK_STATUS_OUTSTOCK => 'Outofstock',
        ];
    }

    public static function getHRStatus($status)
    {
        return static::getHRStatuses()[$status];
    }

    public static function getHRStatuses()
    {
        return [
            static::STATUS_PRIVATE => 'Private',
            static::STATUS_PUBLISH => 'Publish',
            static::STATUS_DRAFT => 'Draft',
            static::STATUS_SOURCE_REMOVED => 'Removed',
        ];
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
    public function getSlug()
    {
        return $this->slug;
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
     * @return int
     */
    public function getPrice(): int
    {
        return $this->price;
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
    public function getSpecialPrice(): int
    {
        return $this->specialPrice;
    }

    /**
     * @return ?string
     */
    public function getSpecialPriceFrom(): ?string
    {
        return $this->specialPriceFrom;
    }

    /**
     * @return ?string
     */
    public function getSpecialPriceTo(): ?string
    {
        return $this->specialPriceTo;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return int
     */
    public function getStockStatus(): int
    {
        return $this->stockStatus;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }

    /**
     * @return Category
     */
    public function getCategory(): Category
    {
        return $this->category;
    }

    /**
     * @return string
     */
    public function getBarcode(): string
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
    public function getEan(): string
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
     * @return string
     */
    public function getSupplierProductId(): string
    {
        return $this->supplierProductId;
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

    /**
     * @return string|null
     */
    public function getShortDescription(): array|string
    {
        return $this->shortDescription;
    }

    public function getIgnoreStatusChange(): int
    {
        return $this->ignoreStatusChange;
    }

    /**
     * @return int
     */
    public function getFictionalDiscountPercentage(): int
    {
        return $this->fictionalDiscountPercentage;
    }

    /**
     * @return int|null
     */
    public function getMappingId(): ?string
    {
        return $this->mappingId;
    }

    public function getSalePriceLoop(): ?int
    {
        return $this->salePriceLoop;
    }
}