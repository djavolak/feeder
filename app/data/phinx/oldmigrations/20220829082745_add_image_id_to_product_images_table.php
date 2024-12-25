<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddImageIdToProductImagesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `productImages` 
    ADD COLUMN `imageId` int(10) NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `productImages` 
            DROP COLUMN `imageId`;";
        $this->query($sql);
    }
}
