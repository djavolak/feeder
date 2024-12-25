<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSlugToProductTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN `slug` varchar(128) NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `product` 
DROP COLUMN `slug`;";
        $this->query($sql);
    }
}
