<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Edit unmapped categories')?> (<?=$data['unmappedCountPerTenant']?> total)</h1>
    </div>
</div>

<div class="row biggerBottomMargin">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <!-- /.panel-heading -->
            <div class="panel-body panel-bodyWhite">
                <?php if(isset($data['searchCount'])):?>
                    <div id="searchCount" data-count="<?=$data['searchCount']?>"></div>
                <?php endif;?>
                <form action="/category-mapper/saveMappedExternal/<?=$data['tenantId'];?>/"
                      class="inlineForm" method="POST" enctype="multipart/form-data" id="externalMappingForm" autocomplete="off">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel-body">
                                <h4><?=$this->t('Mapped categories')?></h4>
                                <?php
                                if ($data['tenantId']):
                                    $ignored = [];
                                    /* @var \EcomHelper\Category\Model\TenantCategoryMapper $category */
                                    /* @var \EcomHelper\Category\Model\Category $localCategory */
                                    foreach ($data['models'] as $key => $category):?>
                                        <div class="row">
                                            <div class="col-lg-2">
                                                <div class="form-group">
                                                    <?php if ($key === 0): ?>
                                                        <label class="userLabel"><?=$this->t('Source (main) Category:')?></label>
                                                    <?php endif; ?>
                                                    <input value="<?=$category->getLocalCategory()->getTitle()?>" readonly class="form-control inputDesign" />
                                                    <input type="hidden" name="categoryId[]" value="<?=$category->getLocalCategory()->getId()?>" />
                                                    <input type="hidden" name="tenantCategoryMappingId[]" value="<?=$category->getId()?>"  />
                                                    <?php $filterUrl = sprintf('/product/view/?categoryId=%s',
                                                        $category->getLocalCategory()->getId()); ?>
                                                    <span><a target="_blank" href="<?=$filterUrl?>">view products(<?=$category->getLocalCategory()->getCount()?>)</a></span>
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <div class="form-group">
                                                    <?php if ($key === 0): ?>
                                                        <label class="userLabel"><?=$this->t('Local (tenant) category:')?></label>
                                                    <?php endif; ?>
                                                    <select class="form-control selectDesign" name="mappedTo[]">
                                                        <option value="0">--- Izaberi ---</option>
                                                        <?php foreach ($data['categories'] as $localCategory): ?>
                                                            <option value="<?=$localCategory['id']?>" <?php if
                                                            ((int) $localCategory['id'] === $category->getRemoteCategory()?->getId()) { echo 'selected'; }
                                                            ?>><?=$localCategory['value']?></option>
                                                        <?php endforeach;?>
                                                    </select>
                                                    <div class="searchCategoryContainer">
                                                        <div class="searchCategory">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach;
                                endif;?>
                            </div>
                        </div>
                    </div>
                </form>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>
