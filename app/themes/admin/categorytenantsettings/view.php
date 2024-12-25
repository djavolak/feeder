<?php $this->layout('layout::standard') ?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Categories tenant settings:')?></h1>
    </div>
</div>

<div class="row biggerBottomMargin">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <!-- /.panel-heading -->
            <div class="panel-body panel-bodyWhite">
                <?php
                    $action = '/category-tenant-settings/create/';
                    if ($data['update']) {
                        $action = '/category-tenant-settings/update/'.$data['tenantId'].'/';
                    }
                ?>
                <form id="form" method="post" action="<?=$action?>">
                    <label>
                        <?=$this->t('Tenant:')?>
                        <select id="tenantSelect" name="tenantId">
                            <option value="1">Konovo.rs</option>
                        </select>
                    </label>
                </form>
                <table width="100%" class="table table-striped table-bordered table-hover" id="clientTable">
                    <thead>
                    <tr>
                        <th><?=$this->t('Category name')?></th>
                        <th><?=$this->t('Category slug')?></th>
                        <th><?=$this->t('Remote category name')?></th>
                        <th><?=$this->t('Remote category slug')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /* @var \EcomHelper\Product\Model\Category $category */
                    foreach ($data['models'] as $model): ?>
                    <?php if ($model instanceof \EcomHelper\Product\Model\Category): ?>
                        <tr>
                            <td><?=$model->getTitle()?></td>
                            <td><?=$model->getSlug()?></td>
                            <td><input form="form" type="text" name="categories[<?=$model->getId()?>][label]"></td>
                            <td><input form="form" type="text" name="categories[<?=$model->getId()?>][slug]"></td>
                        </tr>
                    <?php endif; ?>
                        <?php if ($model instanceof \EcomHelper\Product\Model\CategoryTenantSettings): ?>
                            <input form="form" type="hidden" name="categories[<?=$model->getCategory()->getId()?>][settingId]" value="<?=$model->getId()?>">
                            <tr>
                                <td><?=$model->getCategory()->getTitle()?></td>
                                <td><?=$model->getCategory()->getSlug()?></td>
                                <?php
                                    //We are saving default values to database but want to show user empty fields
                                    $label = $model->getCategory()->getTitle() === $model->getLabel() ? '' : $model->getLabel();
                                    $slug = $model->getCategory()->getSlug() === $model->getSlug() ? '' : $model->getSlug();
                                ?>
                                <td><input form="form" type="text" name="categories[<?=$model->getCategory()->getId()?>][label]" value="<?=$label?>"></td>
                                <td><input form="form" type="text" name="categories[<?=$model->getCategory()->getId()?>][slug]" value="<?=$slug?>"></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach;?>
                    </tbody>
                </table>
                <button class="greenButton" form="form" type="submit"><?=$this->t('Submit')?></button>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>