<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeCatgoryImagesColumnToImage extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `category` 
    change images image text NULL;";
        $this->query($sql);
    }
    public function down()
    {
        $sql = "ALTER TABLE `category` 
           change image images text NULL;";
        $this->query($sql);
    }
}
