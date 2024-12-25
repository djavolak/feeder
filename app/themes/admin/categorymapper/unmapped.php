<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Edit unmapped categories')?> (<?=$data['unmappedCountsPerSupplier']?> total)</h1>
    </div>
</div>

<div class="panel panel-primary">
    <!-- /.panel-heading -->
    <div class="panel-body panel-bodyWhite">
        <form action="/category-mapper/saveUnmapped/<?=$data['supplierId'];?>/"
              class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel-body">
                        <?php if(isset($data['searchCount'])):?>
                            <div id="searchCount" data-count="<?=$data['searchCount']?>"></div>
                        <?php endif;?>
                        <?php
                        if ($data['supplierId']):
                            foreach ($data['models'] as $key => $category): ?>
                                <div class="row">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="userLabel"><?=$this->t('Source 1:')?></label>
                                            <input readonly name="source1[]" value="<?=htmlentities($category->getSource1())?>" class="form-control inputDesign" />
                                            <?php
                                            $catString = rawurlencode(sprintf('%s-%s', $category->getSource1(), $category->getSource2()));
                                            if ($category->getSource3()) {
                                                $catString = rawurlencode(sprintf('%s-%s', $catString, $category->getSource3()));
                                            }?>
                                            <span><a target="_blank" href="/product/view/?supplierId=<?=$category->getSupplier()->getId()?>&supplierCategory=<?=$catString?>">view products (<?=$data['supplierCategory'][$key]?>)</a></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="userLabel"><?=$this->t('Source 2:')?></label>
                                            <input readonly name="source2[]" value="<?=htmlentities($category->getSource2())?>" class="form-control inputDesign" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="userLabel"><?=$this->t('Source 3:')?></label>
                                            <input readonly name="source3[]" value="<?=htmlentities($category->getSource3())?>" class="form-control inputDesign" />
                                        </div>
                                    </div>
                                    <div class="col-lg-1">
                                        <div class="form-group">
                                            <label class="userLabel"><?=$this->t('Product count:')?></label>
                                            <input name="count[]" disabled value="<?=$category->getCount()?>" class="form-control inputDesign" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="userLabel"><?=$this->t('Local category:')?></label>
                                            <select class="form-control selectDesign" name="categoryId[]">
                                                <option value="0">--- Izaberi ---</option>
                                                <option value="-1">--- Ignoriši ---</option>
                                                <?php foreach ($data['categories'] as $localCategory): ?>
                                                    <option value="<?=$localCategory['id']?>"><?=$localCategory['value']?></option>
                                                <?php endforeach;?>
                                            </select>
                                            <input type="hidden" name="id[]" value="<?=$category->getId()?>" />
                                            <div class="searchCategoryContainer">
                                                <div class="searchCategory">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="cursor: pointer;">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-2 flexedCenter">
                                        <div class="form-group">
                                            <button class="btn btn-success marginControl" data-id="<?=$category->getId()?>" data-action="/category-mapper/marginForm/?id=<?=$category->getId()?>"><?=$this->t('Margins')?></button>
                                            <?php if ($category->getCount() === 0 && (int)$data['supplierCategory'][$key] === 0) :?>
                                                <button class="btn btn-danger delete" data-id="<?=$category->getId()?>" data-action="/category-mapper/delete/<?=$category->getId()?>/"><?=$this->t('Delete')?></button>
                                            <?php endif;?>
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
