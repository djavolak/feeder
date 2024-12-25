<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MigrateDataFromSupplierAttribiutes extends AbstractMigration
{
    public function up()
    {
        $this->execute('insert into supplierAttributeLocalAttribute (supplierAttributeId, localAttributeId, localAttributeValueId) 
        select supplierAttributeId, localAttribute, localAttributeValue from supplierAttribute where localAttribute 
            is not null and localAttributeValue is not null');
    }
}
