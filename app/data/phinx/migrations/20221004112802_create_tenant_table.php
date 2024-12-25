<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTenantTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE tenant (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, productionUrl VARCHAR(255) NOT NULL, 
developmentUrl VARCHAR(255) NOT NULL, prodAuthToken VARCHAR(255) NOT NULL, devAuthToken VARCHAR(255) NOT NULL, 
createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `tenant`");
    }
}
