<?php

namespace EcomHelper\Feeder\ParsingActions;

use EcomHelper\Feeder\Model\CategoryParsingRule;

class SearchReplace extends BaseRule
{
    protected function applyRule($productData, $data):array
    {
        $caseSensitive = $data['caseSensitive'] ?? false;
        $search = $data['search'];
        $search = preg_quote($search, '/');
        $replace = $data['replace'];
        $subject = $productData[$data['subject']];
        if (!$caseSensitive) {
            $search = mb_strtolower($search);
            $replace = mb_strtolower($replace);
            $productData[$data['subject']] = preg_replace('/(?<!\w)'.$search.'(?!\w)/', $replace ,$subject);
            return $productData;
        }
        $productData[$data['subject']] = preg_replace('/(?<!\w)'.$search.'(?!\w)/', $replace, $productData[$data['subject']]);
        return $productData;
    }

    protected function undoRule($productData, $data):array
    {
        $caseSensitive = $data['caseSensitive'] ?? false;
        $search = $data['replace'];
        $search = preg_quote($search, '/');
        $replace = $data['search'];
        $subject = $productData[$data['subject']];
        if (!$caseSensitive) {
            $search = mb_strtolower($search);
            $replace = mb_strtolower($replace);
            $productData[$data['subject']] = preg_replace('/(?<!\w)'.$search.'(?!\w)/', $replace ,$subject);
            return $productData;
        }
        $productData[$data['subject']] = preg_replace('/(?<!\w)'.$search.'(?!\w)/', $replace, $productData[$data['subject']]);
        return $productData;
    }
}