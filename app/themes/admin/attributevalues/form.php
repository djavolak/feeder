<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit Attribute value:')?> <?=$data['model']->getValue()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create Attribute:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form>
    <input type="hidden" name="attributeId" value="<?=$data['model']->getAttribute()->getId()?>">
    <input type="hidden" name="id" value="<?=$data['model']->getId()?>">
    <div class="form-group">
        <div class="inputContainer">
            <label for="attributeName"><?=$this->t('Attribute value')?></label>
            <input name="value" id="attributeValue" class="formInput form-control" value="<?=$data['model']?->getValue() ?? ''?>"/>
        </div>
    </div>
    <div class="form-group">
        <div class="inputContainer">
            <label for="link"><?=$this->t('Attribute link')?></label>
            <input name="link" id="link" class="formInput form-control" value="<?=$data['model']?->getLink() ?? ''?>"/>
        </div>
    </div>
    <label for=""><?=$this->t('Image')?></label>
    <?=$this->getImageWithPreview($data['model']?->getImage(), $this->t('Choose Image'), $this->t('Remove Image'), 'imageId')?>
    <input data-action="/attribute-values/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
           class="btn btn-success submitButtonSize"/>
</form>