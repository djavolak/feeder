<?php $this->layout('layout::standard') ?>
<?php if($pageTitle):?>
    <h1><?=$pageTitle?></h1>
<?php endif;?>

<label for="categoryId"><?=$this->t('Category')?></label>
<div style="width:300px;" class="form-group">
    <select class="form-control" id="categoryId">
        <option value="0">--- Izaberi ---</option>
        <?php foreach($data['categories'] as $category):?>
            <option value="<?=$category['id']?>"><?=$category['value']?></option>
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
<div style="width:300px;" class="form-group attributeGroupFormGroup">
    <label for="attributeSearch"><?=$this->t('Attributes')?></label>
    <select class="form-control selectDesign" id="attributeId">
        <option value="0">--- Izaberi ---</option>
        <?php foreach($data['attributes'] as $attribute):?>
            <option value="<?=$attribute['attributeId']?>"><?=$attribute['attributeName']?></option>
        <?php endforeach; ?>
    </select>
    <div class="searchAttributeContainer">
        <div class="searchAttributes">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>
    <button style="margin-top:1rem;" class="btn btn-primary" id="addAttribute"><?=$this->t('Add attribute')?></button>
    <div style="margin-top:1rem; display:flex; flex-direction:column; gap:0.5rem;" id="attributeListContainer">

    </div>
</div>

<script src="<?=ADMIN_ASSET_URL . '/js/gfComponents/pages/SecondDescriptionSettings.js'?>"></script>