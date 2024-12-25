<?php

namespace EcomHelper\Product\Service;

use EcomHelper\Image\Repository\ImageRepository;
use GuzzleHttp\Psr7\ServerRequest as Request;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\User\Service\User;
use Skeletor\Activity\Repository\ActivityRepository;

class Image extends \EcomHelper\Image\Service\Image
{
    public function __construct(ImageRepository $repo, User $user, Logger $logger, ImageProcessor $processor, ActivityRepository $activity)
    {
        parent::__construct($repo, $user, $logger, $processor, $activity);
    }

    public function create(Request $request)
    {
        $data = $request->getParsedBody();
        $images = [];
        if (isset($request->getUploadedFiles()['featuredImage'])) {
            if ($request->getUploadedFiles()['featuredImage']->getSize() !== 0) {
                $images[] = $this->repo->create([
                    'type' => 1,
                    'mimeType' => 'jpg/jpeg',
                    'filename' => $this->processor->processUploadedFile($request->getUploadedFiles()['featuredImage']),
                    'alt' => $data['alt'] ?? ''
                ]);
            }
        }
        foreach($request->getUploadedFiles()['galleryImages'] as $image) {
            if($image->getSize() === 0) continue;
            $images [] = $this->repo->create([
                'type' => 1,
                'mimeType' => 'jpg/jpeg',
                'filename' => $this->processor->processUploadedFile($image),
                'alt' => $data['alt'] ?? '',
            ]);
        }
        return $images;
    }

    public function createFeatured(Request $request)
    {
        $data = $request->getParsedBody();
        if (isset($request->getUploadedFiles()['featuredImage'])) {
            if ($request->getUploadedFiles()['featuredImage']->getSize() !== 0) {
                return $this->repo->create([
                    'type' => 1,
                    'mimeType' => 'jpg/jpeg',
                    'filename' => $this->processor->processUploadedFile($request->getUploadedFiles()['featuredImage']),
                    'alt' => $data['alt'] ?? ''
                ]);
            }
        }
        return null;
    }

    public function createGallery(Request $request)
    {
        $images = [];
        foreach($request->getUploadedFiles()['galleryImages'] as $image) {
            if($image->getSize() === 0) continue;
            $images [] = $this->repo->create([
             'type' => 1,
             'mimeType' => 'jpg/jpeg',
             'filename' => $this->processor->processUploadedFile($image),
             'alt' => $data['alt'] ?? '',
         ]);
        }
        return $images;
    }
    public function createFromPath($path, $supplierId = null)
    {
        return $this->repo->create([
            'type' => 1,
            'mimeType' => 'jpg/jpeg',
            'filename' => $this->processor->processImage($path, $supplierId),
            'alt' => $data['alt'] ?? '',
        ]);
    }

}