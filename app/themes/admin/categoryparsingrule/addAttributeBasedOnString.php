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
            <label for="contains">
                <input <?=isset($data['inputData']['contains']) ? ($data['inputData']['contains'] === '1' ? 'checked' : '') : ''?>
                    type="checkbox" name="data[contains]" id="contains" value="1">
                <?=$this->t('Contains')?>
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
                <?=$this->t('Select attribute')?>
                <div style="display:flex;align-items:center;gap:1rem;" class="searchAttributeAABOS">
                    <select name="data[attributeId]" class="form-control" id="searchAttributeAABOS">
                        <option value="-1">------</option>
                        <?php
                        foreach ($data['attributes'] as $attribute): ?>
                            <option data-fetch-value-action="/attribute-values/fetchForAjaxSearch/?attributeId=<?=$attribute->getId()?>"
                                <?=isset($data['inputData']['attributeId']) ? ((int)$data['inputData']['attributeId'] === $attribute->getId() ? 'selected' : '') : ''?>
                                value="<?=$attribute->getId()?>"><?=$attribute->getAttributeName()?>
                            </option>
                        <?php
                        endforeach; ?>
                    </select>
                    <div style="cursor:pointer;" class="searchAttributeContentAABOS" data-action="/attribute/tableHandler/">
                        <svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                        </svg>
                    </div>
                </div>
            </label>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8">

        </div>
        <div class="col-lg-4">
            <label>
                <?=$this->t('Select attribute value')?>
                <div style="display:flex;align-items:center;gap:1rem;" class="searchAttributeValueAABOS">
                    <select name="data[attributeValueId]" class="form-control" id="searchAttributeValueAABOS">
                        <option value="-1">------</option>
                        <?php
                        foreach ($data['attributeValues'] as $attributeValue): ?>
                            <option
                                <?=isset($data['inputData']['attributeValueId']) ? ((int)$data['inputData']['attributeValueId'] === $attributeValue->getId() ? 'selected' : '') : ''?>
                                    value="<?=$attributeValue->getId()?>"><?=$attributeValue->getAttributeValue()?>
                            </option>
                        <?php
                        endforeach; ?>
                    </select>
                    <div style="cursor:pointer;" class="searchAttributeValueContentAABOS" data-action="/attribute-values/tableHandler/">
                        <svg style="width: 20px;pointer-events: none;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                            <path d="M416 208c0 45.9-14.9 88.3-40 122.7L502.6 457.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0L330.7 376c-34.4 25.2-76.8 40-122.7 40C93.1 416 0 322.9 0 208S93.1 0 208 0S416 93.1 416 208zM208 352c79.5 0 144-64.5 144-144s-64.5-144-144-144S64 128.5 64 208s64.5 144 144 144z"/>
                        </svg>
                    </div>
                </div>
            </label>
        </div>
    </div>
</div>
