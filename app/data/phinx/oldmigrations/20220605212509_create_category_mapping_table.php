<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoryMappingTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `categoryMapping` (
  `categoryMappingId` int(12) unsigned NOT NULL AUTO_INCREMENT,
  `source1` varchar(64) NOT NULL,
  `source2` varchar(64) NULL,
  `source3` varchar(64) NULL,
  `categoryId` int(6) NULL,
  `supplierId` int(6) NOT NULL,
  `status` int(1) NOT NULL DEFAULT 1,
  `rules` text NULL,
  `limiter` text NULL,
  `margin` text NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`categoryMappingId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);

        $this->query("ALTER TABLE `categoryMapping` 
ADD INDEX `category` (`categoryId` ASC);
ADD INDEX `supplier` (`supplierId` ASC);
");
    }

    public function down()
    {
        $this->query("DROP TABLE `categoryMapping`");
    }
}
