<?php $this->layout('layout::standard') ?>
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?=$this->t('Attribute Mapping:')?></h1>
        </div>
    </div>
<?=$this->section('viewTable', $this->fetch('partialsGlobal::viewTable', $data))?>
<div id="attributeIframeModal">
    <div id="closeIframe">
        <svg height="24" width="24" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
    </div>
</div>
<style>
    #tableData td:nth-child(2) {
       display: none;
    }
    #crudTable th:nth-child(2) {
        display: none;
    }
    #createNew {
        display: none;
    }
</style>
