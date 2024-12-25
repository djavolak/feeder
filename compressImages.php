<?php

$path = __DIR__ . '/2022';
$directory = new RecursiveDirectoryIterator($path);
$iterator = new RecursiveIteratorIterator($directory);
/* @var \SplFileInfo $file */
foreach ($iterator as $file) {
    if ($file->isFile() && ($file->getExtension() === 'jpg' || $file->getExtension() === 'jpeg'
            || $file->getExtension() === 'png')) {
//        if (str_contains($file->getPathInfo()->getPathname(), 'product/2023/')) {
//            continue;
//        }
        var_dump($file->getRealPath());
        $orgFile = $file->getRealPath();
        $tmpFile = $orgFile .'-tmp';
        try {
            $image = new \Imagick($orgFile);
            $image->setImageCompression(\Imagick::COMPRESSION_LOSSLESSJPEG);
            $image->setImageCompressionQuality(60);
            $image->writeImage($tmpFile);
            $image->destroy();
            if (filesize($tmpFile) < $file->getSize()) {
                rename($tmpFile, $orgFile);
            } else {
                unlink($tmpFile);
            }
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }
}
die('done');
