<?php

namespace EcomHelper\Post\Model;

use EcomHelper\Category\Model\Category;
use Skeletor\Core\Model\Model;

class Post extends Model
{
    public function __construct(
        private string $id,
        private string $title,
        private string $description,
        private Category|null $category,
        $createdAt = null,
        $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

}