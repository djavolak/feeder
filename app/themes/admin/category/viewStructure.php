<?php $this->layout('layout::standard') ?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Categories:')?></h1>
    </div>
</div>

<div class="row biggerBottomMargin">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <div class="panel-body panel-bodyWhite">
                <form id="sortableCategoriesForm" method="POST" action="/category/saveStructure/">
                    <input type="hidden" name="tenantId" value="<?=$data['tenantId'] ?? null ?>" />
                        <div id="sortableCategories">
                        <ul class="topLevel">
                        <?php
                        /* @var \EcomHelper\Product\Model\Category $category */
                        /* @var \EcomHelper\Product\Model\Category $secondLevelCategory */
                        /* @var \EcomHelper\Product\Model\Category $thirdLevelCategory */
                        foreach($data['catData'] as $category):
                        ?>
                            <li class="accordionFirstLevel">
                                <h2><?=$category['topLevelCategory']->getTitle()?>
                                    <div class="productCount <?=$category['topLevelCategory']->getCount() > 0 ? 'full' : 'empty'?>">
                                        <?=$category['topLevelCategory']->getCount()?>
                                    </div>
                                    <input type="hidden" name="categories[<?=$category['topLevelCategory']->getId()?>][topLevelCategory][id]" value="<?=$category['topLevelCategory']->getId()?>">
                                </h2>
                                <?php if (isset($category['secondLevelCategories'])):?>
                                    <div>
                                        <ul class="secondLevel">
                                            <?php foreach($category['secondLevelCategories'] as $key => $secondLevelCategories):?>
                                                <li class="accordionSecondLevel">
                                                    <h4><?=$category['secondLevelCategories'][$key][0]->getTitle()?>
                                                        <div class="productCount <?= $category['secondLevelCategories'][$key][0]->getCount() > 0 ? 'full' : 'empty'?>">
                                                            <?=$category['secondLevelCategories'][$key][0]->getCount()?>
                                                        </div>
                                                        <input type="hidden" name="categories[<?=$category['topLevelCategory']->getId()?>][secondLevelCategories][<?=$category['secondLevelCategories'][$key][0]->getId()?>][id]" value="<?=$category['secondLevelCategories'][$key][0]->getId()?>">
                                                    </h4>
                                                    <?php if (isset($category['secondLevelCategories'][$key]['thirdLevelCategories'])):?>
                                                        <div>
                                                        <ul class="thirdLevel">
                                                            <?php foreach($category['secondLevelCategories'][$key]['thirdLevelCategories'] as $thirdLevelCategory):
                                                            ?>
                                                                <li class="thirdLevelItem">
                                                                    <h5><?=$thirdLevelCategory->getTitle()?>
                                                                        <div class="productCount <?= $thirdLevelCategory->getCount() > 0 ? 'full' : 'empty'?>">
                                                                            <?=$thirdLevelCategory->getCount()?>
                                                                        </div>
                                                                        <input type="hidden" name="categories[<?=$category['topLevelCategory']->getId()?>][secondLevelCategories][<?=$category['secondLevelCategories'][$key][0]->getId()?>][thirdLevelCategories][<?=$thirdLevelCategory->getId()?>][id]" value="<?=$thirdLevelCategory->getId()?>">
                                                                    </h5>
                                                                </li>
                                                            <?php endforeach;?>
                                                        </ul>
                                                    </div>
                                                    <?php endif;?>
                                                </li>
                                            <?php endforeach;?>
                                        </ul>
                                </div>
                                <?php endif;?>
                            </li>
                        <?php endforeach;?>
                        </ul>
                    </div>
                    <input type="submit" value="<?=$this->t('Save')?>" class="greenButton">
                </form>
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>