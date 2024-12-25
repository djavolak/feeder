<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateTagTable extends AbstractMigration
{
    public function up()
    {
        $sql = "CREATE TABLE tag (id VARCHAR(36) NOT NULL, title VARCHAR(255) NOT NULL, stickerImagePosition INT NOT NULL, 
priceLabel VARCHAR(255) NOT NULL, stickerLabel INT NOT NULL, createdAt DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, 
updatedAt DATETIME DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP, stickerImageId VARCHAR(36) DEFAULT NULL, 
INDEX IDX_389B783D29117C5 (stickerImageId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB;
ALTER TABLE tag ADD CONSTRAINT FK_389B783D29117C5 FOREIGN KEY (stickerImageId) REFERENCES image (id);";
        $this->query($sql);
    }

    public function down()
    {
        $this->query("DROP TABLE `tag`");
    }
}
