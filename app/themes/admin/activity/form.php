<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('View activity:')?> <?=$data['model']->getId()?></h1>
        <?php else: ?>

        <?php endif; ?>
    </div>
</div>
<form enctype="multipart/form-data" action="/tenant/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="imageForm" autocomplete="off">
    <?php //echo $this->formToken(); ?>
    <div class="flexContainer">
        <div class="form-group">
            <div class="inputContainer">
                <label for="title"><?=$this->t('Entity')?></label>
                <input name="name" id="title" class="formInput form-control" value="<?=$data['model']?->getEntity()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="title"><?=$this->t('Entity ID')?></label>
                <input name="name" id="title" class="formInput form-control" value="<?=$data['model']?->getEntityId()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="userId"><?=$this->t('User:')?></label>
                <p><b><?=$data['model']?->getUser()->getDisplayName()?></b></p>
                <input type="hidden" name="userId" id="userId" value="<?=$data['model']?->getUser()->getId()?>" />
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="userId"><?=$this->t('Old Data:')?></label>
                <?php if (strlen($data['model']?->getOldData())) {
                print_r(unserialize($data['model']?->getOldData())->toArray());
                } ?>
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="userId"><?=$this->t('New Data:')?></label>
                <?php if (strlen($data['model']?->getNewData())) {
                    print_r(unserialize($data['model']?->getNewData())->toArray());
                } ?>
            </div>
        </div>
</form>