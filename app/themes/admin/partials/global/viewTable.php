<!-- /.panel-heading -->
<div class="panel-body panel-bodyWhite">
    <button id="createNew" class="btn btn-primary" data-action="<?=$createAction?>" title="Create new"><?=$this->t('Create new')?></button>
    <?php
    echo $tableFilters;
    ?>
    <table class="table tableControls">
        <tr>
            <td>
                <div class="tablePagination"></div>
                <input type="checkbox" class="selectAllRows">
            </td>
            <td>
                <label for="perPageSelector"><?=$this->t('Show')?></label>
                <select aria-label="Per page" class="form-control" id="perPageSelector" name="perPage">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                    <option value="2000">2000</option>
                </select>
                <label for="perPageSelector"><?=$this->t('Entries')?></label>
            </td>
            <td>
                <select name="bulkAction" class="bulkAction form-control">
                    <option value="-1">Choose Action</option>
                    <?php if (isset($entityPath) && $entityPath === 'product') :?>
                    <option value="edit">Edit</option>
                    <option value="sync">Sync</option>
                    <?php endif;?>
                    <option value="delete">Delete</option>
                </select>
                <button class="btn btn-primary bulkApplyButton"><?=$this->t('Apply')?></button>
            </td>
            <td class="viewTableSearch">
                <input class="form-control" aria-label="search" id="search" type="text" name="search"
                       placeholder="<?=$this->t('Search')?>" />
                <div class="searchOptions">
                    <label>
                        <select class="form-control" id="searchOptions" name="searchOptions">
                            <option value="contains">Contains</option>
                            <option value="startsWith">Starts with</option>
                            <option value="endsWith">Ends with</option>
                        </select>
                    </label>
                </div>

            </td>
        </tr>
    </table>
    <table id="crudTable" data-crud-action="/<?=$entityPath?>/" data-js-page="<?=ucfirst($entityPath)?>" class="table table-striped table-bordered table-hover">
        <thead>
        <tr>
            <?=$columnHeaders?>
            <th><?=$this->t('Action')?></th>
        </tr>
        </thead>
        <tbody id="tableData"></tbody>
        <tfoot>
        <tr>
            <td colspan="100%">
                <input type="checkbox" class="selectAllRows">
                <div class="tablePagination"></div>
            </td>
        </tr>
        <tr>
            <td colspan="100%">
                <select name="bulkAction" class="bulkAction form-control">
                    <option value="-1">Choose Action</option>
                    <option value="edit">Edit</option>
                    <option value="sync">Sync</option>
                    <option value="delete">Delete</option>
                </select>
                <button class="btn btn-primary bulkApplyButton"><?=$this->t('Apply')?></button>
            </td>
        </tr>
        </tfoot>
    </table>
</div>