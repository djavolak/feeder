<?php

namespace EcomHelper\Feeder\Service;

use EcomHelper\Attribute\Repository\Attribute;
use EcomHelper\Attribute\Repository\AttributeValues;
use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Feeder\Mapper\SupplierAttributeLocalAttribute;
use EcomHelper\Product\Model\Product;
use EcomHelper\Product\Repository\ProductRepository;
use EcomHelper\Product\Repository\SupplierRepository;
use EcomHelper\Product\Service\ProductSync;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\ServerRequest as Request;
use mysql_xdevapi\ColumnResult;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Activity\Repository\ActivityRepository;
use Skeletor\Mapper\NotFoundException;
use Skeletor\TableView\Model\Column;
use Skeletor\TableView\Service\Table;
use Skeletor\Tenant\Repository\TenantRepositoryInterface;
use Skeletor\User\Service\User;

class SupplierAttributeMapping extends Table
{
    public function __construct(
        \EcomHelper\Feeder\Repository\SupplierAttributeMapping $repo, User $user, Logger $logger,
        TenantRepositoryInterface $tenant, ActivityRepository $activity, private SupplierRepository $supplierRepo,
        private Attribute $attributeRepository, private AttributeValues $attributeValuesRepository,
        \EcomHelper\Feeder\Filter\SupplierAttributeMapping $filter,
        private CategoryRepository $categoryRepository, private ProductRepository $productRepository,
        private \EcomHelper\Attribute\Service\Attribute $attributeService,
        private ProductSync $productSync, private SupplierAttributeLocalAttribute $supplierAttributeLocalAttributeMapper
    )
    {
        parent::__construct($repo, $user, $logger, $tenant, $filter, $activity);
    }

