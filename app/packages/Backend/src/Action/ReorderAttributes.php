<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Product\Service\Product;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\PDOWrite;

class ReorderAttributes
{

    public function __construct(private PDOWrite $pdo,
        private Attribute $attribute, private ProductAttributeValues $attrMapper)
    {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $productIds = $this->getProductsWithAttributes();
        foreach ($productIds as $productId) {
            $orderedAttributes =
                [
                    1442,1040,1417,163,1486,1689,1530,373,1836,645,443,1076,833,1209,779,1192,140,114,1768,614,811
                ];
            $attributes = $this->attribute->getAttributesForProduct($productId);
            $this->attrMapper->deleteBy('productId', $productId);
            while (count($orderedAttributes) > 0) {
                $target = array_shift($orderedAttributes);
                foreach ($attributes as $key => $attribute) {
                    if ((int)$attribute['attributeId'] === $target) {
                        unset($attributes[$key]);
                        $data = [
                            'productId' => $productId,
                            'attributeId' => $attribute['attributeId'],
                            'attributeName' => $attribute['attributeName'],
                            'attributeValue' => $attribute['attributeValue'],
                            'attributeValueId' => $attribute['attributeValueId']
                        ];
                        $this->attrMapper->insert($data);
                    }
                }
            }
            foreach ($attributes as $attribute) {
                $data = [
                    'productId' =>$productId,
                    'attributeId' => $attribute['attributeId'],
                    'attributeName' => $attribute['attributeName'],
                    'attributeValue' => $attribute['attributeValue'],
                    'attributeValueId' => $attribute['attributeValueId']
                ];
                $this->attrMapper->insert($data);
            }
        }
        return $response->withStatus(200);
    }

    public function getProductsWithAttributes(): bool|array
    {
        $sql = 'SELECT DISTINCT productId FROM productAttributeValues';
//        $sql = "SELECT productId FROM productAttributeValues WHERE productId = 42836";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
}