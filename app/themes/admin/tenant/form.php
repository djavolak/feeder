<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit tenant:')?> <?=$data['model']->getName()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create tenant:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form enctype="multipart/form-data" action="/tenant/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="imageForm" autocomplete="off">
    <?php echo $this->formToken(); ?>
    <div class="flexContainer">
        <div class="form-group">
            <div class="inputContainer">
                <label for="title"><?=$this->t('Name')?></label>
                <input name="name" id="title" class="formInput form-control" value="<?=$data['model']?->getName()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Production Url')?></label>
                <input name="productionUrl" id="slug" class="formInput form-control" value="<?=$data['model']?->getProductionUrl()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Development Url')?></label>
                <input name="developmentUrl" id="slug" class="formInput form-control" value="<?=$data['model']?->getDevelopmentUrl()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Production Auth token')?></label>
                <input name="prodAuthToken" id="slug" class="formInput form-control" value="<?=$data['model']?->getProdAuthToken()?>"/>
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Development auth token')?></label>
                <input name="devAuthToken" id="slug" class="formInput form-control" value="<?=$data['model']?->getDevAuthToken()?>" />
            </div>
        </div>
    <div class="form-group">
        <input type="hidden" name="tenantId" value="<?= $data['model']?->getId() ?>">
        <input data-action="/tenant/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
</form>

