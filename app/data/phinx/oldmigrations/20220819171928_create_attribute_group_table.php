<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAttributeGroupTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `attributeGroup` (
      `attributeGroupId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(128) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`attributeGroupId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `attributeGroup`");
    }
}
