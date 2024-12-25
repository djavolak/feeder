<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddMarginGroupIdToCategoryMappingTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `categoryMapping` 
    ADD COLUMN `marginGroupId`  int(12) unsigned NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `categoryMapping` 
            DROP COLUMN `marginGroupId`;";
        $this->query($sql);
    }
}
