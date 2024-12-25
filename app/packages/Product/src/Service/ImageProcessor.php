<?php

namespace EcomHelper\Product\Service;

use EcomHelper\Image\Service\Processor;
use Laminas\Config\Config;

class ImageProcessor extends Processor
{
    protected array $targetDimensions = ['width' => 800, 'height' => 800];
    protected array $cropSizes =
        [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
        ];

    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * @throws \ImagickException
     */
    public function processImage($tmpFileName, $supplierId = null)
    {
        $tmpImage = new \Imagick($tmpFileName);
        $targetPath = $this->imagePath;

        $baseFileName = basename($tmpFileName);
        $targetPath .= $baseFileName . '.jpg';
        $widthOffset = 0;
        $heightOffset = 0;
        //Square Image
        if ($this->isImageSquare($tmpImage)) {
            //Square with bigger dimensions than our target dimensions
            if ($tmpImage->getImageWidth() > $this->targetDimensions['width']) {
                $jpg = new \Imagick();
                $jpg->newImage($tmpImage->getImageWidth(), $tmpImage->getImageHeight(), "white");
                $jpg->compositeimage($tmpImage, \Imagick::COMPOSITE_OVER, 0, 0);
                $jpg->resizeImage($this->targetDimensions['width'], $this->targetDimensions['height'],
                    \Imagick::FILTER_CATROM, 0.9);
                $jpg->setImageFormat('jpg');
            } else { // Square with smaller dimensions than our target dimensions
                $jpg = new \Imagick();
                $widthOffset = ($this->targetDimensions['width'] - $tmpImage->getImageWidth()) / 2;
                $heightOffset = ($this->targetDimensions['height'] - $tmpImage->getImageHeight()) / 2;
                $jpg->newImage($this->targetDimensions['width'], $this->targetDimensions['height'], "white");
                $jpg->compositeimage($tmpImage, \Imagick::COMPOSITE_OVER, $widthOffset, $heightOffset);
            }
        } else { // Rectangle Image
            $whiteSquareDimension = $this->getMaxDimension($tmpImage);
            $jpg = new \Imagick();
            $jpg->newImage($whiteSquareDimension, $whiteSquareDimension, "white");
            $widthOffset = ($whiteSquareDimension - $tmpImage->getImageWidth()) / 2;
            $heightOffset = ($whiteSquareDimension - $tmpImage->getImageHeight()) / 2;
            $jpg->compositeimage($tmpImage, \Imagick::COMPOSITE_OVER, $widthOffset, $heightOffset);
            $jpg->setImageFormat('jpg');
            $jpg->resizeImage($this->targetDimensions['width'], $this->targetDimensions['height'],
                \Imagick::FILTER_CATROM, 1);
        }
        $jpg = $this->handleSpecialCasesPerSupplier($jpg, $supplierId, $widthOffset, $heightOffset);
        $jpg->writeImage($targetPath);
        $this->createCropSizesForImage($jpg);
        unlink($tmpFileName);
        return substr($targetPath, strpos($targetPath, 'images') + 6);
    }

    /**
     * @throws \ImagickException
     */
    private function isImageSquare(\Imagick $img): bool
    {
        if ($img->getImageWidth() !== $img->getImageHeight()) {
            return false;
        }
        return true;
    }

    /**
     * @param \Imagick $img
     * @return int
     * @throws \ImagickException
     */
    private function getMaxDimension(\Imagick $img): int
    {
        if ($img->getImageHeight() > $img->getImageWidth()) {
            return $img->getImageHeight();
        }
        return $img->getImageWidth();
    }

    /**
     * @throws \ImagickException
     */
    public function createCropSizesForImage(\Imagick $image)
    {
        foreach ($this->cropSizes as $cropSize) {
            $croppedImage = clone $image;
            $baseFileName = pathinfo($croppedImage->getImageFilename(), PATHINFO_FILENAME);
            $fileDir = pathinfo($croppedImage->getImageFilename(), PATHINFO_DIRNAME) . '/';
            $extension = pathinfo($croppedImage->getImageFilename(), PATHINFO_EXTENSION);
            $fileNameWithoutExtension = $fileDir . str_replace('.'.$extension,'', $baseFileName);
            $croppedImage->resizeImage($cropSize[0], $cropSize[1], \Imagick::FILTER_CATROM, 1);
            $croppedImage->writeImage($fileNameWithoutExtension .'-' . $cropSize[0] . 'x' . $cropSize[1] . '.'. $extension);
            $croppedImage->destroy();
        }
    }

    /**
     * @throws \ImagickException
     */
    private function handleSpecialCasesPerSupplier(\Imagick $jpg, mixed $supplierId, $widthOffset = 0, $heightOffset = 0)
    {
        switch ($supplierId) {
            case 14:
                $overLayImage = new \Imagick(ADMIN_ASSET_PATH . '/images/telitPowerMask.png');
                $jpg->compositeimage($overLayImage, \Imagick::COMPOSITE_OVER, $widthOffset, $heightOffset);
                return $jpg;
            default:
                return $jpg;
        }
    }
}