<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductSupplierAttributesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `productSupplierAttributes` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `supplierAttributeMappingId` int(10) unsigned NOT NULL,
      `productId` int(10) unsigned,
      `sku` varchar(128),
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `productSupplierAttributes`");
    }
}
