<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Product\Service\ImageProcessor;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\PDORead;

class RegenerateCropsForProducts
{
    public function __construct(private PDORead $driver, private ImageProcessor $imageProcessor)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $sql = "SELECT * FROM productImages JOIN image USING (imageId)";
        $stmt = $this->driver->prepare($sql);
        $stmt->execute();
        $images = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        foreach ($images as $key => $image) {
            $imagePath = str_replace('/product', '', IMAGES_PATH);
            try {
                $imageObj = new \Imagick($imagePath . $image['filename']);
                $this->imageProcessor->createCropSizesForImage($imageObj);
                echo $this->progressBar($key, count($images), 'ImageId: ' . $image['imageId']);
            } catch (\ImagickException $e) {
                var_dump($e->getMessage());
            }
        }
        return $response->withStatus(200);
    }

    private function progressBar($done, $total, $info = "", $width = 50): string
    {
        $perc = round(($done * 100) / $total);
        $bar = round(($width * $perc) / 100);
        return sprintf("%s%%[%s>%s] %s/%s %s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width - $bar), $done,
            $total, $info);
    }
}