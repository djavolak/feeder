<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddImageIdToAttributeValues extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attributeValues` 
    ADD COLUMN `imageId` int(10) UNSIGNED NOT NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attributeValues` 
            DROP COLUMN `imageId`;";
        $this->query($sql);
    }
}
