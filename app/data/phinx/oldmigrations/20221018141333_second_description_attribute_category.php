<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class SecondDescriptionAttributeCategory extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `secondDescriptionAttributeCategory` (
      `secondDescriptionAttributeCategoryId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `categoryId` int(10) NOT NULL,
      `attributeId` int(10) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`secondDescriptionAttributeCategoryId`),
      UNIQUE KEY `categoryIdAttributeId` (`categoryId`, `attributeId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `secondDescriptionAttributeCategory`");
    }
}
