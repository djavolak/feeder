<?php
/**
 * @var \EcomHelper\Product\Model\Product[] $data['model']
 */
?>
<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit product:')?> <?=$data['model']->getTitle()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create product:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form action="/product/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
    <?= $this->formToken(); ?>
    <div class="flexContainer productFlexContainer">
        <div>
            <div class="form-group">
                <div class="inputContainer">
                    <label for="ignoreStatusChange"><?=$this->t('Ignore status change')?></label>
                    <?php if ($data['model']?->getIgnoreStatusChange() === 1): ?>
                        <input type="checkbox" name="ignoreStatusChange" id="ignoreStatusChange" checked>
                    <?php else: ?>
                        <input type="checkbox" name="ignoreStatusChange" id="ignoreStatusChange">
                    <?php endif; ?>
                </div>
                <div class="inputContainer">
                    <label for="title"><?=$this->t('Title')?></label>
                    <input name="title" id="title" class="formInput form-control" value="<?=htmlentities($data['model']?->getTitle()) ?? ''?>"/>
                </div>
            </div>
            <div class="form-group">
                <div class="inputContainer">
                    <label for="slug"><?=$this->t('Slug')?></label>
                    <input name="slug" id="slug" class="formInput form-control" value="<?=$data['model']?->getSlug() ?? ''?>"/>
                </div>
            </div>
            <div class="form-group">
                <div class="inputContainer">
                    <label for="description"><?=$this->t('Description')?></label>
                    <div id="descriptionContainer" class="postEditor"></div>
                </div>
                <?php if($data['model']):?>
                    <div class="descriptionBlockData" data-blocks="<?=htmlentities(json_encode($data['model']?->getDescription() ?? ''))?>"></div>
                <?php endif;?>
            </div>
            <div class="form-group">
                <div class="inputContainer">
                    <label for="shortDescription"><?=$this->t('Short Description')?></label>
                    <div id="shortDescriptionContainer" class="postEditor"></div>
                <?php if($data['model']):?>
                    <div class="shortDescriptionBlockData" data-blocks="<?=htmlentities(json_encode($data['model']?->getShortDescription() ?? ''))?>"></div>
                <?php endif;?>
                </div>
            </div>
        </div>
        <div class="innerFlex productInnerFlex">
            <div class="form-group categoryList">
                <label for="status"><?=$this->t('Status')?></label>
                <select class="form-control" name="status" id="status">
                    <?php foreach(\EcomHelper\Product\Model\Product::getHRStatuses() as $key => $value):?>
                        <option <?=$data['model']?->getStatus() === $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                    <?php endforeach;?>
                </select>
                <span><?=$this->t('Select Category')?></span>
                <input class="form-control" type="text" id="categorySearch"
                       placeholder="<?=$this->t('Search Categories')?>"
                       aria-label="<?=$this->t('Search Categories')?>">
                <div class="inputContainer scrollableContainer flexedList">
                    <?php foreach ($data['categories'] as $category):?>
                    <div>
                            <label class="firstLevel">
                                <input <?=$data['model']?->getCategory()->getId() === $category['topLevelCategory']->getId() ? 'checked' : ''?> name="category" class="categoryCheckbox" type="checkbox" value="<?=$category['topLevelCategory']->getId()?>">
                                <?=$category['topLevelCategory']->getTitle()?>
                            </label>
                            <?php if(isset($category['secondLevelCategories'])): ?>
                                <?php foreach($category['secondLevelCategories'] as $secondLevelCategory):?>
                                <div>
                                    <label class="secondLevel">
                                        <input <?=$data['model']?->getCategory()->getId() === $secondLevelCategory[0]->getId() ? 'checked' : ''?> name="category" class="categoryCheckbox" type="checkbox" value="<?=$secondLevelCategory[0]->getId()?>">
                                        <?='-' . $secondLevelCategory[0]->getTitle()?>
                                    </label>
                                    <?php if(isset($secondLevelCategory['thirdLevelCategories'])): ?>
                                        <?php foreach($secondLevelCategory['thirdLevelCategories'] as $thirdLevelCategory):?>
                                            <label class="thirdLevel">
                                                <input <?=$data['model']?->getCategory()->getId() === $thirdLevelCategory->getId() ? 'checked' : ''?> name="category" class="categoryCheckbox" type="checkbox" value="<?=$thirdLevelCategory->getId()?>">
                                                <?='--' . $thirdLevelCategory->getTitle()?>
                                            </label>
                                        <?php endforeach; ?>
                                    <?php endif;?>
                                </div>
                                <?php endforeach;?>
                            <?php endif;?>
                    </div>
                    <?php endforeach;?>
                </div>
            </div>
            <div class="form-group productTabbedRight">
                <div id="tabbedContentContainer" class="marginTopTabbed">
                    <div id="tabs">
                        <div data-target="1" class="tab active"><?=$this->t('Pricing')?></div>
                        <div data-target="2" class="tab"><?=$this->t('Inventory')?></div>
                        <div data-target="3" class="tab"><?=$this->t('Attributes')?></div>
                        <div data-target="4" class="tab"><?=$this->t('Tags')?></div>
                    </div>
                    <div id="1" class="tabContent">
                        <div class="form-group">
                            <label for="inputPrice"><?=$this->t('Input Price')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="inputPrice" name="inputPrice" value="<?=$data['model']?->getInputPrice() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="price"><?=$this->t('Price')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="price" name="price" value="<?=$data['model']?->getPrice() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="specialPrice"><?=$this->t('Sale Price')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="specialPrice" name="specialPrice" value="<?=$data['model']?->getSpecialPrice() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="fictionalSalePercentage"><?=$this->t('Fictional sale percentage')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="fictionalSalePercentage" name="fictionalDiscountPercentage" value="<?=$data['model']?->getFictionalDiscountPercentage() ?? ''?>">
                        </div>
                        <!-- @todo fix specialPriceFrom and To, should be date, not datetime in DB -->
                        <?php
                        $specialPriceFrom = null;
                        $specialPriceTo = null;
                        if($data['model']?->getSpecialPriceFrom()) {
                            $specialPriceFrom = date('Y-m-d',strtotime($data['model']->getSpecialPriceFrom()));
                        }
                        if($data['model']?->getSpecialPriceTo()) {
                            $specialPriceTo = date('Y-m-d',strtotime($data['model']->getSpecialPriceTo()));
                        }
                        ?>
                        <div class="form-group">
                            <label for="specialPriceFrom"><?=$this->t('Sale Price From')?></label>
                            <input class="form-control" type="date" id="specialPriceFrom" name="specialPriceFrom" value="<?=$specialPriceFrom?>">
                        </div>
                        <div class="form-group">
                            <label for="specialPriceTo"><?=$this->t('Sale Price To')?></label>
                            <input class="form-control" type="date" id="specialPriceTo" name="specialPriceTo" value="<?=$specialPriceTo?>">
                        </div>
                        <div class="form-group">
                            <label for="priceLoop">Loop Sale Price?</label>
                            <input type="checkbox" id="salePriceLoop" name="salePriceLoop" <?=$data['model']?->getSalePriceLoop() ? 'checked' : ''?>>
                        </div>
                    </div>
                    <div id="2" class="hidden tabContent">
                        <div class="form-group">
                            <label for="supplierId"><?=$this->t('Supplier')?></label>
                            <select class="form-control" name="supplierId" id="supplierId">
                                <?php foreach($data['suppliers'] as $supplier):?>
                                    <option <?=$data['model']?->getSupplier()->getId() === $supplier->getId() ? 'selected' : ''?>  value="<?=$supplier->getId()?>"><?=$supplier->getName()?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stockStatus"><?=$this->t('Stock Status')?></label>
                            <select class="form-control" name="stockStatus" id="stockStatus">
                                <?php foreach(\EcomHelper\Product\Model\Product::getHRStockStatuses() as $key => $value):?>
                                    <option <?=$data['model']?->getStockStatus() === $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                                <?php endforeach;?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="quantity"><?=$this->t('Quantity')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="quantity" name="quantity" value="<?=$data['model']?->getQuantity() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="ean"><?=$this->t('EAN')?></label>
                            <input class="form-control" type="text" id="ean" name="ean" value="<?=$data['model']?->getEan() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="barcode"><?=$this->t('Barcode')?></label>
                            <input onwheel="return false" class="form-control" type="number" id="barcode" name="barcode" value="<?=$data['model']?->getBarcode() ?? ''?>">
                        </div>
                        <div class="form-group">
                            <label for="sku"><?=$this->t('SKU')?></label>
                            <input class="form-control" type="text" id="sku" name="sku" value="<?=$data['model']?->getSku() ?? ''?>">
                            <input class="form-control" type="hidden" id="supplierProductId" name="supplierProductId" value="<?=$data['model']?->getSupplierProductId() ?? ''?>">
                        </div>
                    </div>
                    <div id="3" class="hidden tabContent attributeTab">
                        <label for="attributeGroupSelect">
                            <?=$this->t('Add attributes from group')?>
                        </label>
                        <select id="attributeGroupSelect" class="form-control">
                            <option value="0"><?=$this->t('Select Attribute Group')?></option>
                            <?php foreach ($data['attributeGroups'] as $attributeGroupData):?>
                                <option value="<?=$attributeGroupData['values']?>"><?=$attributeGroupData['name']?></option>
                            <?php endforeach;?>
                        </select>
                        <div id="attributeSearchContainer" class="searchContainer">
                            <select class="form-control" name="attributeSelect" id="attributeSelect">
                                <option value="-1"><?=$this->t('Select Attribute')?></option>
                                <?php foreach($data['attributes'] as $attribute):?>
                                    <option value="<?=$attribute['attributeId']?>" data-position="<?=$attribute['position']?>"><?=$attribute['attributeName']?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button class="btn btn-primary" id="addAttributeFromSelect"><?=$this->t('Add')?></button>
                        <button class="btn btn-primary" id="createNewAttribute"><?=$this->t('Create New Attribute')?></button>
                        <div id="attributes" class="scrollableContainerCustomHeight" data-allowed-attribute-ids="<?=$data['allowedAttributeIdsForSecondDescription'] ?? '-1'?>">
                            <?php if($data['productAttributeValues'] !== []):?>
                                <?php foreach($data['productAttributeValues'] as $key => $productAttributeValue):?>
                                   <div style="display:none;" class="existingAttribute" data-attribute-id="<?=$key?>" data-position="<?=$productAttributeValue[0]['position']?>">
                                   <?php foreach($productAttributeValue as $value):?>
                                        <div data-attribute-id="<?=$key?>"
                                             data-attribute-value-id="<?=$value['attributeValueId']?>"
                                             data-attribute-name="<?=htmlspecialchars($value['attributeName'])?>"
                                             data-attribute-value="<?=htmlspecialchars($value['attributeValue'])?>"
                                        ></div>
                                    <?php endforeach;?>
                                   </div>
                                <?php endforeach;?>
                            <?php endif;?>
                        </div>
                    </div>
                    <div id="4" class="hidden tabContent tagTab">
                        <label for="tagSelect"><?=$this->t('Select tag')?></label>
                        <select class="form-control" id="tagSelect">
                            <option style="background:lightgray; color:white;" selected disabled value="-1"><?=$this->t('Choose a tag')?></option>
                            <?php foreach($data['tags'] as $tag): ?>
                                <option value="<?=$tag->getId()?>"><?=$tag->getTitle()?></option>
                            <?php endforeach; ?>
                        </select>
                        <div id="tagsContainer">
                        <?php if ($data['model']) {
                        foreach($data['model']->getTags() as $tag): ?>
                            <div class="tagContainer" data-tag-id="<?=$tag->getId()?>">
                                <input name="tags[<?=$tag->getId()?>]" type="hidden" value="<?=$tag->getTitle()?>">
                                <span><?=$tag->getTitle()?></span>
                                <div class="deleteTag">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        <?php endforeach; } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <label for="fileInput"><?=$this->t('Featured image')?></label>
    <?=$this->getImageWithPreview($data['model']?->getImages()[0], $this->t('Choose Image'), $this->t('Remove Image'))?>
    <label for="fileInput"><?=$this->t('Gallery images')?></label>
    <div class="galleryImagesWrapper" id="galleryImageUpload">
        <input multiple type="file" id="multipleFileInput" name="galleryImages[]">
        <span class="uploadText"><?=$this->t('Click or drag to add files')?></span>
    </div>
    <div id="galleryPreview">
        <?php if (is_array($data['model']?->getImages()) && count($data['model']?->getImages()) !== 0) : ?>
            <?php $count = 0; ?>
            <?php /** @var \EcomHelper\Product\Model\Image $image */
            foreach ($data['model']->getImages() as $key => $image) :
                if ($key === 0 && $image->getIsMain()) continue;?>
                <div class="previewImage" data-image-id="<?=$image->getId()?>" data-position="<?=$count?>">
                    <input type="hidden" name="existingImages[<?=$image->getId()?>]" value="<?=$count?>">
                    <?php $count++ ?>
                    <img src="/images/<?=$image->getFile()?>" alt="product image">
                    <div class="containerIcon">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="pointer-events: none; width: 32px; height: 32px; color: red;">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </div>
                </div>
            <?php endforeach;?>
        <?php endif;?>
    </div>
    <div class="form-group">
        <input type="hidden" name="id" value="<?= $data['model']?->getId() ?>">
        <input disabled data-action="/product/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>
<template id="productAttributeComponent">
    <div class="productAttributeComponent">
        <header>
            <div class="name"></div>
            <div class="delete"><?=$this->t('Remove')?></div>
        </header>
        <div class="contentBody">
            <div class="contentBodyWrapper">
                <input type="text" class="attributeValueSearch form-control"
                       placeholder="<?= $this->t('Attribute values') ?>">
                <button class="createNewAttributeValueFromForm btn btn-primary"><?= $this->t('Add New') ?></button>
            </div>

            <div class="attributeValuesList scrollableContainerCustomHeight"></div>
        </div>
    </div>
</template>
