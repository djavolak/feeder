<div class="form-group">
    <div class="row">
        <div class="col-lg-4">
            <label>
                <?=$this->t('Search for')?>
                <?php
                $oldSearch = $data['inputData']['search'] ?? '';
                if ($oldSearch !== '') {
                    $oldSearch = htmlspecialchars($oldSearch);
                }
                ?>
                <input value="<?=$oldSearch?>" class="form-control" type="text" name="data[search]">
            </label>
        </div>
        <div class="col-lg-4">
            <label>
                <?=$this->t('Search where')?>
                <select name="data[subject]" class="form-control">
                    <option value="-1">------</option>
                    <option <?=isset($data['inputData']['subject']) ? ($data['inputData']['subject'] === 'title' ? 'selected' : '') : ''?>
                        value="title"><?=$this->t('Title')?></option>
                    <option <?=isset($data['inputData']['subject']) ? ($data['inputData']['subject'] === 'description' ? 'selected' : '') : ''?>
                        value="description"><?=$this->t('Description')?></option>
                </select>
            </label>
        </div>
        <div class="col-lg-4">
            <label>
                <?=$this->t('Change category to')?>
                <div style="display:flex;align-items:center;gap:1rem;" class="selectWithSearchCBOS">
                    <select name="data[category]" class="form-control" id="searchCategoryCBOS">
                       <option value="-1">------</option>
                        <?php
                        foreach ($data['categories'] as $category): ?>
                            <option
                                <?=isset($data['inputData']['category']) ? ((int)$data['inputData']['category'] === $category->getId() ? 'selected' : '') : ''?>
                                    value="<?=$category->getId()?>"><?=$category->getTitle()?>
                            </option>
                        <?php
                        endforeach; ?>
                    </select>
                    <div style="cursor:pointer;" class="searchContentCBOS" data-action="/category/tableHandler/">
                        <svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                        </svg>
                    </div>
                </div>
            </label>
        </div>
    </div>
</div>
