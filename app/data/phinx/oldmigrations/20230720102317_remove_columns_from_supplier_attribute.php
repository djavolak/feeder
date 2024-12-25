<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RemoveColumnsFromSupplierAttribute extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('supplierAttribute');
        $table->removeColumn('localAttribute');
        $table->removeColumn('localAttributeValue');
        $table->save();
    }

    public function down()
    {
        $table = $this->table('supplierAttribute');
        $table->addColumn('localAttribute', 'integer');
        $table->addColumn('localAttributeValue', 'integer');
        $table->save();
    }
}
