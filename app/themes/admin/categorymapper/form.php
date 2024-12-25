<?php
/* @var array $data */
/* @var int $loggedInRole */
/* @var string $protectedPath */

$this->layout('layout::standard') ?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Edit mapping for supplier:')?> <?=$data['model']->getName()?></h1>
    </div>
</div>
<form action="/category-mapper/updateForSupplier/<?=$data['model']->getId();?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
    <?php //$this->formToken(); ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <h4><?=$this->t('Basic Info')?></h4>
                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Source 1:')?></label>
                                <input name="name" value="<?=$data['model']?->getName() ?>" class="form-control inputDesign" />
                            </div>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Code:')?></label>
                                <input name="code" value="<?=$data['model']?->getCode() ?>" class="form-control inputDesign" />
                            </div>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Status')?></label>
                                <select class="form-control selectDesign" name="status">
                                    <option value="1" <?php if ($data['model']?->getStatus()) { echo 'selected'; }?>
                                    ><?=$this->t('Active')?></option>
                                    <option value="0" <?php if (!$data['model']?->getStatus()) { echo 'selected'; }?>
                                    ><?=$this->t('Inactive')?></option>
                                </select>
                            </div>

                            <h4><?=$this->t('Feed settings')?></h4>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Feed Type')?></label>
                                <select class="form-control selectDesign" name="sourceType">
                                    <option value="1" <?php if ($data['model']?->getSourceType() == 1) { echo 'selected'; }?>
                                    ><?=$this->t('Http')?></option>
                                    <option value="2" <?php if ($data['model']?->getSourceType() == 2) { echo 'selected'; }?>
                                    ><?=$this->t('Local')?></option>
                                </select>
                            </div>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Feed source:')?></label>
                                <input name="feedSource" value="<?=$data['model']?->getFeedSource() ?>" class="form-control inputDesign" />
                            </div>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Feed username:')?></label>
                                <input name="feedUsername" value="<?=$data['model']?->getFeedUsername() ?>" class="form-control inputinputDesign" />
                            </div>

                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Feed password:')?></label>
                                <input name="feedPassword" value="<?=$data['model']?->getFeedPassword() ?>" class="form-control inputDesign" />
                            </div>
                        </div>
                        <!-- /.col-lg-4 (nested) -->
                        <div class="col-lg-4">
                            <h4><?=$this->t('Feed Rules')?></h4>
                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Sku prefix:')?></label>
                                <input name="skuPrefix" value="<?=$data['model']?->getSkuPrefix()?>" class="form-control inputDesign" />
                            </div>

                            <h4><?=$this->t('Mapping settings')?></h4>

                            <input type="hidden" name="supplierId" value="<?=$data['model']?->getId()?>" />
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="row" style="float: left;">
                            <div class="form-group">
                                <input type="submit" value="<?=$this->t('Submit')?>" class="btn btn-success submitButtonSize" />
                            </div>
                        </div>
                    </div>
                    <!-- /.row (nested) -->
                </div>
                <!-- /.panel-body -->
            </div>
            <!-- /.panel -->
        </div>
        <!-- /.row -->
    </div>
</form>