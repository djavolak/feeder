<?php
namespace EcomHelper\Feeder\Model;

use Skeletor\Core\Model\Model;

class Product extends Model
{
    private $productId;

    private $title;

    private $images;

    private $price;

    private $inputPrice;

    private $sku;

    private $description;

    private $specialPrice;

    private $specialPriceFrom;

    private $specialPriceTo;

    private $status;

    private $stockStatus;

    private $quantity;

    private $categories;

    private $supplierCategory;

    private $weight;

    private $ean;

    private $barcode;

    private $attributes;

    private $supplierId;

    private $supplierProductId;

    /**
     * @param $productId
     * @param $title
     * @param $images
     * @param $price
     * @param $description
     * @param $specialPrice
     * @param $specialPriceFrom
     * @param $specialPriceTo
     * @param $status
     * @param $categories
     * @param $weight
     * @param $barcode
     * @param $attributes
     * @param $ean
     * @param $createdAt
     * @param $updatedAt
     */
    public function __construct(
        $productId, $title, $images, $price, $description, $specialPrice, $specialPriceFrom, $specialPriceTo, $status,
        $stockStatus, $quantity, $categories, $weight, $barcode, $attributes, $ean, $supplierId, $supplierProductId,
        $inputPrice, $sku, $supplierCategory, private ?int $mappingId
    ) {
        parent::__construct(null, null);
        $this->productId = $productId;
        $this->title = $title;
        $this->images = $images;
        $this->price = $price;
        $this->description = $description;
        $this->specialPrice = $specialPrice;
        $this->specialPriceFrom = $specialPriceFrom;
        $this->specialPriceTo = $specialPriceTo;
        $this->status = $status;
        $this->stockStatus = $stockStatus;
        $this->quantity = $quantity;
        $this->categories = $categories;
        $this->weight = $weight;
        $this->barcode = $barcode;
        $this->attributes = $attributes;
        $this->ean = $ean;
        $this->supplierId = $supplierId;
        $this->supplierProductId = $supplierProductId;
        $this->inputPrice = $inputPrice;
        $this->sku = $sku;
        $this->supplierCategory = $supplierCategory;
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * @return mixed
     */
    public function getSupplierCategory()
    {
        return $this->supplierCategory;
    }

    /**
     * @return mixed
     */
    public function getInputPrice()
    {
        return $this->inputPrice;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return (int) $this->productId;
    }

    public function setId($id)
    {
        $this->productId = (int) $id;
    }

    /**
     * @return mixed
     */
    public function getStockStatus()
    {
        return (int) $this->stockStatus;
    }

    /**
     * @return mixed
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return mixed
     */
    public function getSpecialPrice()
    {
        return $this->specialPrice;
    }

    /**
     * @return mixed
     */
    public function getSpecialPriceFrom()
    {
        return $this->specialPriceFrom;
    }

    /**
     * @return mixed
     */
    public function getSpecialPriceTo()
    {
        return $this->specialPriceTo;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return (int) $this->status;
    }

    /**
     * @return mixed
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @return mixed
     */
    public function getEan()
    {
        return $this->ean;
    }

    /**
     * @return mixed
     */
    public function getBarcode()
    {
        return $this->barcode;
    }

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @return mixed
     */
    public function getSupplierId()
    {
        return $this->supplierId;
    }

    /**
     * @return mixed
     */
    public function getSupplierProductId()
    {
        return $this->supplierProductId;
    }


    /**
     * @return int|null
     */
    public function getMappingId(): ?int
    {
        return $this->mappingId;
    }
}