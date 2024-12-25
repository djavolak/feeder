<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUnsignedToTables extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `category` 
CHANGE COLUMN `status` `status` INT UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `level` `level` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `parent` `parent` INT UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `count` `count` INT UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `tenantId` `tenantId` INT UNSIGNED NULL DEFAULT NULL;";
        $this->query($sql);

        $sql = "ALTER TABLE `categoryMapping` 
CHANGE COLUMN `supplierId` `supplierId` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `status` `status` INT UNSIGNED NOT NULL DEFAULT '1' ,
CHANGE COLUMN `count` `count` INT UNSIGNED NULL DEFAULT NULL ;";
        $this->query($sql);

        $sql = "ALTER TABLE `product` 
CHANGE COLUMN `status` `status` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `stockStatus` `stockStatus` INT UNSIGNED NOT NULL DEFAULT '0' ,
CHANGE COLUMN `quantity` `quantity` INT UNSIGNED NULL DEFAULT NULL ,
CHANGE COLUMN `category` `category` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `supplierId` `supplierId` INT UNSIGNED NULL DEFAULT NULL ;";
        $this->query($sql);

        $sql = "ALTER TABLE `productImages` 
CHANGE COLUMN `productId` `productId` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `main` `main` INT UNSIGNED NOT NULL ,
CHANGE COLUMN `sort` `sort` INT UNSIGNED NULL DEFAULT NULL ;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `category` 
CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT '0' ,
CHANGE COLUMN `level` `level` INT NOT NULL ,
CHANGE COLUMN `parent` `parent` INT NULL DEFAULT NULL ,
CHANGE COLUMN `count` `count` INT NULL DEFAULT NULL ,
CHANGE COLUMN `tenantId` `tenantId` INT NULL DEFAULT NULL;";
        $this->query($sql);

        $sql = "ALTER TABLE `categoryMapping` 
CHANGE COLUMN `supplierId` `supplierId` INT NOT NULL ,
CHANGE COLUMN `status` `status` INT NOT NULL DEFAULT '1' ,
CHANGE COLUMN `count` `count` INT NULL DEFAULT NULL ;";
        $this->query($sql);

        $sql = "ALTER TABLE `product` 
CHANGE COLUMN `status` `status` INT NOT NULL ,
CHANGE COLUMN `stockStatus` `stockStatus` INT NOT NULL DEFAULT '0' ,
CHANGE COLUMN `quantity` `quantity` INT NULL DEFAULT NULL ,
CHANGE COLUMN `category` `category` INT NOT NULL ,
CHANGE COLUMN `supplierId` `supplierId` INT NULL DEFAULT NULL ;";
        $this->query($sql);

        $sql = "ALTER TABLE `productImages` 
CHANGE COLUMN `productId` `productId` INT NOT NULL ,
CHANGE COLUMN `main` `main` INT NOT NULL ,
CHANGE COLUMN `sort` `sort` INT NULL DEFAULT NULL ;";
        $this->query($sql);
    }
}
