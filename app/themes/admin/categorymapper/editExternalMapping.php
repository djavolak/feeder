<?php $this->layout('layout::standard');?>
<?php
$pageMapped = '';
$pageUnMapped = '';
if((int)$data['page'] <= 0) {
    $data['page'] = null;
}
if($data['page']) {
    $pageMapped = '?page=' . ($data['page'] > ceil($data['countData']['mapped'][0] / 25) ? 1 : $data['page']) . '&limit=25';
    $pageUnMapped = '?page=' . ($data['page'] > ceil($data['countData']['unmapped'][0] / 25) ? 1 : $data['page']) . '&limit=25';
}
?>
<div class="row">
    <div class="col-lg-2">
        <select class="form-control mandatorySelect tenantSelect" data-location="/category-mapper/externalMapping/">
            <option value="-1"><?=$this->t('---Select tenant---')?></option>
            <?php foreach ($data['tenants'] as $tenant): ?>
                <option value="<?=$tenant->getId();?>"
                    <?php if (isset($data['filters']['tenantId']) && ($data['filters']['tenantId'] == $tenant->getId())) {echo 'selected';} ?>><?=$tenant->getName()?></option>
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
<div id="tabbedContentContainer" class="marginTop" data-master-categories-action="/category-mapper/getMasterCategories/<?=$data['tenantId'] ? $data['tenantId'] . '/': ''?>">
    <div id="tabs">
        <?php if ($data['tenantId'] !== ''):?>
            <div data-count="<?=$data['countData']['mapped']?>" data-action="/category-mapper/mappedExternal/<?=$data['tenantId']?>/<?=$pageMapped?>" data-form-ready-event-name="mappedReady" class="tab active mappedTab" data-type="mapped">Mapped</div>
            <div data-count="<?=$data['countData']['unmapped']?>" data-action="/category-mapper/unmappedExternal/<?=$data['tenantId']?>/<?=$pageUnMapped?>" data-form-ready-event-name="unmappedReady" class="tab unmappedTab" data-type="unmapped">Unmapped</div>
        <?php endif;?>
    </div>
    <div id="tabContent"></div>
</div>
<div id="pagination">
</div>
<script type="module" src="<?=ADMIN_ASSET_URL . '/js/gfComponents/pages/MappingExternalTabbed.js'?>"></script>