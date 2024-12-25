<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTenantToCategoryTable extends AbstractMigration
{
    public function up()
    {
        $sql = "ALTER TABLE `category` 
    ADD COLUMN `tenantId` int(8) NULL;";
        $this->query($sql);
    }

    public function down()
    {
        $sql = "ALTER TABLE `category` 
DROP COLUMN `tenantId`;";
        $this->query($sql);
    }
}
