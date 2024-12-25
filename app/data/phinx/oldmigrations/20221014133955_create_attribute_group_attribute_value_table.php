<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAttributeGroupAttributeValueTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `attributeGroupAttributeValue` (
      `attributeGroupAttributeValueId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `attributeGroupId` int(10) NOT NULL,
      `attributeId` int(10) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`attributeGroupAttributeValueId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `attributeGroupAttributeValue`");
    }
}
