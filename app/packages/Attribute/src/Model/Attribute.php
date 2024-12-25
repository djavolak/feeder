<?php
namespace EcomHelper\Attribute\Model;

use Skeletor\Core\Model\Model;

class Attribute extends Model
{
    public function __construct(
        private string $id,
        private string $name,
        private $position,
        private $isVisible,
        private $isFilter,
        private $groups = null,
        private $values = null,
        private ?\Datetime $createdAt = null,
        private ?\Datetime $updatedAt = null)
    {
        parent::__construct($createdAt, $updatedAt);
    }


    public function getIsVisible(): int
    {
        return $this->isVisible;
    }

    public function getIsFilter(): int
    {
        return $this->isFilter;
    }


    public function getPosition(): ?int
    {
        return $this->position;
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
     * @return int|null
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @return mixed
     */
    public function getValues()
    {
        return $this->values;
    }

}