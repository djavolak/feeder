<?php
namespace EcomHelper\Product\Model;

use Skeletor\Core\Model\Model;

class Image extends Model
{
    private $productImageId;

    private $file;

    private $productId;

    private $isMain;

    private $sort;
    private int $imageId;

    public function __construct(
        int $productImageId, int $imageId, string $file, int $productId, int $isMain, int $sort, $createdAt = null, $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
        $this->productImageId = $productImageId;
        $this->file = $file;
        $this->productId = $productId;
        $this->isMain = $isMain;
        $this->sort = $sort;
        $this->imageId = $imageId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->productImageId;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return int
     */
    public function getProductId(): int
    {
        return $this->productId;
    }

    /**
     * @return int
     */
    public function getIsMain(): int
    {
        return $this->isMain;
    }

    /**
     * @return int
     */
    public function getSort(): int
    {
        return $this->sort;
    }

    public function getImageId(): int
    {
        return $this->imageId;
    }
}