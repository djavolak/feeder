<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAttributeValuesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `attributeValues` (
      `attributeValueId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `attributeId` int(10) unsigned NOT NULL,
      `attributeValue` varchar(128) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`attributeValueId`),
      CONSTRAINT `attributeId`
      FOREIGN KEY (`attributeId`)
      REFERENCES attribute(`attributeId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `attributeValues`");
    }
}
