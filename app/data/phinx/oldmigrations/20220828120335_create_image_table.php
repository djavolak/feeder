<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateImageTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `image` (
      `imageId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `filename` varchar(128) NOT NULL,
      `alt` varchar(128) NOT NULL,
      `type` int(1) NOT NULL,
      `mimeType` varchar(16) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`imageId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `image`");
    }
}
