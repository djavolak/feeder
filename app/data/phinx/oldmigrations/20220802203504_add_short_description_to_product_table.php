<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddShortDescriptionToProductTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN `shortDescription` text NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `product` 
            DROP COLUMN `shortDescription`;";
        $this->query($sql);
    }
}
