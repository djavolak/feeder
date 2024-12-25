<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddFictionalDiscountPrecentageToProduct extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN `fictionalDiscountPercentage` tinyint(3) UNSIGNED NOT NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `product` 
            DROP COLUMN `fictionalDiscountPercentage`;";
        $this->query($sql);
    }
}
