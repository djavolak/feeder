<?php
namespace EcomHelper\Attribute\Model;

use Doctrine\Common\Collections\Collection;
use Skeletor\Core\Model\Model;

class AttributeGroup extends Model
{
    public function __construct(
        private string $id,
        private string $name,
        private ?Collection $attributes = null,
        private $categoryId = null,
        private ?\Datetime $createdAt = null,
        private ?\Datetime $updatedAt = null
    )
    {
        parent::__construct($createdAt, $updatedAt);
    }

    public function getAttributes()
    {
        return $this->attributes;
    }


    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }
    /**
     * @return int
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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