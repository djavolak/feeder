<h2><?=$this->t('Margin Rules')?></h2>
<?php
/**
 * @var \EcomHelper\Category\Model\MarginRule $marginRule
 */
?>
<form method="POST" action="">
    <div id="marginRulesContainer">
    <div class="form-group fixedMarginInputs">
        <?php // Rules are always >= for every margin , for now? TBD ?>
        <input type="hidden" name="rules[]" value=">=">
        <span><?=$this->t('Fixed Margin ')?></span>
        <input class="form-control" type="hidden" name="prices[]" aria-label="<?=$this->t('from')?>" value="-1">
        <span><?=$this->t('Margin: ')?></span>
        <input id="fixedMargin" class="form-control" type="text" name="margins[]" aria-label="<?=$this->t('margin')?>" value="<?=$data['fixedMargin']?->getMargin() ? $data['fixedMargin']?->getMargin() : ''?>">
    </div>
        <div style="display:flex; gap:1rem;">
            <button class="btn btn-success" id="addGroup"><?=$this->t('Add Group')?></button>
            <select name="" id="marginGroup" class="form-control" style="width:300px;">
                <option value="-1"><?=$this->t('Izaberi')?></option>
                <?php foreach($data['marginGroups'] as $marginGroup) :?>
                    <option <?=($data['marginGroupId'] !== null && $data['marginGroupId'] === $marginGroup->getId()) ? 'selected' : ''?> value="<?=$marginGroup->getId()?>"><?=$marginGroup->getName()?></option>
                <?php endforeach;?>
            </select>
        </div>
    <button class="btn btn-success" id="addRule"><?=$this->t('Add Rule')?></button>

        <?php foreach($data['data'] as $marginRule): ?>
            <div class="form-group marginRuleFormGroup <?=$data['marginGroupId'] ? 'marginRuleFromGroup' : ''?>">
                <?php // Rules are always >= for every margin , for now? TBD ?>
                <input type="hidden" name="rules[]" value=">=">
                <span><?=$this->t('From: ')?></span>
                <input class="form-control" type="number" name="prices[]" aria-label="<?=$this->t('from')?>" value="<?=$marginRule->getPrice()?>">
                <span><?=$this->t('Margin: ')?></span>
                <input class="form-control" type="text" name="margins[]" aria-label="<?=$this->t('margin')?>" value="<?=$marginRule->getMargin()?>">
                <div title="<?=$this->t('Delete')?>" class="deleteMargin"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></div>
            </div>
        <?php endforeach;?>
        <?php if($data['marginGroupId']):?>
        <input type="hidden" id="marginGroupInputId" value="<?=$data['marginGroupId']?>" name="marginGroupInputId">
        <?php endif;?>
    </div>
    <input type="hidden" name="mappingId" value="<?=$data['mappingId']?>">
    <input data-action="/category-mapper/saveMarginRules/" id="submitMarginRules" type="submit" class="btn btn-success" value="<?=$this->t('Save')?>">
</form>

<template id="marginRuleTemplate">
    <div class="form-group marginRuleFormGroup">
        <input type="hidden" name="rules[]" value=">=">
        <span><?=$this->t('From: ')?></span>
        <input class="form-control" type="number" name="prices[]" aria-label="<?=$this->t('from')?>">
        <span><?=$this->t('Margin: ')?></span>
        <input class="form-control" type="text" name="margins[]" aria-label="<?=$this->t('margin')?>">
        <div title="<?=$this->t('Delete')?>" class="deleteMargin"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg></div>
    </div>
</template>