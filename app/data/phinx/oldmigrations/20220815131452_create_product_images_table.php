<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductImagesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `productImages` (
  `productImagesId` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `productId` int(14) NOT NULL,
  `main` int(1) NOT NULL,
  `sort` int(3) NULL,
  `file` varchar(255) NOT NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`productImagesId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `productImages`");
    }
}
