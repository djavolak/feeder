<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUnmappedCategoryTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `unMappedCategory` (
  `unMappedCategoryId` int(6) unsigned NOT NULL AUTO_INCREMENT,
  `source1` varchar(64) NOT NULL,
  `source2` varchar(64) NULL,
  `source3` text NULL,
  `supplierId` int(6) NULL,
  `count` int(6) NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`unMappedCategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `unMappedCategory`");
    }
}
