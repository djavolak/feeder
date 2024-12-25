<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameSupplierAttributesMappingTable extends AbstractMigration
{
   public function up ()
   {
         $this->table('supplierAttributeMapping')
              ->rename('supplierAttribute')
              ->save();
   }

    public function down ()
    {
            $this->table('supplierAttribute')
                  ->rename('supplierAttributeMapping')
                  ->save();
    }
}
