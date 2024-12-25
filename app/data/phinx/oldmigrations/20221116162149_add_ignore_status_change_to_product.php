<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIgnoreStatusChangeToProduct extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN `ignoreStatusChange` tinyint(1) UNSIGNED NOT NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `product` 
            DROP COLUMN `ignoreStatusChange`;";
        $this->query($sql);
    }
}
