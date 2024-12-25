<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddIndexesToProductImagesTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `productImages` 
ADD INDEX `main` (`main` ASC),
ADD INDEX `productId` (`productId` ASC),
ADD INDEX `imageId` (`imageId` ASC);";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `productImages` DROP INDEX `main`, DROP INDEX `productId`, DROP INDEX `imageId`;";
        $this->query($sql);
    }
}
