<?php
namespace EcomHelper\Gpt\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;

class GptApi
{
    private Client $client;
    private string $key;

    private $messages = [];
    private $gptConfig = [
        'model' => 'gpt-3.5-turbo-16k',
        'temperature' => 0,
        'max_tokens' => 10000,
        'frequency_penalty' => 0,
        'presence_penalty' => 0,
    ];

    public function __construct(private Config $config)
    {
        $this->client = new Client([
            'verify' => false
        ]);
        $this->key = $this->config->get('gptKey');
    }

    public function addMessage(string $role, string $content): self
    {
        $this->messages[] = [
            'role' => $role,
            'content' => $content
        ];
        return $this;
    }

    public function setGptConfig(array $config): self
    {
        $this->gptConfig = array_merge($this->gptConfig, $config);
        return $this;
    }

    /**
     * @throws GuzzleException
     */
    public function getAnswer(string $question): string
    {
        $this->addMessage('user', $question);
        $args = array_merge($this->gptConfig, [
            'messages' => $this->messages
        ]);
        $response = $this->client->post('https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->key,
            ],
            'json' => $args
        ]);
        $body = $response->getBody();
        $data = json_decode($body, true);
        if (isset($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }
        throw new \Exception('Invalid response from GPT');
    }
}