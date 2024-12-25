<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUniqueSkuToProduct extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` ADD UNIQUE INDEX `sku_UNQ` (`sku` ASC)";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `product` DROP INDEX `sku_UNQ`";
        $this->query($sql);
    }
}

