<?php $this->layout('layout::standard') ?>
<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header"><?=$this->t('Queue:')?></h1>
    </div>
</div>

<div class="row biggerBottonMargin">
    <div class="col-lg-12">
        <div class="panel panel-primary">
            <!-- /.panel-heading -->
            <div class="panel-body panel-bodyWhite">
                <table width="100%" class="table table-striped table-bordered table-hover" id="clientTable">
                    <thead>
                    <tr>
                        <th><?=$this->t('Url')?></th>
                        <th><?=$this->t('Client')?></th>
                        <th><?=$this->t('Date created')?></th>
                        <th><?=$this->t('Date updated')?></th>
                        <th><?=$this->t('Action')?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    /* @var \EcomCrawler\Scraper\Model\Queue $queue */
                    foreach ($data['models'] as $queue): ?>
                    <tr>
                        <td><?=$queue->getUrl()?></td>
                        <td><?=$queue->getClient()?></td>
                        <td><?=$queue->getCreatedAt()->format('d/m/Y H:i')?></td>
                        <td><?=$queue->getUpdatedAt()->format('d/m/Y H:i')?></td>
                        <td>
                            <a href="/queue/delete/<?=$queue->getId()?>/" class="delete"><?=$this->t('Delete')?></a>
                        </td>
                    </tr>
                    <?php endforeach;?>
                    </tbody>
                </table>
                <!-- /.table-responsive -->
            </div>
            <!-- /.panel-body -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-12 -->
</div>