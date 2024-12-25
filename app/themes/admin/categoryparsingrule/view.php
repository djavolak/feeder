<?php $this->layout('layout::standard') ?>
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?=$this->t('Parsing rules:')?></h1>
        </div>
    </div>
<?=$this->section('viewTable', $this->fetch('partialsGlobal::viewTable', $data))?>
<style>
    #tableData td:nth-child(2) {
       display: none;
    }
    #crudTable th:nth-child(2) {
        display: none;
    }
</style>
