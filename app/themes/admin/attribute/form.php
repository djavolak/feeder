<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit Attribute:')?> <?=$data['model']->getName()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create Attribute:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form>
    <?= $this->formToken(); ?>
    <div class="form-group">
        <div class="inputContainer">
            <label for="attributeName"><?=$this->t('Attribute Name')?></label>
            <input name="attributeName" id="attributeName" class="formInput form-control" value="<?=htmlspecialchars($data['model']?->getName()) ?? ''?>"/>
        </div>
    </div>
    <div class="form-group">
        <label for="attributePosition"><?=$this->t('Priority')?></label>
        <input type="number" name="position" id="attributePosition" class="form-control" value="<?=$data['model']?->getPosition()?>">
    </div>
    <div class="form-group">
        <input type="checkbox" name="isVisible" id="isVisible" <?=$data['model']?->getIsVisible() || !isset($data['model']) ? 'checked' : ''?>>
        <label for="isVisible"><?=$this->t('Visible on the product page')?></label>
    </div>
    <div class="form-group">
        <input type="checkbox" name="isFilter" id="isFilter" <?=$data['model']?->getIsFilter() ? 'checked' : ''?>>
        <label for="isFilter"><?=$this->t('Used as a filter')?></label>
    </div>
    <div class="form-group">
        <label for="attributeGroupId"><?=$this->t('Attribute Group')?></label>
        <select id="attributeGroupId" required class="form-control">
            <option value="0">--- <?= $this->t('Select group') ?> ---</option>
        <?php foreach ($data['groups'] as $group): ?>
            <option value="<?=$group->getId()?>"><?= $group->getName()?></option>
        <?php endforeach; ?>
        </select>
        <div class="searchGroupContainer">
            <div class="searchGroup">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
        <button style="margin-top:1rem;" class="btn btn-primary" id="addGroup"><?=$this->t('Add Group')?></button>
        <div id="groupCollection">
        <?php if ($data['model']?->getGroups()) {
            foreach($data['model']->getGroups() as $existingAttributeGroup):?>
            <div class="coupleContainer existingGroup">
                <div class="dummySingleContainer">
                    <span class="dummy"><?=htmlspecialchars($existingAttributeGroup->getName())?></span>
                    <div class="deleteDummy" data-id="<?=$existingAttributeGroup->getId()?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <input type="hidden" name="attributeGroup[]" value="<?=$existingAttributeGroup->getId()?>">
            </div>
        <?php endforeach; }?>
        </div>
    </div>

    <div id="groupInputContainer">
        <label for="attributeInput"><?=$this->t('Enter an attribute value then hit enter')?></label>
        <input type="text" id="attributeInput" class="form-control">
        <div id="dummyContainer" class="dummyContainer">
        <?php if ($data['model']) {
        foreach($data['model']->getValues() as $attributeValue): ?>
            <div class="coupleContainer">
                <div class="dummySingleContainer">
                    <span class="dummy"><?=$attributeValue->getValue()?></span>
                    <div class="deleteDummy" title="<?=$this->t('Delete')?>" data-id="<?=$attributeValue->getId()?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <input name="attributeValues[<?=$attributeValue->getId()?>]" type="hidden" value="<?=$attributeValue->getValue()?>">
                </div>
            </div>
        <?php endforeach; }?>
        </div>
    </div>

    <input type="hidden" name="id" value="<?=$data['model']?->getId()?>">
    <input data-action="/attribute/<?= ($data['model']) ? 'update/' . $data['model']->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
           class="btn btn-success submitButtonSize"/>
</form>
