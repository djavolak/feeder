<?php
namespace EcomHelper\Product\Model;

use Skeletor\Core\Model\Model;

class SupplierFieldsMapping extends Model
{

    public function __construct(
        private string $id, private string $sourceFieldName, private ?Supplier $supplier = null, private ?string $productFieldName = null,
        $createdAt = null, $updatedAt = null
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

    public function getSourceFieldName(): string
    {
        return $this->sourceFieldName;
    }

    public function getProductFieldName()
    {
        return $this->productFieldName;
    }

    public function getSupplier(): Supplier
    {
        return $this->supplier;
    }

}