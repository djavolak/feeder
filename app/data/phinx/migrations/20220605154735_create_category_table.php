<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateCategoryTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE category (id VARCHAR(36) NOT NULL, parent VARCHAR(36) DEFAULT NULL, title VARCHAR(255) NOT NULL, count INT NOT NULL, 
level INT NOT NULL, status INT NOT NULL, description VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, secondDescription VARCHAR(255)
 NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, 
 imageId VARCHAR(36) DEFAULT NULL, tenantId VARCHAR(36) DEFAULT NULL, INDEX IDX_64C19C110F3034D (imageId), INDEX IDX_64C19C13D8E604F (parent), 
 INDEX IDX_64C19C1E17F0227 (tenantId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE= InnoDB;
ALTER TABLE category ADD CONSTRAINT FK_64C19C110F3034D FOREIGN KEY (imageId) REFERENCES image (id);
ALTER TABLE category ADD CONSTRAINT FK_64C19C13D8E604F FOREIGN KEY (parent) REFERENCES category (id);
ALTER TABLE category ADD CONSTRAINT FK_64C19C1E17F0227 FOREIGN KEY (tenantId) REFERENCES tenant (id);";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `category`");
    }
}
