<?php

namespace EcomHelper\Feeder\ParsingActions;

class ChangeCategoryBasedOnString extends BaseRule
{
    protected function applyRule(array $productData, array $data): array
    {
        $subject = mb_strtolower($productData[$data['subject']]);
        $keyword = mb_strtolower($data['search']);
        $category = $data['category'];
        $keyword = preg_quote($keyword, '/');
        if (preg_match('/(?<!\w)'.$keyword.'(?!\w)/', $subject)) {
            $productData['category'] = $category;
        }
        return $productData;
    }

    protected function undoRule(array $productData, array $data): array
    {
        $subject = mb_strtolower($productData[$data['subject']]);
        $keyword = mb_strtolower($data['search']);
        $keyword = preg_quote($keyword, '/');
        $category = $this->model->getCategories()[0]->getId();
        if (preg_match('/(?<!\w)'.$keyword.'(?!\w)/', $subject)) {
            $productData['category'] = $category;
        }
        return $productData;
    }
}