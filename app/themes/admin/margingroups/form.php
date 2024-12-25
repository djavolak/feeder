<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit margin group:')?> <?=$data['model']->getName()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create margin group:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form action="/product/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" id="userForm" autocomplete="off">
    <?= $this->formToken() ?>

    <div class="form-group">
        <div class="inputContainer">
            <label for="name"><?=$this->t('Name')?></label>
            <input name="name" id="name" class="formInput form-control" value="<?=$data['model']?->getName() ?? ''?>"/>
        </div>
    </div>

    <div id="marginRulesContainer">
        <button class="btn btn-success" id="addRule"><?=$this->t('Add Rule')?></button>
        <?php foreach($data['data'] as $marginRule): ?>
            <div class="form-group">
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
    </div>

    <div class="form-group">
        <input type="hidden" name="marginGroupId" value="<?= $data['model']?->getId() ?>">
        <input data-action="/margin-groups/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>
<template id="marginRuleTemplate">
    <div class="form-group">
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
