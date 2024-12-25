<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSupplierCategoryToProduct extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `product` 
    ADD COLUMN supplierCategory text NULL;";
        $this->query($sql);
    }
    public function down()
    {
        $sql = "ALTER TABLE `product` 
   DROP COLUMN supplierCategory;";
        $this->query($sql);
    }
}
