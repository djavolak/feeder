<?php

namespace EcomHelper\Backend\Action;

use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Mapper\SecondDescriptionAttributeCategory;
use EcomHelper\Attribute\Service\Attribute;
use EcomHelper\Product\Service\Product;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Skeletor\Mapper\PDOWrite;

class RegenerateShortDescription
{

    public function __construct(private PDOWrite $pdo, private Attribute $attribute,
        private Product $productService, private SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper)
    {
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response)
    {
        $ids = [];
        for ($i= 0; $i <= 117; $i++){
            $productIds = $this->getProducts(0, 500);
            foreach ($productIds as $productId) {
                $ids[] = $productId;
                $newShortDesc = $this->generateNewShortDesc($productId);
                $this->productService->updateField('shortDescription', $newShortDesc, $productId);
            }
        }
        $this->productService->syncProducts($ids);
        return $response->withStatus(200);
    }

    private function getProductsWithDodatnaGrafikaAttribute(): bool|array
    {
        $sql = 'SELECT DISTINCT productId FROM productAttributeValues WHERE attributeId = 443';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function getProducts($offset, $limit)
    {
        $sql = 'SELECT DISTINCT productId FROM product WHERE status = 2 LIMIT :offset, :limit';
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':offset', $offset, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    private function generateNewShortDesc($productId)
    {
        $product = $this->productService->getById($productId);
        $allowedAttributeIdsForSecondDescription = [];
        $allowedAttributeIdsForSecondDescriptionData = $this->secondDescriptionAttributeCategoryMapper->getAttributeIdsByCategoryId($product->getCategory()->getId());
        foreach($allowedAttributeIdsForSecondDescriptionData as $allowedAttributeId) {
            $allowedAttributeIdsForSecondDescription[] = $allowedAttributeId['attributeId'];
        }
        $shortDescHtml = '<table class="woocommerce-product-attributes shop_attributes"><tbody>';
        /** @var \EcomHelper\Attribute\Model\ProductAttributeValues $attribute */
        $groupedAttributes = [];
        $attributeNameMapping = [];
        foreach ($product->getAttributes() as $attribute) {
            $groupedAttributes[$attribute['attributeId']][] = $attribute['attributeValue'];
            $attributeNameMapping[$attribute['attributeId']] = $attribute['attributeName'];
        }
        while (count($allowedAttributeIdsForSecondDescription) > 0 ) {
            $target = array_shift($allowedAttributeIdsForSecondDescription);
            foreach($groupedAttributes as $attributeId => $attributeValues) {
                if ((int)$attributeId === $target) {
                    $found = true;
                    $shortDescHtml .= "<tr><th>{$attributeNameMapping[$attributeId]}</th>";
                    $shortDescHtml .= "<td>";
                    foreach ($attributeValues as $key => $attributeValue) {
                        $shortDescHtml .= $attributeValue;
                        if (array_key_last($attributeValues) !== $key) {
                            $shortDescHtml .= ', ';
                        }
                    }
                    $shortDescHtml .= '</td></tr>';
                }
            }
        }
        $shortDescHtml .= '</tbody></table>';
        if (isset($found)) {
            return $shortDescHtml;
        }
        return '';
    }
}