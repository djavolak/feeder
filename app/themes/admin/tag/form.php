<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit tag:')?> <?=$data['model']->getTitle()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create tag:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form action="/product/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" id="userForm" autocomplete="off">
    <?php echo $this->formToken(); ?>
    <div class="form-group">
        <div class="inputContainer">
            <label for="title"><?=$this->t('Title')?></label>
            <input name="title" id="title" class="formInput form-control" value="<?=$data['model']?->getTitle() ?? ''?>"/>
        </div>
    </div>

    <div class="form-group">
        <label for="priceLabel"><?=$this->t('Price label')?></label>
        <input type="text" name="priceLabel" id="priceLabel" class="form-control" value="<?=$data['model']?->getPriceLabel() ?? ''?>">
    </div>

    <div class="form-group">
        <h2><?=$this->t('Sticker options')?></h2>

        <?php
        $stickerTextHiddenClass = '';
        $stickerImageHiddenClass = 'hidden';
        if($data['model']?->getStickerImage()) {
            $stickerTextHiddenClass = 'hidden';
            $stickerImageHiddenClass = '';
        }
        $textSelected = $stickerTextHiddenClass === '';
        $imageSelected = $stickerImageHiddenClass === '';
        ?>

        <select id="stickerOption" class="form-control">
            <option <?= $textSelected ? 'selected' : ''?> value="0"><?=$this->t('Text')?></option>
            <option <?= $imageSelected ? 'selected' : ''?> value="1"><?=$this->t('Image')?></option>
        </select>

        <div id="stickerLabelContainer" class="<?=$stickerTextHiddenClass?>">
            <label for="stickerLabel"><?=$this->t('Sticker text')?></label>
            <input type="text" name="stickerLabel" id="stickerLabel" class="form-control" value="<?=$data['model']?->getStickerLabel() ?? ''?>">
        </div>

        <label for=""><?=$this->t('Sticker image')?></label>
        <?=$this->getImageWithPreview($data['model']?->getStickerImage(), $this->t('Choose Image'), $this->t('Remove Image'), 'stickerImageId')?>

        <label for="stickerImagePosition"><?=$this->t('Sticker position')?></label>
        <select name="stickerImagePosition" id="stickerImagePosition" class="form-control">
            <?php foreach(\Skeletor\Tag\Model\Tag::getHRPositions() as $key => $value):?>
                <option <?=$data['model']?->getStickerImagePosition() === $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
            <?php endforeach;?>
        </select>
    </div>

    <div class="form-group">
        <input type="hidden" name="tagId" value="<?= $data['model']?->getId() ?>">
        <input data-action="/tag/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>