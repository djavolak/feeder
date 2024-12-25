<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateImageTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE image (id VARCHAR(36) NOT NULL, filename VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, type INT NOT NULL, mimeType 
VARCHAR(255) NOT NULL, label VARCHAR(255) DEFAULT NULL, author VARCHAR(255) DEFAULT NULL, orientation VARCHAR(255) DEFAULT NULL, 
createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `image`");
    }
}
