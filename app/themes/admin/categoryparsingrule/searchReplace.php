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
                    <option <?=isset($data['inputData']['subject']) ?
                        ($data['inputData']['subject'] === 'description' ? 'selected' : '') : ''?>
                            value="description"><?=$this->t('Description')?></option>
                </select>
            </label>
        </div>
        <div class="col-lg-4">
            <label>
                <?=$this->t('Replace with')?>
                <input value="<?=$data['inputData']['replace'] ?? ''?>" class="form-control" type="text" name="data[replace]">
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4">
            <label>
                <?=$this->t('Case sensitive')?>
                <input <?=isset($data['inputData']['caseSensitive']) ?
                    ($data['inputData']['caseSensitive'] === 'on' ? 'checked' : '') : ''?>
                        name="data[caseSensitive]" type="checkbox">
            </label>
        </div>
    </div>
</div>