<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddCountToCategoryMapping extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `categoryMapping` 
    ADD COLUMN `count` int(8) NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `categoryMapping` 
            DROP COLUMN `count`;";
        $this->query($sql);
    }
}
