<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIndexesToTables extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attribute` ADD INDEX `attrGroupId` (`attributeGroupId` ASC);";
        $this->query($sql);

        $sql = "ALTER TABLE `attributeGroupAttributeValue` 
ADD INDEX `attributeId` (`attributeId` ASC),
ADD INDEX `attrGroupId` (`attributeGroupId` ASC);";
        $this->query($sql);

        $sql = "ALTER TABLE `category` 
ADD INDEX `status` (`status` ASC),
ADD INDEX `tenantId` (`tenantId` ASC);";
        $this->query($sql);

        $sql = "ALTER TABLE `categoryMapping` 
ADD INDEX `supplierId` (`supplierId` ASC),
ADD INDEX `status` (`status` ASC),
ADD INDEX `marginGroupId` (`marginGroupId` ASC);";
        $this->query($sql);

        $sql = "ALTER TABLE `product` ADD INDEX `category` (`category` ASC);";
        $this->query($sql);

        $sql = "ALTER TABLE `tenantCategoryMapping` 
ADD INDEX `categoryId` (`categoryId` ASC),
ADD INDEX `tenantId` (`tenantId` ASC),
ADD INDEX `mappedToId` (`mappedToId` ASC);";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attribute` DROP INDEX `attrGroupId`;";
        $this->query($sql);
        $sql = "ALTER TABLE `attributeGroupAttributeValue` DROP INDEX `attributeId`, DROP INDEX `attrGroupId`;";
        $this->query($sql);
        $sql = "ALTER TABLE `category` DROP INDEX `status`, DROP INDEX `tenantId`;";
        $this->query($sql);
        $sql = "ALTER TABLE `categoryMapping` DROP INDEX `status`, DROP INDEX `supplierId`, DROP INDEX `marginGroupId`;";
        $this->query($sql);
        $sql = "ALTER TABLE `product` DROP INDEX `category`;";
        $this->query($sql);
        $sql = "ALTER TABLE `tenantCategoryMapping` DROP INDEX `categoryId`, DROP INDEX `tenantId`, DROP INDEX `mappedToId`;";
        $this->query($sql);
    }
}
