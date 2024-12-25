<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('Edit Category:')?> <?=$data['model']->getTitle()?></h1>
        <?php else: ?>
            <h1 class="page-header"><?=$this->t('Create Category:')?></h1>
        <?php endif; ?>
    </div>
</div>
<form enctype="multipart/form-data" action="/product/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="categoryForm" autocomplete="off">
    <?= $this->formToken(); ?>
    <div class="form-group">
        <div class="row">
            <div class="col-lg-4">
                <label for="statusSelect"><?=$this->t('Status')?></label>
                <select id="statusSelect" class="form-control" name="status">
                    <option
                        <?=$data['model']?->getStatus() === 1 ? 'selected': ''?> value="1"><?=$this->t('Active')?>
                    </option>
                    <option
                        <?=$data['model']?->getStatus() === 0 ? 'selected': ''?> value="0"><?=$this->t('Inactive')?>
                    </option>
                </select>
            </div>
            <div class="col-lg-4">
                <label for="tenantSelect">
                    <?=$this->t('Select tenant')?>
                </label>
                <select data-action="/category/getParentableCategories/" id="tenantSelect" class="form-control" name="tenantId">
                    <option value="0"><?=$this->t('No tenant')?></option>
                <?php foreach ($data['tenants'] as $tenant): ?>
                    <option
                        <?=$data['model']?->getTenant()?->getId() === $tenant->getId() ? 'selected': ''?> value="<?=$tenant->getId()?>">
                        <?=$tenant->getName()?>
                    </option>
                <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="inputContainer categoryInputContainer">
            <label for="categorySelect">
                <?=$this->t('Parent')?>
            </label>
            <div id="categoryList" data-category-id="<?=$data['model']?->getId()?>">
                <select name="parent" class="form-control" id="categorySelect">
                    <option value="0">No parent</option>
                    <?php foreach ($data['categories'] as $category): ?>
                    <?php // skip current cat
                        if ($category->getId() === $data['model']?->getId()) { continue; }
                    ?>
                    <option value="<?=$category->getId()?>" <?php if ($category->getId() === $data['model']?->getParent()?->getId()) { echo 'selected'; } ?>><?=$category->getName()?></option>
                    <?php endforeach;?>
                </select>
                
            </div>
        </div>
    </div>
    <div class="flexContainer">
        <div class="form-group">
            <div class="inputContainer">
                <label for="title"><?=$this->t('Title')?></label>
                <input name="title" id="title" class="formInput form-control" value="<?=$data['model']?->getTitle() ?? ''?>"/>
            </div>
        </div>
        <div class="form-group">
            <div class="inputContainer">
                <label for="slug"><?=$this->t('Slug')?></label>
                <input name="slug" id="slug" class="formInput form-control" value="<?=$data['model']?->getSlug() ?? ''?>"/>
            </div>
        </div>
        <div class="form-group fullWidth">
            <div class="inputContainer">
                <label for="description"><?=$this->t('Description')?></label>
                <div id="contentContainer" class="postEditor"></div>
            </div>
        </div>
        <div class="form-group fullWidth">
            <div class="inputContainer">
                <label for="secondDescription"><?=$this->t('Second description')?></label>
                <textarea id="secondDescription"><?=$data['model']?->getSecondDescription() ?? ''?></textarea>
                <?php $secondDesc = '';
                if ($data['model']) {
                    $secondDesc = htmlentities($data['model']->getSecondDescription());
                } ?>
                <input type="hidden" name="secondDescription" id="secondDescriptionInput" value="<?=$secondDesc?>">
            </div>
        </div>

    </div>
    <label for=""><?=$this->t('Image')?></label>
    <?=$this->getImageWithPreview($data['model']?->getImage(), $this->t('Choose Image'), $this->t('Remove Image'), 'imageId')?>
    <div class="form-group">
        <input type="hidden" name="id" value="<?= $data['model']?->getId() ?>">
        <input data-action="/category/<?= ($data['model']) ? 'update/' . $data['model']?->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
               class="btn btn-success submitButtonSize"/>
    </div>
    <?php if($data['model']):?>
        <div class="blockData" data-blocks="<?=htmlentities(json_encode($data['model']?->getDescription() ?? ''))?>"></div>
    <?php endif;?>
</form>

