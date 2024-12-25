<?php

namespace EcomHelper\Feeder\Model;

use EcomHelper\Attribute\Model\Attribute;
use EcomHelper\Attribute\Model\AttributeValue;
use EcomHelper\Category\Model\Category;
use Skeletor\Model\Model;

class SupplierAttributeMapping extends Model
{
    public function __construct(
        private int $supplierAttributeId,
        private \EcomHelper\Product\Model\Supplier $supplier,
        private string $attribute,
        private string $attributeValue,
        private ?array $localAttributes,
        private ?int $mapped,
        private ?Category $category,
        private ?\DateTime $createdAt,
        private ?\DateTime $updatedAt
    )
    {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->supplierAttributeId;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return string
     */
    public function getAttributeValue(): string
    {
        return $this->attributeValue;
    }

    /**
     * @return Attribute[]|null
     */
    public function getLocalAttributes(): ?array
    {
        return $this->localAttributes;
    }
    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return \EcomHelper\Product\Model\Supplier
     */
    public function getSupplier(): \EcomHelper\Product\Model\Supplier
    {
        return $this->supplier;
    }

    /**
     * @return int|null
     */
    public function getMapped(): ?int
    {
        return $this->mapped;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }
}