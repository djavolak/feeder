<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ParsingRuleCategory extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `parsingRuleCategory` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `parsingRuleId` int(10) NOT NULL,
      `categoryId` int(10) NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `parsingRuleCategory`");
    }
}
