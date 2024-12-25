<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTenantCategoryMappingTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `tenantCategoryMapping` (
  `tenantCategoryMappingId` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `categoryId` int(6) NOT NULL,
  `tenantId` int(6) NOT NULL,
  `mappedToId` int(6) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tenantCategoryMappingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `tenantCategoryMapping`");
    }
}