    function compileTableColumns(): array
    {
        $attributes = $this->attributeRepository->fetchAll();
        $suppliers = $this->supplierRepo->fetchAll();
        foreach ($suppliers as $supplier) {
            $supplierOptions[$supplier->getId()] = $supplier->getName();
        }
        $categories = $this->categoryRepository->fetchAll();
        foreach ($categories as $category) {
            $categoryOptions[$category->getId()] = $category->getName();
        }
        $columnDefinitions = [
            (new Column('id', 'ID')),
            (new Column('name', 'name'))
                ->addJsDataParam('editColumn', true),
            (new Column('supplier', 'Supplier'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', true)
                ->addViewParam('filterValues', $supplierOptions ?? []),
            (new Column('category', 'Category'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', true)
                ->addViewParam('filterValues', $categoryOptions ?? []), //inserted via js
            (new Column('attribute', 'Attribute'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', true)
                ->addViewParam('filterValues', ['-1' => '']),//inserted via js
            (new Column('attributeValue', 'Attribute value'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', true)
                ->addViewParam('filterValues', ['-1' => '']),//inserted via js
            (new Column('localAttributes', 'Local attributes'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', false),
            (new Column('localAttributeValues', 'Local attribute values'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', false),
            (new Column('mapped', 'Mapped'))
                ->addJsDataParam('orderable', true)
                ->addViewParam('filterable', true)
                ->addViewParam('filterValues', ['1' => 'Yes', '0' => 'No']),
            new Column('attributeActions', 'Attribute Actions')
        ];

        return $columnDefinitions;
    }

    public function fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter = null): array
    {
        foreach ($filter as $key => $value) {
            $filter[$key] = urldecode($value);
        }
        if (!$order) {
            $order = ['orderBy' => 'supplierAttributeId', 'order' => 'desc'];
        }
        $data = parent::fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter);
        $items = [];
        foreach ($data['entities'] as $item) {
            $arr['localAttributes'] = [];
            $arr['localAttributeValues'] = [];
            foreach ($item->getLocalAttributes() as $key => $localAttribute) {
                $arr['localAttributes'][] = $localAttribute['localAttribute']->getAttributeName();
                foreach($localAttribute['localAttributeValues'] as $localAttributeValue) {
                    $arr['localAttributeValues'][] = $localAttributeValue->getAttributeValue();
                }
            }
            if (count($arr['localAttributes']) > 0) {
                $arr['localAttributes'] = implode(', ', $arr['localAttributes']);
            }
            if (count($arr['localAttributeValues']) > 0) {
                $arr['localAttributeValues'] = implode(', ', $arr['localAttributeValues']);
            }
            $arr['id'] = $item->getId();
            $arr['attribute'] = $item->getAttribute();
            $arr['attributeValue'] = $item->getAttributeValue();
            $arr['supplier'] = $item->getSupplier() ? $item->getSupplier()->getName() : 'Global';
            $arr['category'] = $item->getCategory()?->getName();
            $arr['mapped'] = $item->getMapped() ? 'Yes' : 'No';
            $arr['attributeActions'] = $this->getAttributeActions($item);
            $items[] = $arr;
        }
        return [
            'entities' => $items,
            'count' => $data['count']
        ];
    }

    public function getAttributesBySupplierId(int $supplierId, $search = null, $category = null)
    {
        return $this->repo->fetchSupplierAttributes($supplierId, $search, $category);
    }

    public function getCategoriesForSupplier($supplierId, $search = null)
    {
        return $this->repo->fetchSupplierCategories($supplierId, $search);
    }

    public function getAttributeValuesByAttribute(int $supplierId, string $attribute, $search = null)
    {
        return $this->repo->fetchSupplierAttributeValues($supplierId, $attribute, $search);
    }

    /**
     * @throws \Exception
     */
    public function update(Request $request)
    {
        if ($this->filter) {
            $data = $this->filter->filter($request);
        } else {
            $data = $request->getParsedBody();
        }
        $supplierAttributeModelOld = $this->repo->getById((int)$request->getAttribute('id'));
        if (isset($data['localAttributes'])) {
            $localAttributes = $data['localAttributes'];
            $localAttributeValues = $data['localAttributeValues'];
            unset($data['localAttributes']);
            unset($data['localAttributeValues']);
            if (count($localAttributes)) {
                foreach ($localAttributeValues as $localAttributeValue) {
                    if ($localAttributeValue === '-1'){
                        $data['mapped'] = 0;
                        break;
                    }
                    $data['mapped'] = 1;
                }
                //delete all mappings for that supplier attribute
                $this->supplierAttributeLocalAttributeMapper->deleteBy('supplierAttributeId' , $data['supplierAttributeId']);

                //save mappings
                foreach ($localAttributes as $localAttribute) {
                    if ($localAttribute === '-1') {
                        continue;
                    }
                    $values = $localAttributeValues[$localAttribute];
                    foreach ($values as $value) {
                        if ($value === '-1') {
                            continue;
                        }
                        //do not insert value that already exists
                        $foundItem = $this->supplierAttributeLocalAttributeMapper->fetchAll([
                            'supplierAttributeId' => $data['supplierAttributeId'],
                            'localAttributeId' => $localAttribute,
                            'localAttributeValueId' => $value
                        ]);
                        if (!$foundItem) {
                            $this->supplierAttributeLocalAttributeMapper->insert([
                                'supplierAttributeId' => $data['supplierAttributeId'],
                                'localAttributeId' => $localAttribute,
                                'localAttributeValueId' => $value
                            ]);
                        }
                    }
                }
            } else {
                //delete all mappings for that supplier attribute
                $data['mapped'] = 0;
                $this->supplierAttributeLocalAttributeMapper->deleteBy('supplierAttributeId' , $data['supplierAttributeId']);
            }
            $supplierAttributeModel = $this->repo->update($data);
            $this->createActivity($supplierAttributeModel, $supplierAttributeModelOld);
            return $supplierAttributeModel;
        }
        $data['mapped'] = 0;
        $this->supplierAttributeLocalAttributeMapper->deleteBy('supplierAttributeId' , $data['supplierAttributeId']);
        $supplierAttributeModel = $this->repo->update($data);
        $this->createActivity($supplierAttributeModel, $supplierAttributeModelOld);
        return $supplierAttributeModel;
    }

    private function getAttributeActions(mixed $item): string
    {
        return <<<HTML
            <div class="attributeActionsContainer">
                <button data-action="/attribute-mapping/applyMapping/?id={$item->getId()}"
                 data-id="{$item->getId()}" class="applyMapping">Apply</button>
                <button data-action="/attribute-mapping/undoMapping/?id={$item->getId()}" 
                data-id="{$item->getId()}" class="undoMapping">Undo</button>
            </div>
            HTML;
    }

    /**
     * @return Product[]
     * @throws NotFoundException
     */
    public function getProductsByAttributeMappingId(int $id): array
    {
        $products = [];
        foreach ($this->repo->getRelatedProductIds($id) as $productId) {
            try {
                $products[] = $this->productRepository->getById($productId);
            } catch (NotFoundException) {
                continue;
            }
        }
        return $products;
    }

    /**
     * @throws NotFoundException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function applyMapping(int $id): void
    {
        $localAttributes = $this->supplierAttributeLocalAttributeMapper->fetchAll(['supplierAttributeId' => $id]);
        if ($localAttributes) {
            $products = $this->getProductsByAttributeMappingId($id);
            $IdsToSync = [];
            foreach ($products as $product) {
                try {
                    $IdsToSync[] = $product->getId();
                    $attributes = $this->parseProductAttributes($product->getAttributes());
                    foreach ($localAttributes as $localAttributeData) {
                        $attribute = $this->attributeRepository->getById($localAttributeData['localAttributeId']);
                        $value = $this->attributeValuesRepository->getById($localAttributeData['localAttributeValueId']);
                        $attributes[$attribute->getId()][] = ['name' => $attribute->getAttributeName()];
                        $attributes[$attribute->getId()][] = ['value' => $value->getAttributeValue() . '#' . $value->getId()];
                    }
                    $this->attributeService->saveAttributesForProduct($product->getId(), $attributes, true);
                } catch (\Exception $e) {
                    continue;
                }
            }
            $this->productSync->syncProducts($IdsToSync);
        }
    }

    /**
     * @throws NotFoundException
     * @throws GuzzleException
     * @throws \Exception
     */
    public function undoMapping(int $id): void
    {
        $relations = $this->supplierAttributeLocalAttributeMapper->fetchAll(['supplierAttributeId' => $id]);
        $products = $this->getProductsByAttributeMappingId($id);
        $IdsToSync = [];
        foreach ($products as $product) {
            $IdsToSync[] = $product->getId();
            $attributes = $product->getAttributes();
            foreach ($attributes as $key => $attribute) {
                foreach ($relations as $relation){
                    if ((int)$attribute['attributeId'] === (int)$relation['localAttributeId'] &&
                        (int)$attribute['attributeValueId'] === (int)$relation['localAttributeValueId']) {
                        unset($attributes[$key]);
                    }
                }
            }
            $attributes = $this->parseProductAttributes($attributes);
            $this->attributeService->saveAttributesForProduct($product->getId(), $attributes, true);
            $this->productSync->syncProducts($IdsToSync);
        }
    }

    public function parseProductAttributes(array $attributes): array
    {
        foreach ($attributes as $attribute){
            $formattedAttributes[$attribute['attributeId']][] =
                ['name' => $attribute['attributeName']];
            $formattedAttributes[$attribute['attributeId']][] =
                ['value' => $attribute['attributeValue'] . '#' . $attribute['attributeValueId']];
        }
        return $formattedAttributes ?? [];
    }
}