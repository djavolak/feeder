<?php
namespace EcomHelper\Feeder\Service\Importer;

use EcomHelper\Attribute\Mapper\AttributeValues;
use EcomHelper\Attribute\Mapper\ProductAttributeValues;
use EcomHelper\Attribute\Mapper\SecondDescriptionAttributeCategory;
use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Backend\Controller\AttributeScraper;
use EcomHelper\Category\Model\CategoryMapper;
use EcomHelper\Feeder\Mapper\SupplierAttributeLocalAttribute;
use EcomHelper\Feeder\Repository\SupplierAttributeMapping;
use EcomHelper\Feeder\Service\AttributeWebScraper;
use EcomHelper\Feeder\Service\CategoryParsingRule;
use EcomHelper\Feeder\Service\ImageFetcher;
use EcomHelper\Gpt\Suppliers\Ewe\Ewe;
use EcomHelper\Gpt\Suppliers\Factory;
use EcomHelper\Product\Repository\ProductRepository as ProductRepo;
use EcomHelper\Feeder\Model\Product;
use EcomHelper\Product\Service\Image;
use GuzzleHttp\Exception\GuzzleException;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface as Logger;

abstract class Importer
{
    protected array $processedItemIds = [];
    public function __construct(
        protected \Redis $redis, protected ProductRepo $productRepo, protected ImageFetcher $imageFetcher,
        protected Logger $logger, protected Image $image, protected \EcomHelper\Product\Mapper\Image $imageMapper,
        protected \EcomHelper\Product\Service\Product $productService, protected CategoryParsingRule $parsingRulesService,
        protected SupplierAttributeMapping $supplierAttributeMappingRepo, protected ProductAttributeValues $productAttributeValuesMapper,
        protected SecondDescriptionAttributeCategory $secondDescriptionAttributeCategoryMapper, private \Redis $cache,
        private SupplierAttributeLocalAttribute $supplierAttributeLocalAttributeMapper, private Attribute $attributeRepository,
        private \EcomHelper\Attribute\Repository\AttributeValues $attributeValuesRepository, protected Config $config,
    ) {
    }

    abstract protected function getCacheKey();

    abstract protected function getLogTemplate();

    abstract protected function processItem(Product $product): bool;

    /**
     * @throws GuzzleException
     * @throws \RedisException
     */
    public function process($supplierName, $offset = 0, $limit = 25000)
    {
        $cacheKey = $this->getCacheKey() . $supplierName;
//        unlink(DATA_PATH . '/feed/updatedItems-',$supplierName.'.txt');
        $keys = array_slice($this->redis->sMembers($cacheKey . 'index'), $offset, $limit, true);
        $total = count($keys);
        // @TODO simple queue: reserve items by removing them from queue, to prevent duplicate items
        foreach ($keys as $key) {
            $this->redis->sRem($cacheKey . 'index', $key);
        }
        $processedCount = $errorCount = 0;
        $errors = [];
        $string = '';
        foreach ($keys as $key) {
            try {
                $item = unserialize($this->redis->get($cacheKey . $key));
                if ($item) {
                    if ($this->processItem($item)) {
                        $string .= $item->getSku() . PHP_EOL;
                        $processedCount++;
                        //Items with id 0 are new items, for now we don't need them here this array is only used for syncing with wp
                        if ($item->getId() !== 0) {
                            $this->processedItemIds[] = $item->getId();
                        }
                    }
                } else {
                    var_dump('failed');
                    var_dump($cacheKey);
                    var_dump($key);
                }

            } catch (\Exception $e) {
                $errorCount++;
                echo $e->getMessage() . PHP_EOL;
            } catch (\Error $e) {
                $errorCount++;
                echo $e->getMessage() . PHP_EOL;
            }
        }
        if (count($this->getProcessedItemIds()) > 0) {
            $this->productService->syncProducts($this->getProcessedItemIds(), 'feed', $supplierName);
        }
        $this->logger->info(sprintf($this->getLogTemplate(), $processedCount, $total));
    }
    public function getProcessedItemIds(): array
    {
        return $this->processedItemIds;
    }

    /**
     * @throws \Exception
     */
    protected function getLocalAttributesAndCreateMappingIfNeeded(Product $product): array
    {
        $localAttributesFormatted = [];
        $attributes = $product->getAttributes();
        if (isset($attributes['scrape']) && $attributes['scrape'] === true) {
            try {
                $attributes = (new AttributeWebScraper($this->cache))($product->getSupplierId(), $product->getSupplierProductId());
            } catch (\Exception|GuzzleException $e) {
                $this->logger->error($e->getMessage() . 'Product sku: '.$product->getSku());
                $attributes = [];
            }
        }
        if (isset($attributes['gpt']) && $attributes['gpt'] === true) {
            try {
                $gpt = (new Factory())($this->config ,$product->getSupplierId());
                $attributes = $gpt->getAttributesFromDescription($product->getDescription());
            } catch (GuzzleException $e) {
                $this->logger->error($e->getMessage() . 'Product sku: '.$product->getSku());
                $attributes = [];
            }
        }
        foreach ($attributes as $attribute) {
            if (isset($attribute['values'])){
                foreach ($attribute['values'] as $value) {
                    if (strlen($value) > 256) {
                        $value = substr($value, 0, 256);
                    }
                    //Fixing problems with wrong encoding, not sure, it needed both of these but fuck it, it works
                    $value = str_replace('"', '"', $value);
                    $value = str_replace("''", '"', $value);
                    $mapping = $this->supplierAttributeMappingRepo->fetchAll(
                        ['attribute' => $attribute['name'],
                            'supplier' => $product->getSupplierId(),
                            'attributeValue' => $value,
                            'category' => $product->getCategories()
                        ]);
                    if (count($mapping) === 0) {
                        $new = $this->supplierAttributeMappingRepo->create([
                            'attribute' => $attribute['name'],
                            'supplier' => $product->getSupplierId(),
                            'attributeValue' => $value,
                            'category' => $product->getCategories()
                        ]);
                        $this->supplierAttributeMappingRepo->createRelationWithProduct($product->getSku(), $new->getId());
                    }
                    if (!isset($new)) {
                        $localAttributes = $this->supplierAttributeLocalAttributeMapper->fetchAll(['supplierAttributeId' => $mapping[0]->getId()]);
                        if ($localAttributes) {
                            foreach ($localAttributes as $localAttributeData) {
                                $attribute = $this->attributeRepository->getById($localAttributeData['localAttributeId']);
                                $value = $this->attributeValuesRepository->getById($localAttributeData['localAttributeValueId']);
                                $localAttributesFormatted[$attribute->getId()][] = ['name' => $attribute->getAttributeName()];
                                $localAttributesFormatted[$attribute->getId()][] = ['value' => $value->getAttributeValue() . '#' . $value->getId()];
                            }
                        }
                        //if there is no relation with product, create it
                        if (count($this->supplierAttributeMappingRepo->checkIfRelationExist($product->getSku(), $mapping[0]->getId())) === 0) {
                            $this->supplierAttributeMappingRepo->createRelationWithProduct($product->getSku(), $mapping[0]->getId());
                        }
                    }
                }
            }
        }
        return $localAttributesFormatted;
    }
    protected function generateNewShortDesc($product)
    {
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
        $shortDesc = '';
        if (isset($found)) {
           $shortDesc = $shortDescHtml;
        }
        $this->productService->updateField('shortDescription', $shortDesc, $product->getId());
    }
}