<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddLinkToAttributeValue extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `attributeValues` 
    ADD COLUMN `link` varchar(255)";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `attributeValues` 
            DROP COLUMN `link`;";
        $this->query($sql);
    }
}
