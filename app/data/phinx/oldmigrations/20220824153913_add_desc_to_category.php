<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDescToCategory extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `category` 
    ADD COLUMN `secondDescription` text NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `category` 
            DROP COLUMN `secondDescription`;";
        $this->query($sql);
    }
}
