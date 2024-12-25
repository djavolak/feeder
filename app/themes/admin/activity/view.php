<?php $this->layout('layout::standard') ?>
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?=$this->t('Activities:')?></h1>
        </div>
    </div>
<?=$this->section('viewTable', $this->fetch('partialsGlobal::viewTable', $data))?>