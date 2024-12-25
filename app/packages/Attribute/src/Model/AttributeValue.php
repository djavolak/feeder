<?php

namespace EcomHelper\Attribute\Model;

use EcomHelper\Image\Model\Image;
use Skeletor\Core\Model\Model;

class AttributeValue extends Model
{
    /**
     * @return string|null
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @param string $id
     * @param $attribute
     * @param string $value
     * @param Image|null $image
     * @param string|null $link
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     */
public function __construct(
        private string $id, private string $value, private ?Image $image = null, private $attribute = null,
        private ?string $link = null, ?\DateTime $createdAt = null, ?\DateTime $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return Image|null
     */
    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}