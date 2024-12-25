<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCategoryIdToAttributeGroupTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attributeGroup` 
    ADD COLUMN `categoryId`  int(6) unsigned NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attributeGroup` 
            DROP COLUMN `categoryId`;";
        $this->query($sql);
    }
}
