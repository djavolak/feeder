<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `product` (
  `productId` int(14) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL,
  `sku` varchar(64) NOT NULL,
  `images` text NULL,
  `price` decimal(14,2) NOT NULL,
  `inputPrice` decimal(14,2) NOT NULL,
  `description` text NULL,
  `specialPrice` decimal(14,2) NULL,
  `specialPriceFrom` datetime NULL,
  `specialPriceTo` datetime NULL,
  `status` int(1) NOT NULL,
  `stockStatus` int(1) NOT NULL DEFAULT 0,
  `quantity` int(5) NULL,
  `category` int(6) NOT NULL,
  `weight` decimal(10,4) NULL,
  `barcode` varchar(32) NULL,
  `ean` varchar(32) NULL,
  `attributes` varchar(32) NULL,
  `supplierId` int(4) NULL,
  `supplierProductId` varchar(32) NULL,
  `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`productId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);

        $this->query("ALTER TABLE `product` 
ADD INDEX `supplier` (`supplierId` ASC),
ADD INDEX `status` (`status` ASC),
ADD INDEX `stockStatus` (`stockStatus` ASC);
;");
    }

    public function down()
    {
        $this->query("DROP TABLE `product`");
    }
}
