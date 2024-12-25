<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenamePrimaryKeyOnSupplierAttributesTable extends AbstractMigration
{
    public function up()
    {
        $this->table('supplierAttribute')
            ->renameColumn('supplierAttributeMappingId', 'supplierAttributeId')
            ->save();
        $this->table('productSupplierAttributes')
            ->renameColumn('supplierAttributeMappingId', 'supplierAttributeId')
            ->save();
    }

    public function down()
    {
        $this->table('supplierAttribute')
            ->renameColumn('supplierAttributeId', 'supplierAttributeMappingId')
            ->save();
        $this->table('productSupplierAttributes')
            ->renameColumn('supplierAttributeId', 'supplierAttributeMappingId')
            ->save();
    }
}
