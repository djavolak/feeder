<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIsVisibleAndIsFilterToAttributeTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attribute` 
    ADD COLUMN `isVisible`  tinyint(1) unsigned DEFAULT 1,
    ADD COLUMN `isFilter` tinyint(1) unsigned DEFAULT 0;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attribute` 
            DROP COLUMN `isVisible`,
            DROP COLUMN `isFilter`";
        $this->query($sql);
    }
}
