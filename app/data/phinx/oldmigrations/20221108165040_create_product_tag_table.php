<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductTagTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `productTag` (
      `productTagId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `productId` int(14) unsigned NOT NULL,
      `tagId` int(10) unsigned NOT NULL,
      `tagTitle` varchar(128) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`productTagId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `productTag`");
    }
}
