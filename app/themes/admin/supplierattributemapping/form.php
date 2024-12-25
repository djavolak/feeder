<div class="row">
    <div class="col-lg-12">
        <?php
        if ($data['model']): ?>
            <h1 class="page-header"><?=$this->t('Edit Attribute mapping:')?> <?=htmlentities($data['model']->getAttribute())?></h1>
        <?php
        endif; ?>
    </div>
</div>
<form enctype="multipart/form-data" class="inlineForm" method="POST" enctype="multipart/form-data"
      id="attributeMappingForm" autocomplete="off">
    <?= $this->formToken(); ?>
    <div class="form-group">
        <div class="row">
            <div class="col-lg-4">
                <label for="supplier"><?=$this->t('Supplier')?></label>
                <p readonly class="form-control"><?=$data['model']->getSupplier()->getName()?></p>
                <input type="hidden" readonly value="<?=$data['model']->getSupplier()->getId()?>" name="supplier" id="supplier"
                       class="form-control">
            </div>
            <div class="col-lg-4">
                <label for="attributeValue"><?=$this->t('Attribute value')?></label>
                <input readonly value="<?=htmlentities($data['model']->getAttributeValue())?>" name="attributeValue"
                       id="attributeValue" class="form-control">
            </div>
            <div class="col-lg-4">
                <label for="mapped"><?=$this->t('Mapped')?></label>
                <p readonly class="form-control"><?=$data['model']->getMapped() ? 'Yes' : 'No'?></p>
                <input name="mapped" class="form-control" type="hidden" readonly value="<?=$data['model']->getMapped()?>">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <button class="btn btn-primary" id="addNewLocalAttribute" type="button"><?=$this->t('Add new local attribute')?></button>
        </div>
        <div class="col-lg-4">
            <button class="btn btn-primary" id="addNewAttribute" type="button"><?=$this->t('Edit Attributes')?></button>
        </div>
    </div>
    <div id="localAttributesContainer" class="form-group">
            <?php foreach($data['model']->getLocalAttributes() as $attributeId => $attributeData):?>
            <div class="row localAttributeWrapper">
                <div class="col-lg-4">
                    <label for="localAttribute"><?=$this->t('Local attribute')?></label>
                    <select data-name-param="attributeName" data-id-param="id" data-fetch-path="/attribute/tableHandler/" name="localAttributes[]" class="form-control ajaxSearchForm attributeSelect">
                        <option value="-1"><?=$this->t('Select')?></option>
                        <?php
                        foreach ($data['attributes'] as $attribute): ?>
                            <option value="<?=$attribute->getId()?>"
                                <?php
                                    if ($attribute->getId() == $attributeId):?>
                                        selected
                              <?php endif;?>
                            ><?=htmlentities($attribute->getAttributeName())?></option>
                        <?php
                        endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4">
                    <label for="localAttributeValue"><?=$this->t('Local attribute value')?></label>
                    <select data-name-param="attributeValue" data-id-param="id" data-fetch-path="/attribute-values/tableHandler/"
                            name="localAttributeValues[<?=$attributeId?>][]" class="form-control ajaxSearchForm attributeValuesSelect">
                        <option value="-1">---</option>
                        <?php
                        foreach ($data['attributeValues'][$attributeId] as $attributeValue): ?>
                            <option value="<?=$attributeValue->getId()?>"
                                <?php
                                $selected = false;
                                foreach ($attributeData['localAttributeValues'] as $selectedAttributeValue) {
                                    if ($selectedAttributeValue->getId() == $attributeValue->getId()) {
                                        $selected = true;
                                    }
                                }
                                if ($selected): ?>
                                    selected
                                <?php
                                endif; ?>
                            ><?=htmlentities($attributeValue->getAttributeValue())?></option>
                        <?php
                        endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-4" style="display: flex">
                    <button class="btn btn-danger deleteLocalAttribute"><?= $this->t('delete')?></button>
                </div>
            </div>
            <?php endforeach;?>
    </div>
    <div class="form-group">
        <input type="hidden" name="supplierAttributeId" value="<?=$data['model']?->getId()?>">
        <input data-action="/attribute-mapping/update/<?=$data['model']->getId()?>/"
               id="submitButton" type="submit" value="<?=$this->t('Save')?>" class="btn btn-success submitButtonSize"/>
    </div>
</form>

<template id="localAttributesInputs">
    <div class="row localAttributeWrapper">
        <div class="col-lg-4">
            <label for="localAttributes"><?=$this->t('Local attribute')?></label>
            <select data-name-param="attributeName" data-id-param="id" data-fetch-path="/attribute/tableHandler/" name="localAttributes[]" class="form-control ajaxSearchForm attributeSelect">
                <option value="-1"><?=$this->t('Select')?></option>
                <?php
                foreach ($data['attributes'] as $attribute): ?>
                    <option value="<?=$attribute->getId()?>"><?=htmlentities($attribute->getAttributeName())?></option>
                <?php
                endforeach; ?>
            </select>
        </div>
        <div class="col-lg-4">
            <label for="localAttributeValues"><?=$this->t('Local attribute value')?></label>
            <select data-name-param="attributeValue" data-id-param="id" data-fetch-path="/attribute-values/tableHandler/"
                    name="localAttributeValues[]" class="form-control ajaxSearchForm attributeValuesSelect">
                <option value="-1">---</option>
            </select>
        </div>
        <div class="col-lg-4" style="display: flex">
            <button class="btn btn-danger deleteLocalAttribute"><?= $this->t('delete')?></button>
        </div>
    </div>
</template>

