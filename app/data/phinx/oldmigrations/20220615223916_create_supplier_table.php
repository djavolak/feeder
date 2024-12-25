<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSupplierTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE supplier (id VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, code VARCHAR(255) NOT NULL, skuPrefix VARCHAR(255) NOT N
ULL, status INT NOT NULL, feedSource VARCHAR(255) NOT NULL, feedUsername VARCHAR(255) NOT NULL, feedPassword VARCHAR(255) NOT NULL, 
sourceType VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAM
P on update CURRENT_TIMESTAMP, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `supplier`");
    }
}
