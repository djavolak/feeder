<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoryParsingRuleTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `categoryParsingRule` (
      `categoryParsingRuleId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `supplierId` int(10) NOT NULL,
      `action` varchar(60)NOT NULL,
      `priority` int(10) default 0,
      `data` text NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`categoryParsingRuleId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `categoryParsingRule`");
    }
}
