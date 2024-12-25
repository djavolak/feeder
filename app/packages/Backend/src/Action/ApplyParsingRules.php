<?php

namespace EcomHelper\Backend\Action;

use DI\DependencyException;
use DI\NotFoundException;
use EcomHelper\Backend\Controller\CategoryParsingRuleController;
use EcomHelper\Feeder\Service\CategoryParsingRule;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Service\ProductSync;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ApplyParsingRules
{

    public function __construct(private CategoryParsingRule $service,
        private ProductRepository $productRepo,
        private ProductSync $productSync,
        private \EcomHelper\Feeder\Repository\CategoryParsingRule $categoryParsingRuleRepo,
        private CategoryParsingRuleController $ruleController) {}


    /**
     * @throws GuzzleException
     * @throws DependencyException
     * @throws NotFoundException
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $rules = $this->categoryParsingRuleRepo->fetchAll();
        $max = count($rules);
        foreach ($rules as $key => $rule) {
            $this->ruleController->handleRulesForExistingProducts($rule->getId());
            echo $this->progressBar($key + 1, $max, 'RuleId: ' . $rule->getId());
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