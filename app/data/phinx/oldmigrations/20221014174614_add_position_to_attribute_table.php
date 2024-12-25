<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddPositionToAttributeTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attribute` 
    ADD COLUMN `position`  int(3) unsigned NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attribute` 
            DROP COLUMN `position`;";
        $this->query($sql);
    }
}
