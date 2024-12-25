<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCategoryToAttributeMapping extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `supplierAttributeMapping`
    ADD COLUMN `category` int(10) unsigned NOT NULL AFTER `mapped`;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `supplierAttributeMapping` 
            DROP COLUMN `category`;";
        $this->query($sql);
    }
}
