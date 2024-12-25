<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit post:')?> <?=$data['model']->getTitle()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create post:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form enctype="multipart/form-data" action="/post/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="imageForm" autocomplete="off">
    <?php echo $this->formToken(); ?>
    <div class="flexContainer">
        <div class="form-group">
            <div class="inputContainer">
                <label for="title"><?=$this->t('Title')?></label>
                <input name="title" id="title" class="formInput form-control" value="<?=$data['model']?->getTitle()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Descripton')?></label>
                <input name="description" id="slug" class="formInput form-control" value="<?=$data['model']?->getDescription()?>" />
            </div>
        </div>
    <div class="form-group">
        <input type="hidden" name="id" value="<?= $data['model']?->getId() ?>">
        <input data-action="/post/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>

