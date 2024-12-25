<?php

namespace EcomHelper\Attribute\Model;

use Skeletor\Core\Model\Model;

class SecondDescriptionAttributeCategory extends Model
{
    public function __construct(
        private int $secondDescriptionAttributeCategoryId,
        private int $categoryId,
        private int $attributeId,
        private ?\Datetime $createdAt,
        private ?\Datetime $updatedAt)
    {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->secondDescriptionAttributeCategoryId;
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->categoryId;
    }

    /**
     * @return int
     */
    public function getAttributeId(): int
    {
        return $this->attributeId;
    }

    /**
     * @return \Datetime|null
     */
    public function getCreatedAt(): ?\Datetime
    {
        return $this->createdAt;
    }

    /**
     * @return \Datetime|null
     */
    public function getUpdatedAt(): ?\Datetime
    {
        return $this->updatedAt;
    }


}