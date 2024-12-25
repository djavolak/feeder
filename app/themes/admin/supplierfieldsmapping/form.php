<div class="row">
    <div class="col-lg-12">
    <?php if ($data['model']):?>
        <h1 class="page-header"><?=$this->t('Edit mapping:')?> <?=$data['model']->getSourceFieldName()?></h1>
    <?php else: ?>
        <h1 class="page-header"><?=$this->t('Create mapping:')?></h1>
    <?php endif; ?>
    </div>
</div>
<form action="/supplier-field-mapping/<?= ($data['model']) ? 'update/' . $data['model']->getId() : 'create' ?>/"
      class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Source field name:')?></label>
                                <input name="sourceFieldName" value="<?=$data['model']?->getSourceFieldName() ?>" class="form-control inputDesign" />
                            </div>

                        </div>
                        <div class="col-lg-4">
                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Product field name:')?></label>
                                <select name="productFieldName" class="form-control inputDesign">
                                    <option value="0">Ignore</option>
                                <?php
                                $fields = ['id', 'title', 'description', 'sku', 'inputPrice', 'quantity', 'attributes', 'barcode', 'ean', 'cat1', 'cat2', 'cat3', 'meta', 'images'];
                                foreach ($fields as $field) { ?>
                                    <option value="<?=$field?>" <?php if ($data['model']?->getProductFieldName() === $field) { echo 'selected'; } ?>><?=$field?></option>
                                <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group divUser">
                                <label class="userLabel"><?=$this->t('Supplier')?></label>
                                <p><?=$data['model']?->getSupplier()->getName()?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="row" style="float: left;">
                            <div class="form-group">
                                <input data-action="/supplier-field-mapping/<?= ($data['model']) ? 'update/' . $data['model']->getId() : 'create' ?>/" id="submitButton" type="submit" value="<?= $this->t('Save') ?>"
                                       class="btn btn-success submitButtonSize"/>
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