<?php

namespace EcomHelper\MarginGroups\Filter;

use Laminas\Filter\ToInt;
use Psr\Http\Message\ServerRequestInterface as Request;
use Skeletor\Filter\FilterInterface;

class MarginGroups implements FilterInterface
{

    public function getErrors()
    {
        return [];
    }

    public function filter(Request $request): array
    {
        $rules = '';
        $limiter = '';
        $margin = '';
        $int = new ToInt();
        $postData = $request->getParsedBody();
        if(isset($postData['rules'], $postData['prices'], $postData['margins'])) {
            $rules = serialize($postData['rules']);
            $limiter = serialize($postData['prices']);
            $margin = serialize($postData['margins']);

        }

       $data = [
           'marginGroupId' => (isset($postData['marginGroupId'])) ? $int->filter($postData['marginGroupId']) : null,
           'name' => $postData['name'],
           'rules' => $rules,
           'limiter'=> $limiter,
           'margin' => $margin,
       ];
        return $data;
    }
}