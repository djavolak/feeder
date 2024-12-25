<?php
namespace EcomHelper\Product\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Category\Entity\Category;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Product\Model\Product as DtoModel;
use Skeletor\Image\Entity\Image;
use Skeletor\Tag\Entity\Tag;

#[ORM\Entity]
#[ORM\Table(name: 'product')]
class Product
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $title;
    #[ORM\Column(type: Types::STRING)]
    private string $slug;
    #[ORM\Column(type: Types::STRING)]
    private string $description;
    #[ORM\Column(type: Types::STRING)]
    private string $shortDescription;
    #[ORM\Column(type: Types::STRING)]
    private string $ean;
    #[ORM\Column(type: Types::STRING)]
    private string $supplierProductId;
    #[ORM\Column(type: Types::STRING)]
    private string $sku;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $supplierCategory = null;  // @TODO what is this ?

    #[ORM\Column(type: Types::INTEGER)]
    private int $price;
    #[ORM\Column(type: Types::INTEGER)]
    private int $specialPrice;
    #[ORM\Column(type: Types::INTEGER)]
    private int $inputPrice;
    #[ORM\Column(type: Types::INTEGER)]
    private int $status;
    #[ORM\Column(type: Types::INTEGER)]
    private int $stockStatus;
    #[ORM\Column(type: Types::INTEGER)]
    private int $quantity;
    #[ORM\Column(type: Types::INTEGER)]
    private int $ignoreStatusChange;
    #[ORM\Column(type: Types::INTEGER)]
    private int $fictionalDiscountPercentage;
    #[ORM\Column(type: Types::STRING)]
    private ?string $mappingId;
    #[ORM\Column(type: Types::INTEGER)]
    private int $salePriceLoop;

    #[ORM\Column(type: Types::STRING)]
    private string $barcode;

    #[ORM\Column(type: Types::STRING)]
    private string $parsedProductId;

    #[ORM\ManyToOne(targetEntity: Image::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'imageId', referencedColumnName: 'id', unique: false)]
    private ?Image $image;

    #[ORM\ManyToOne(targetEntity: Category::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'categoryId', referencedColumnName: 'id', unique: false)]
    private Category $category;

    #[ORM\ManyToOne(targetEntity: Supplier::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'supplierId', referencedColumnName: 'id', unique: false)]
    private Supplier $supplier;

    #[ORM\ManyToMany(targetEntity: Tag::class, fetch: 'EAGER')]
    private Collection $tags;

    // attributes

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->title = $dto->getTitle();
        $this->slug = $dto->getSlug();
        $this->description = $dto->getDescription();
        $this->shortDescription = $dto->getShortDescription();
        $this->ean = $dto->getEan();
        $this->supplierProductId = $dto->getSupplierProductId();
        $this->sku = $dto->getSku();
        $this->supplierCategory = $dto->getSupplierCategory();
        $this->price = $dto->getPrice();
        $this->specialPrice = $dto->getSpecialPrice();
        $this->inputPrice = $dto->getInputPrice();
        $this->status = $dto->getStatus();
        $this->barcode = $dto->getBarcode();
        $this->stockStatus = $dto->getStockStatus();
        $this->quantity = $dto->getQuantity();
        $this->ignoreStatusChange = $dto->getIgnoreStatusChange();
        $this->fictionalDiscountPercentage = $dto->getFictionalDiscountPercentage();
        if ($dto->getMappingId()) { // @TODO remove if after implementing the mappingid
            $this->mappingId = $dto->getMappingId();
        }
        $this->salePriceLoop = $dto->getSalePriceLoop();
        $this->parsedProductId = $dto->getParsedProductId();
    }

    public function setTags(Collection $tags)
    {
        $this->tags = $tags;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getId()
    {
        return $this->id;
    }
}