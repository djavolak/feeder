<?php
namespace EcomHelper\Image\Model;

use Skeletor\Model\Model;

class Image extends Model
{
    const TYPE_JPEG = 1;
    const TYPE_PNG = 2;
    const TYPE_WEBP = 3;

    /**
     * @param int $imageId
     * @param string $filename
     * @param string $alt
     * @param int $type
     * @param string $mimeType
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     */
    public function __construct(
        private int $imageId,
        private string $filename,
        private string $alt,
        private int $type,
        private string $mimeType,
        private ?\DateTime $createdAt,
        private ?\DateTime $updatedAt
    ) {
        parent::__construct($this->createdAt, $this->updatedAt);
    }

    public static function getHRType($status)
    {
        return static::getHRTypes()[$status];
    }

    public static function getHRTypes()
    {
        return [
            static::TYPE_JPEG => 'Jpeg',
            static::TYPE_PNG => 'Png',
            static::TYPE_WEBP => 'Webp',
        ];
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->imageId;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @return string
     */
    public function getAlt(): string
    {
        return $this->alt;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }

}