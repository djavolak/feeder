<?php

namespace EcomHelper\Gpt\Suppliers\Ewe;

use EcomHelper\Gpt\Service\GptApi;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;

class Ewe extends GptApi
{

    public function __construct(Config $config)
    {
        parent::__construct($config);

        //System message for Ai behavior
        $this->addMessage('system', file_get_contents(__DIR__ . '/Examples/Attributes/Example1/system.txt'));

        //Example 1 it is used for Ai to better predict answer
        $this->addMessage('user', file_get_contents(__DIR__ . '/Examples/Attributes/Example1/question.txt'));
        $this->addMessage('assistant', file_get_contents(__DIR__ . '/Examples/Attributes/Example1/answer.txt'));
    }

    /**
     * @throws GuzzleException
     */
    public function getAttributesFromDescription(string $question): array
    {
        $question = 'Extract product attributes from string and return them in json string if there are multiple values for 
        single attribute put them in array where key is attribute name. If there are no valid attributes return empty string.
        Do not return anything except json string. "{' . $question . '}"';
        return $this->formatAttributes(json_decode(parent::getAnswer($question), true));
    }

    private function formatAttributes(array $attributes): array
    {
        $attributesArray = [];
        foreach ($attributes as $attributeGroup => $attributeSet) {
            if (!is_array($attributeSet)) continue;
            foreach ($attributeSet as $attributeName => $attributeValue) {
                $attributeName = $attributeGroup . ' / ' . $attributeName;
                if (array_key_exists($attributeName, $attributesArray)) {
                    if (!in_array($attributeValue, $attributesArray[$attributeName]['values'])) {
                        if (is_array($attributeValue)) {
                            $attributesArray[$attributeName]['values'] = array_merge($attributesArray[$attributeName]['values'], $attributeValue);
                        } else {
                            $attributesArray[$attributeName]['values'][] = $attributeValue;
                        }
                    }
                } else {
                    if (is_array($attributeValue)) {
                        $attributesArray[$attributeName] = [
                            'name' => $attributeName,
                            'values' => $attributeValue
                        ];
                    } else if (is_string($attributeValue)) {
                        $attributesArray[$attributeName] = [
                            'name' => $attributeName,
                            'values' => [$attributeValue]
                        ];
                    }
                }
            }
        }
        return $attributesArray;
    }
}