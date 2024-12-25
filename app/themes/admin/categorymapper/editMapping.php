<?php $this->layout('layout::standard') ?>
<?php
    $pageMapped = '';
    $pageUnMapped = '';
    $pageIgnored = '';
    if((int)$data['page'] <= 0) {
        $data['page'] = null;
    }
    if($data['page']) {
        $pageMapped = '?page=' . ($data['page'] > ceil($data['countData']['mapped'][0] / 25) ? 1 : $data['page']) . '&limit=25';
        $pageUnMapped = '?page=' . ($data['page'] > ceil($data['countData']['unmapped'][0] / 25) ? 1 : $data['page']) . '&limit=25';
        $pageIgnored= '?page=' . ($data['page'] > ceil($data['countData']['ignored'][0] / 25) ? 1 : $data['page']) . '&limit=25';
    } else {
        if (isset($data['filters']['categoryMappingId']))
            $pageMapped = $data['filters']['categoryMappingId'] ? $data['filters']['categoryMappingId'].'/' : '';
    }
?>
<div class="row">
    <div class="col-lg-2">
        <select class="form-control mandatorySelect supplierSelect" data-location="/category-mapper/internalMapping/">
            <option value=""><?=$this->t('Supplier')?></option>
            <?php foreach ($data['suppliers'] as $supplier): ?>
                <option value="<?=$supplier->getId()?>"
                    <?php if (isset($data['filters']['supplier']) && ($data['filters']['supplier'] == $supplier->getId())) {echo 'selected';} ?>><?=$supplier->getName()?></option>
            <?php endforeach;?>
        </select>
    </div>
    <button class="btn btn-primary filter" id="applyFilter"><?=$this->t('Filter')?></button>
</div>
<div class="searchSource">
    <label for="searchSource"><?=$this->t('Search')?></label>
    <input id="searchSource" type="text" class="form-control">
    <div>
        <div>
            <input type="radio" name="searchType" id="sourceCheckbox" value="source" checked>
            <label for="sourceCheckbox">Source only</label>
        </div>
        <div>
            <input type="radio" name="searchType" id="localCheckbox" value="local">
            <label for="localCheckbox">Local only</label>
        </div>
    </div>
</div>
<div id="tabbedContentContainer" class="marginTop" data-master-categories-action="/category-mapper/getMasterCategories/">
    <div id="tabs">
        <div data-count="<?=$data['countData']['mapped']?>" data-action="/category-mapper/mapped/<?=$data['filters']['supplier']?>/<?=$pageMapped?>" data-form-ready-event-name="mappedReady" class="tab active mappedTab" data-type="mapped">Mapped</div>
        <div data-count="<?=$data['countData']['unmapped']?>" data-action="/category-mapper/unmapped/<?=$data['filters']['supplier']?>/<?=$pageUnMapped?>" data-form-ready-event-name="unmappedReady" class="tab unmappedTab" data-type="unmapped">Unmapped</div>
        <div data-count="<?=$data['countData']['ignored']?>" data-action="/category-mapper/ignored/<?=$data['filters']['supplier']?>/<?=$pageIgnored?>" data-form-ready-event-name="ignoredReady" class="tab ignoredTab" data-type="ignored">Ignored</div>
    </div>
    <div id="tabContent"></div>
</div>
    <div id="pagination">
    </div>
<script type="module" src="<?=ADMIN_ASSET_URL . '/js/gfComponents/pages/MappingTabbed.js'?>"></script>