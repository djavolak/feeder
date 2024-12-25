<?php
$this->layout('layout::standard'); ?>

<table>
<?php foreach ($data['data']['dsc'] as $info) { ?>
    <tr>
        <td><?=$info['timestamp']?></td>
        <td><?=$info['message']?></td>
    </tr>
<?php } ?>
</table>

<table>
    <?php foreach ($data['data']['uspon'] as $info) { ?>
        <tr>
            <td><?=$info['timestamp']?></td>
            <td><?=$info['message']?></td>
        </tr>
    <?php } ?>
</table>