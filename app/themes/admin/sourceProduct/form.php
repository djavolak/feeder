<?php
/**
 * @var \EcomHelper\Product\Model\SourceProduct[] $data['model']
 */
?>
<div class="row">
    <div class="col-lg-12">
        <?php if ($data['model']):?>
            <h1 class="page-header"><?=$this->t('View source product:')?> <?=$data['model']->getSupplierProductId()?></h1>
        <?php else: ?>

        <?php endif; ?>
    </div>
</div>
<form action=""
      class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
    <?= $this->formToken(); ?>
    <div class="flexContainer productFlexContainer">
        <div>
            <div class="form-group">
                <div class="inputContainer">
                    <label for="title"><?=$this->t('Cat1')?></label>
                    <p><?=$data['model']?->getCat1() ?? ''?></p>
                </div>
                <div class="inputContainer">
                    <label for="title"><?=$this->t('Cat2')?></label>
                    <p><?=$data['model']?->getCat2() ?? ''?></p>
                </div>
                <div class="inputContainer">
                    <label for="title"><?=$this->t('Cat3')?></label>
                    <p><?=$data['model']?->getCat3() ?? ''?></p>
                </div>
            </div>
            <p>Source data</p>
            <ul>
                <?php foreach ($data['model']->getProductData() as $key => $value): ?>
                <li><?=$key?> - <?php if (is_array($value)) { print_r($value); } else { echo $value; } ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="form-group">

    </div>
</form>