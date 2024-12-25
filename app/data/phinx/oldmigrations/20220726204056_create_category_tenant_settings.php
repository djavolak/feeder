<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoryTenantSettings extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `categoryTenantSettings` (
        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `tenantId` int(10) unsigned NOT NULL,
        `categoryId` int(6) unsigned NOT NULL,
        `label` varchar(64) NOT NULL,
        `slug` varchar(64) NOT NULL,
        `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
        $this->query("ALTER TABLE `categoryTenantSettings` 
        ADD INDEX `tenantId_index` (`tenantId`), 
        ADD INDEX `categoryId_index` (`categoryId`),
        ADD UNIQUE INDEX `categoryTenantSettings_tenantId_slug_index`(tenantId, slug);");
    }

    public function down()
    {
        $this->query("DROP TABLE `categoryTenantSettings`");
    }
}
