<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddDescriptionColumnTOParsingRule extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `categoryParsingRule` 
    ADD COLUMN `description` varchar(255)";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `categoryParsingRule` 
            DROP COLUMN `description`;";
        $this->query($sql);
    }
}
