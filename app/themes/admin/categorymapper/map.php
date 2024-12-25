<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Edit existing category mapping')?></h1>
    </div>
</div>
<div class="row biggerBottonMargin">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <!-- /.panel-heading -->
            <div class="panel-body panel-bodyWhite mapBody">
                <?php if(isset($data['searchCount'])):?>
                    <div id="searchCount" data-count="<?=$data['searchCount']?>"></div>
                <?php endif;?>
                <form action="/category-mapper/saveMapped/<?=$data['supplierId'];?>/"
                      class="inlineForm" method="POST" enctype="multipart/form-data" id="userForm" autocomplete="off">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="panel-body">
                                <h4><?=$this->t('Mapped categories')?></h4>
                        <?php
                        if ($data['supplierId']):
                            $ignored = [];
                            /* @var \EcomHelper\Category\Model\CategoryMapper $category */
                        foreach ($data['models'] as $key => $category):
                            ?>
                                <div class="row" data-names="<?=$category->getSource1()?>-<?=$category->getSource2()?>-<?=$category->getSource3()?>">
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <?php if ($key === 0): ?>
                                            <label class="userLabel"><?=$this->t('Source 1:')?></label>
                                            <?php endif; ?>
                                            <input readonly name="source1[]" value="<?=htmlentities($category->getSource1())?>" class="form-control inputDesign" />
                                            <span><a target="_blank" href="/product/view/?supplierId=<?=$category->getSupplier()->getId()?>&mappingId=<?=$category->getId()?>">view products (<?=$data['count']($category->getId())?>)</a></span>
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <?php if ($key === 0): ?>
                                            <label class="userLabel"><?=$this->t('Source 2:')?></label>
                                            <?php endif; ?>
                                            <input readonly name="source2[]" value="<?=htmlentities($category->getSource2())?>" class="form-control inputDesign" />
                                        </div>
                                    </div>
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <?php if ($key === 0): ?>
                                            <label class="userLabel"><?=$this->t('Source 3:')?></label>
                                            <?php endif; ?>
                                            <input readonly name="source3[]" value="<?=htmlentities($category->getSource3())?>" class="form-control inputDesign" />
                                        </div>
                                    </div>
                                    <div class="col-lg-4">
                                        <div class="form-group">
                                            <?php if ($key === 0): ?>
                                            <label class="userLabel"><?=$this->t('Local category:')?></label>
                                            <?php endif; ?>
                                            <select class="form-control selectDesign categorySelect" name="categoryId[]">
                                                <option value="0">--- Izaberi ---</option>
                                                <option value="-1" <?php if ($category->getIgnored()) { echo 'selected'; } ?>>--- Ignoriši ---</option>
                                        <?php foreach ($data['categories'] as $localCategory): ?>
                                                <option value="<?=$localCategory['id']?>" <?php if
                                                ($localCategory['id'] === $category->getCategory()?->getId()) { echo 'selected'; }
                                                ?>><?=$localCategory['value']?></option>
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
                                            <?php if (isset($data['catsWithExistingRules'][$category->getCategory()->getId()])): ?>
                                                <a class="btn btn-success" target="_blank" href="/category-parsing-rules/view/?ids=<?=implode(',',$data['catsWithExistingRules'][$category->getCategory()->getId()])?>"><?=$this->t('Parsing rules')?></a>
                                            <?php endif; ?>
                                        <?php if ($category->getCount() === 0 && $data['supplierCategory'] !== [] && (int)$data['supplierCategory'][$key] === 0) :?>
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
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>