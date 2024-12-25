<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Service\Image;
use EcomHelper\Product\Service\ImageProcessor;
use GuzzleHttp\Psr7\UploadedFile;

class FixProductImages
{

    public function __construct(private ProductRepository $productRepository, private Image $imageService,
        private ImageProcessor $productImageProcessor)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(): void
    {
        try {
            $products = $this->productRepository->fetchAll();
            foreach ($products as $product) {
                $images = $product->getImages() ?? [];
                /** @var \EcomHelper\Product\Model\Image $image */
                foreach($images as $image) {
                    $imagePath = sprintf('%s/%s', IMAGES_PATH, str_replace('/product/', '', $image->getFile()));
                    //Change processImage return name before starting this
                    $this->productImageProcessor->processImage($imagePath);
                }
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            var_dump($e->getTraceAsString());
            die();
        }
    }
}