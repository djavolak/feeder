<?php

namespace EcomHelper\Feeder\Model;

use EcomHelper\Category\Model\Category;
use EcomHelper\Feeder\ParsingActions\AddAttributeBasedOnString;
use EcomHelper\Feeder\ParsingActions\ChangeCategoryBasedOnString;
use EcomHelper\Feeder\ParsingActions\SearchReplace;
use EcomHelper\Product\Model\Supplier;
use Skeletor\Model\Model;

class CategoryParsingRule extends Model
{
    public static array $supportedRules = [
        SearchReplace::class => 'Search and replace',
        ChangeCategoryBasedOnString::class => 'Change category based on string',
        AddAttributeBasedOnString::class => 'Add attribute based on string',
    ];
    public function __construct(private ?\EcomHelper\Product\Model\Supplier $supplier, private string $action,
        private string $data, $createdAt, $updatedAt,private array $categories = [], private ?int $categoryParsingRuleId = null, private ?int $priority = null,private ?string $description = '')
    {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->categoryParsingRuleId;
    }

    /**
     * @return Supplier|null
     */
    public function getSupplier(): ?\EcomHelper\Product\Model\Supplier
    {
        return $this->supplier;
    }

    /**
     * @return Category[]
     */
    public function getCategories(): array
    {
        return $this->categories;
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getPriority(): ?int
    {
        return $this->priority;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }


}