<?php
namespace EcomHelper\Product\Repository;

use Doctrine\ORM\EntityManagerInterface;
use EcomHelper\Product\Model\Supplier as Model;
use Skeletor\Core\TableView\Repository\TableViewRepository;

class SupplierRepository extends TableViewRepository
{
    const ENTITY = \EcomHelper\Product\Entity\Supplier::class;
    const FACTORY = \EcomHelper\Product\Factory\SupplierFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function make($itemData): Model
    {
        $data = [];
        foreach ($itemData as $name => $value) {
            if (in_array($name, ['createdAt', 'updatedAt'])) {
                $data[$name] = null;
                if ($value) {
                    if (strtotime($value)) {
                        $dt = clone $this->dt;
                        $dt->setTimestamp(strtotime($value));
                        $data[$name] = $dt;
                    } else {
                        $data[$name] = null;
                    }
                }
            } else {
                $data[$name] = $value;
            }
        }

        return new Model(...$data);
    }

    public function getSearchableColumns(): array
    {
        return ['name', 'code'];
    }
}
