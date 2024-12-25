<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSupplierAttributeMappingTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `supplierAttributeMapping` (
      `supplierAttributeMappingId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `supplier` int(10) unsigned NOT NULL,
      `attribute` TEXT NOT NULL,
      `attributeValue` TEXT NOT NULL,
      `localAttribute` int(10) unsigned,
      `localAttributeValue` int(10) unsigned,
      `category` int(10) unsigned,
      `mapped` int(1) unsigned default 0,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`supplierAttributeMappingId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `supplierAttributeMapping`");
    }
}
