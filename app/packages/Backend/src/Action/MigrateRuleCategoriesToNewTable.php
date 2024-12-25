<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Feeder\Mapper\CategoryParsingRule;
use EcomHelper\Feeder\Mapper\ParsingRuleCategory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class MigrateRuleCategoriesToNewTable
{
    public function __construct(private ParsingRuleCategory $parsingRuleCategoryMapper, private CategoryParsingRule $categoryParsingRuleMapper)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response) :ResponseInterface
    {
        //fetch all rules
        $rules = $this->categoryParsingRuleMapper->fetchAll();
        foreach ($rules as $rule) {
            $this->parsingRuleCategoryMapper->insert([
                'parsingRuleId' => $rule['categoryParsingRuleId'],
                'categoryId' => $rule['categoryId'],
            ]);
        }
        return $response->withStatus(200);
    }
}