<div class="row">
    <div class="col-lg-12">
        <?php
        if ($data['model']): ?>
            <h1 class="page-header"><?=$this->t('Edit Rule:')?> <?=$data['model']->getId()?></h1>
        <?php
        else: ?>
            <h1 class="page-header"><?=$this->t('Create Rule:')?></h1>
        <?php
        endif; ?>
    </div>
    <form enctype="multipart/form-data"
          action="/product/<?=($data['model']) ? 'update/' . $data['model']?->getId() : 'create'?>/" class="col-lg-12"
          method="POST" id="categoryForm" autocomplete="off">
        <?= $this->formToken(); ?>
        <div class="form-group">
            <div class="row">
                <div class="col-lg-4">
                    <div class="inputContainer">
                        <label for="supplierId">
                            <?=$this->t('Select supplier')?>
                        </label>
                        <select class="form-control" id="supplierId" name="supplierId">
                            <option value="0">Global</option>
                            <?php
                            foreach ($data['suppliers'] as $supplier): ?>
                                <option
                                    <?=$data['model']?->getSupplier()?->getId() === $supplier->getId() ? 'selected' : ''?>
                                        value="<?=$supplier->getId()?>"><?=$supplier->getName()?>
                                </option>
                            <?php
                            endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="inputContainer">
                        <label for="action">
                            <?=$this->t('Select action')?>
                        </label>
                        <select class="form-control" id="action" name="action">
                            <option value="0">------</option>
                            <?php
                            foreach ($data['supportedActions'] as $key => $supportedAction): ?>
                                <option data-action="/category-parsing-rules/getFormInputsForAction/?action=<?=$key?>&id=<?=$data['model']?->getId() ?? 0?>"
                                    <?=$data['model']?->getAction() === $key ? 'selected' : ''?>
                                        value="<?=$key?>"><?=$supportedAction?>
                                </option>
                            <?php
                            endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-lg-4">
                    <label>
                        <?=$this->t('Description')?>
                    </label>
                    <textarea rows="4" class="form-control" name="description"><?=$data['model']?->getDescription()?></textarea>
                </div>
            </div>
        </div>
        <h2><?=$this->t('Select Categories')?></h2>
        <div class="form-group">
            <div class="row">
                <div class="col-lg-4">
                    <input id="ruleCategorySearch" data-action="/category/tableHandler/" class="form-control" type="text" placeholder="Search..."/>
                </div>
                <div class="col-lg-8">
                    <div data-name="categories[]" data-searchId="ruleCategorySearch" class="searchInputs">
                        <?php foreach ($data['model'] ? $data['model']->getCategories(): [] as $category):?>
                            <div>
                                <span><?=$category->getName()?></span>
                                <span data-id="<?=$category->getId()?>" class="removeResult">
                                    <svg width="24px" height="24px" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                                         fill="none" viewBox="0 0 24 24"
                                         stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </span>
                                <input type="hidden" name="categories[]" value="<?=$category->getId()?>">
                            </div>
                        <?php endforeach;?>
                    </div>
                </div>
            </div>
        </div>
        <div id="formInputs"></div>
        <div class="form-group">
            <input type="hidden" name="categoryParsingRuleId" value="<?=$data['model']?->getId()?>">
            <input data-action="/category-parsing-rules/<?=($data['model']) ? 'update/' . $data['model']?->getId() : 'create'?>/"
                   id="submitButton" type="submit" value="<?=$this->t('Save')?>"
                   class="btn btn-success submitButtonSize"/>
        </div>
    </form>
</div>