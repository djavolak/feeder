<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateProductAttributeValuesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `productAttributeValues` (
      `id` int(14) unsigned NOT NULL AUTO_INCREMENT,
      `productId` int(14) unsigned NOT NULL,
      `attributeId` int(10) unsigned NOT NULL,
      `attributeValueId` int(10) unsigned NOT NULL,
      `attributeName` varchar(128) NOT NULL,
      `attributeValue` varchar(128) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      CONSTRAINT `productIdProductAttributeTable`
      FOREIGN KEY (`productId`)
      REFERENCES product(`productId`),
      
      CONSTRAINT `attributeIdProductAttributeTable`
      FOREIGN KEY (`attributeId`)
      REFERENCES attribute(`attributeId`),
      
      CONSTRAINT `attributeValueIdProductAttributeTable`
      FOREIGN KEY (`attributeValueId`)
      REFERENCES attributeValues(`attributeValueId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
        $this->query($sql);

        $this->query("ALTER TABLE `productAttributeValues` 
            ADD INDEX `productIdProductAttributeValues` (`productId`);");
    }

    public function down()
    {
        $this->query("DROP TABLE `productAttributeValues`");
    }
}
