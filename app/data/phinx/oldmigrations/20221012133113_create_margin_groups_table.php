<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateMarginGroupsTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE `marginGroups` (
      `marginGroupId` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `name` varchar(128) NOT NULL,
      `rules` text NOT NULL,
      `limiter` text NOT NULL,
      `margin` text NOT NULL,
      `createdAt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `updatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`marginGroupId`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `marginGroups`");
    }
}
