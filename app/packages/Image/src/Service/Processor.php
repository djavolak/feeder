<?php
namespace EcomHelper\Image\Service;

use EcomHelper\Image\Repository\ImageRepository;
use GuzzleHttp\Psr7\ServerRequest as Request;
use GuzzleHttp\Psr7\UploadedFile;
use Laminas\Config\Config;
use League\Flysystem\Filesystem;
use Skeletor\Image\Service\Image as ImageService;
use Skeletor\TableView\Model\Column;
use Skeletor\TableView\Service\Table as TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Activity\Service\Activity;
use Skeletor\User\Service\User;

class Processor
{
    private $cropSizes = [
        'deskSingle' => 800,
        'mobileSingle' => 400,
        'deskList' => 200,
    ];

    protected $imagePath;

    public function __construct(Config $config)
    {
        if ($config->offsetGet('cropSizes')) {
            $this->cropSizes = $config->offsetGet('cropSizes')->toArray();
        }
        $this->imagePath = $this->createDirectoryStructure();
    }

    private function createDirectoryStructure()
    {
        $dt = new \DateTime();
        $dateFolder = IMAGES_PATH . sprintf('/%s/', $dt->format('Y'));
        if (!is_dir($dateFolder)) {
            mkdir($dateFolder);
        }
        $dateFolder .= sprintf('%s/', $dt->format('m'));
        if (!is_dir($dateFolder)) {
            mkdir($dateFolder);
        }
        $dateFolder .= sprintf('%s/', $dt->format('d'));
        if (!is_dir($dateFolder)) {
            mkdir($dateFolder);
        }

        return $dateFolder;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param $itemId
     *
     * @return string saved file name
     * @throws \Exception
     */
    public function processUploadedFile(UploadedFile $uploadedFile, $fileName = '')
    {
        return $this->processImage($this->createTmpImageFromUploadedFile($uploadedFile, $fileName));
    }

    private function createTmpImageFromUploadedFile(UploadedFile $uploadedFile, $fileName)
    {
        $msg = false;
        switch ($uploadedFile->getError()) {
            case UPLOAD_ERR_INI_SIZE:
                $msg = 'Image is bigger than allowed size.';
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $msg = 'Image is bigger than allowed size.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $msg = 'Unable to find tmp directory.';
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $msg = 'Could not write file, check permissions.';
                break;
        }
        if ($msg) {
            throw new \Exception($msg);
        }
        $targetPath = $this->imagePath . md5($uploadedFile->getClientFilename());
        if ($fileName) {
            $targetPath = $this->imagePath . $fileName;
        }
        $uploadedFile->moveTo($targetPath);

        return $targetPath;
    }

    public function processImage($tmpFileName)
    {
        $tmpImage = new \Imagick($tmpFileName);
        $jpg = new \Imagick();
        $jpg->newImage($tmpImage->getImageWidth(), $tmpImage->getImageHeight(), "white");
        $jpg->compositeimage($tmpImage, \Imagick::COMPOSITE_OVER, 0, 0);
        $jpg->setImageFormat('jpg');
        $targetPath = $this->imagePath;

        $baseFileName = basename($tmpFileName);
        $targetPath .= $baseFileName;
        $jpg->writeImage($targetPath . '-org.jpg');
        \imagewebp(\imagecreatefromjpeg($targetPath . '-org.jpg'), $targetPath . '-org.webp');
        if ($jpg->getImageWidth() > 2000) {
            $scaled = clone $jpg;
            $scaled->scaleImage((int) ceil($jpg->getImageWidth() / 2), (int) ceil($jpg->getImageHeight() / 2));
            $scaled->writeImage($targetPath . '-org-scaled.jpg');
            \imagewebp(\imagecreatefromjpeg($targetPath . '-org-scaled.jpg'), $targetPath . '-org-scaled.webp');
        }
        $jpg->writeImage($targetPath . '.jpg');
//        $this->createCrops($jpg, $targetPath);
        \imagewebp(\imagecreatefromjpeg($targetPath . '.jpg'), $targetPath . '.webp');
        unlink($tmpFileName);
        $savePath = substr($targetPath, strpos($targetPath, 'images') + 6) . '.jpg';

        return $savePath;
    }

    private function getBestFitCrop(\Imagick $image)
    {
        $width = $image->getImageWidth();
        $height = $image->getImageHeight();
        $maxSize = min($width, $height);
        if ($width >= $height) {
            $wOffset = (int) ceil(($width - $height) / 2);
            $hOffset = 0;
        } else {
            $hOffset = (int) ceil(($height - $width) / 2);
            $wOffset = 0;
        }

        if (!$image->cropImage($maxSize, $maxSize, $wOffset, $hOffset)) {
            var_dump('could not save image');
            die();
        }

        return $image;
    }

    private function createCrops(\Imagick $image, $targetPath)
    {
        foreach ($this->cropSizes as $cropSize) {
            $crop = clone $image;
            $newHeight = (int) ($cropSize / ($image->getImageWidth() / $image->getImageHeight()));
            $crop->resizeImage($cropSize, $newHeight, \Imagick::FILTER_CATROM, 1);
            $cropPath = sprintf('%s_%s.jpg', $targetPath, $cropSize);
            $crop->writeImage($cropPath);
            $crop->destroy();
            $cropWebpPath = sprintf('%s_%s.webp', $targetPath, $cropSize);
            \imagewebp(\imagecreatefromjpeg($cropPath), $cropWebpPath);
        }
    }
}