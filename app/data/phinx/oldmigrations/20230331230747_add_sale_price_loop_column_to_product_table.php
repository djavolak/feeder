<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSalePriceLoopColumnToProductTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN salePriceLoop int(1) unsigned NULL;";
        $this->query($sql);
    }
    public function down()
    {
        $sql = "ALTER TABLE `product` 
   DROP COLUMN salePriceLoop;";
        $this->query($sql);
    }
}
