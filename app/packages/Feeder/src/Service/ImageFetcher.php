<?php
namespace EcomHelper\Feeder\Service;

use GuzzleHttp\Client;

class ImageFetcher
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function fetch($url)
    {
        if (strlen($url) === 0) {
            return '';
        }
        $imageData = '';
        $parsedUrl = parse_url($url);
        try {
            if (!isset($parsedUrl['scheme'])) {
                throw new \Exception(sprintf('Invalid url provided for image fetch: %s.', $url));
            }

            switch ($parsedUrl['scheme']) {
                case 'https':
                case 'http':
                    $imageData = $this->handleHttp($url);
                    break;

                case 'default':
                    return '';
                    break;
            }
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            return '';
        }
        if($imageData === '') {
            return '';
        }

        $imageFileName = md5($parsedUrl['path']) . '.jpg';
        $imagePath = sprintf('%s/%s', IMAGES_PATH, $imageFileName);
        file_put_contents($imagePath, $imageData);

        return $imagePath;
    }

    private function handleHttp($url)
    {
        $config = [];
        if (false !== strpos($url, 'kimtec')) {
            $config = [
                'cert' => DATA_PATH . '/feed/keys/kimtek.crt.pem',
                'ssl_key' => DATA_PATH . '/feed/keys/kimtek.key.pem'
            ];
        }
        $data = '';
        try {
            $response = $this->client->get($url, $config);
            $data = $response->getBody()->getContents();
        } catch (\Exception $e) {
            // tmp fix for importing images
//            if (false !== strstr($e->getMessage(), '404 Not Found')) {
//                $url = str_replace('cdn.knv.rs', 'konovo.rs/media', $url);
//                $request = $this->client->get($url);
//            } else {
                var_dump($e->getMessage());
//                die('fetch image');
//            }
        }
//        $allowedTypes = ['image/jpeg', 'image/png']; // 'image/webp' does not work well
//        if (!in_array($response->getHeader('Content-Type')[0], $allowedTypes)) {
//            $response = $this->client->get($url);
//        }


        return $data;
    }
}