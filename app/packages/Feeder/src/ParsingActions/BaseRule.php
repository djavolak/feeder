<?php

namespace EcomHelper\Feeder\ParsingActions;

use EcomHelper\Feeder\Model\CategoryParsingRule;

abstract class BaseRule
{
    protected CategoryParsingRule $model;
    public function __invoke(array $productData, CategoryParsingRule $model, $undo = false): array
    {;
        $this->model = $model;
        $data = unserialize($model->getData());
        if ($undo){
            return $this->undoRule($productData, $data);
        }
        return $this->applyRule($productData, $data);
    }
    abstract protected function applyRule(array $productData, array $data): array;
    abstract protected function undoRule(array $productData, array $data): array;
}