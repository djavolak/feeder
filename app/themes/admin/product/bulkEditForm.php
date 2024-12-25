<div class="row">
    <div class="col-lg-12">
        <h1><?=$this->t('Bulk Edit')?></h1>
    </div>
</div>
<form action="/product/bulkEditSubmit/" method="POST" autocomplete="off">
    <div id="bulkEditContainer">
        <?= $this->formToken(); ?>
        <div class="form-group">
            <?=$this->t('Editing Products:')?>
            <div id="editingProducts" class="scrollableContainerCustomHeight">
        <?php foreach($data['ids'] as $key => $id) :?>
                <div>
                    <span><?=$data['names'][$key]?></span>
                    <input type="hidden" value="<?=$id?>" name="ids[]">
                    <div class="deleteProductFromEdit">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
        <?php endforeach; ?>
            </div>
        </div>

        <div class="form-group">
            <label for="status"><?=$this->t('Status')?></label>
            <select class="form-control" name="status" id="status">
                <option value="-1"><?=$this->t('No change')?></option>
                <?php foreach(\EcomHelper\Product\Model\Product::getHRStatuses() as $key => $value):?>
                    <option value="<?=$key?>"><?=$value?></option>
                <?php endforeach;?>
            </select>
        </div>

        <div class="form-group">
            <label for="stockStatus"><?=$this->t('Stock Status')?></label>
            <select class="form-control" name="stockStatus" id="stockStatus">
                <option value="-1"><?=$this->t('No change')?></option>
                <?php foreach(\EcomHelper\Product\Model\Product::getHRStockStatuses() as $key => $value):?>
                    <option value="<?=$key?>"><?=$value?></option>
                <?php endforeach;?>
            </select>
        </div>
        <div class="form-group">
            <label for="specialPricePercentage"><?=$this->t('Sale Price Percentage')?></label>
            <input style="width: 30%" class="form-control" type="number" id="specialPricePercentage" name="specialPricePercentage" value="">
            <label for="fictionalSalePercentage"><?=$this->t('Fictional sale percentage')?></label>
            <input style="width: 30%" class="form-control" type="number" id="fictionalSalePercentage" name="fictionalDiscountPercentage" value="">
            <label for="specialPriceFrom"><?=$this->t('Sale Price From')?></label>
            <input style="width: 30%" class="form-control" type="date" id="specialPriceFrom" name="specialPriceFrom" value="">
            <label for="specialPriceTo"><?=$this->t('Sale Price To')?></label>
            <input style="width: 30%" class="form-control" type="date" id="specialPriceTo" name="specialPriceTo" value="">
            <label for="priceLoop">Loop sale price</label>
            <input type="radio" name="salePriceLoop" value="1">
            <label for="priceLoop">Stop looping sale price</label>
            <input type="radio" name="salePriceLoop" value="0">
            <label for="priceLoop">No change </label>
            <input checked type="radio" name="salePriceLoop" value="-1">
        </div>
        <div class="form-group">
            <span><?=$this->t('Select Category')?></span>
            <select name="category" class="form-control">
                <option value="-1"><?=$this->t('No change')?></option>
                <?php foreach ($data['categories'] as $category):?>
                    <option value="<?=$category['topLevelCategory']->getId()?>"><?=$category['topLevelCategory']->getTitle()?></option>
                <?php if(isset($category['secondLevelCategories'])): ?>
                    <?php foreach($category['secondLevelCategories'] as $secondLevelCategory):?>
                            <option value="<?=$secondLevelCategory[0]->getId()?>">&nbsp;&nbsp;-<?=$secondLevelCategory[0]->getTitle()?></option>
                            <?php if(isset($secondLevelCategory['thirdLevelCategories'])): ?>
                                <?php foreach($secondLevelCategory['thirdLevelCategories'] as $thirdLevelCategory):?>
                                    <option value="<?=$thirdLevelCategory->getId()?>">&nbsp;&nbsp;&nbsp;&nbsp;--<?=$thirdLevelCategory->getTitle()?></option>
                                <?php endforeach; ?>
                            <?php endif;?>
                    <?php endforeach ?>
                    <?php endif;?>
                <?php endforeach; ?>
            </select>
            <div class="searchCategoryContainer">
                <div class="searchCategory">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="attributeGroupId"><?=$this->t('Attribute Group')?></label>
            <select id="attributeGroupId" name="attributeGroupId" required class="form-control">
                <option value="0">--- <?= $this->t('Select group') ?> ---</option>
                <?php
                foreach ($data['groups'] as $group): ?>
                    <option value="<?=$group->getId()?>"><?= $group->getName()?></option>
                <?php
                endforeach; ?>
            </select>
            <div class="searchGroupContainer">
                <div class="searchGroup">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button style="margin-top:1rem;" class="btn btn-primary" id="addGroup"><?=$this->t('Add Group')?></button>
        </div>

        <div class="form-group">
            <div id="attributeSearchContainer" class="searchContainer">
                <label for="entitySearchInput" class="userLabel"><?= $this->t('Search Attributes') ?></label>
                <div id="searchInputContainer">
                    <input type="text" id="entitySearchInput" class="form-control">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <div id="entityOptions" class="hidden"></div>
            </div>
            <div id="attributes" class="bulkAttributesContainer"></div>
        </div>

        <div class="form-group scrollableContainerCustomHeight">
            <h3><?=$this->t('Delete Attributes')?></h3>
            <?php
            $attributesUsed = [] ?>
            <?php
            foreach ($data['attributes'] as $attribute) {
                foreach ($attribute as $attr) {
                    if (!isset($attributesUsed[$attr['attributeId']][$attr['attributeValueId']])) :?>
                    <?php $attributesUsed[$attr['attributeId']][$attr['attributeValueId']] = $attr; ?>
                        <div class="bulkAttributeDelete">
                            <input class="form-control" type="text" readonly value="<?=htmlspecialchars($attr['attributeName'])?>">
                            <input class="form-control" type="text" readonly value="<?=htmlspecialchars($attr['attributeValue'])?>">
                            <div class="deleteAttributeBulk" data-attribute-id="<?=$attr['attributeId']?>" data-attribute-value-id="<?=$attr['attributeValueId']?>">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    <?php endif;
                }
            }
            ?>
        </div>

        <div class="form-group">
            <h3><?=$this->t('Search tags')?></h3>
            <select id="tagSelectBulk" class="form-control">
                <option value="-1"><?=$this->t('Select tag')?></option>
                <?php foreach($data['tags'] as $tag) :?>
                    <option value="<?=$tag->getId()?>"><?=$tag->getTitle()?></option>
                <?php endforeach; ?>
            </select>
            <div class="searchTagsContainer">
                <div class="searchTags">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <button style="margin-top:1rem;" class="btn btn-primary" id="addTag"><?=$this->t('Add Tag')?></button>
            <div id="tagsToBeAddedContainer">

            </div>
        </div>

        <div class="form-group">
            <h3><?=$this->t('Delete Tags')?></h3>
            <div id="bulkTagsToDelete">
                <?php foreach($data['existingTags'] as $existingTag): ?>
                    <div class="deleteTagBulkEntity">
                        <div class="tagToDelete">
                            <span><?=$existingTag['tagTitle']?></span>
                        </div>
                        <div class="deleteTagBulk" data-id="<?=$existingTag['productTagId']?>" data-tag-id="<?=$existingTag['tagId']?>">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <input data-action="/product/bulkEditSubmit/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>

<template id="tagToBeAddedTemplate">
    <div class="tagToBeAddedEntity">
        <input type="hidden">
        <div class="tagToBeAddedInner">
            <span></span>
        </div>
        <div class="deleteTagToBeAdded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
        </div>
    </div>
</template>
