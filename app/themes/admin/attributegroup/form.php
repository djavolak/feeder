<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit Attribute Group:')?> <?=$data['model']->getName()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create Attribute Group:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form>
    <div class="form-group">
        <div class="inputContainer">
            <label for="name"><?=$this->t('Name')?></label>
            <input name="name" id="name" class="name form-control" value="<?=$data['model']?->getName() ?? ''?>"/>
        </div>
    </div>

    <div class="form-group">
        <label for="categoryId"><?=$this->t('Category')?></label>
        <select class="form-control selectDesign" name="categoryId" id="categoryId">
            <option value="0">--- Izaberi ---</option>
            <?php foreach($data['categories'] as $category):?>
                <option <?=$category['id'] === $data['model']?->getCategoryId() ? 'selected' : ''?> value="<?=$category['id']?>"><?=$category['value']?></option>
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
    <div class="form-group attributeGroupFormGroup">
        <label for="attributeSearch"><?=$this->t('Attributes')?></label>
        <select class="form-control selectDesign" id="attributeId">
            <option value="0">--- Izaberi ---</option>
        <?php foreach($data['attributes'] as $attribute):?>
            <option value="<?=$attribute->getId()?>"><?=$attribute->getName()?></option>
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

        <div id="attributeCollection">
        <?php if ($data['model']) {
        foreach($data['model']->getAttributes() as $attribute):?>
            <div class="coupleContainer existingAttribute">
                <div class="dummySingleContainer">
                    <span class="dummy"><?=$attribute->getName()?></span>
                    <div class="deleteDummy" data-id="<?=$attribute->getId()?>">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <input type="hidden" name="attributes[]" value="<?=$attribute->getId()?>">
                </div>
            </div>
        <?php endforeach ; }?>
        </div>
    </div>

    <input type="hidden" name="id" value="<?=$data['model']?->getId()?>">
    <input data-action="/attribute-group/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
           class="btn btn-success submitButtonSize"/>
</form>