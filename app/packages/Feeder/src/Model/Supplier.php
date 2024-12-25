<?php
namespace EcomHelper\Feeder\Model;

use Skeletor\Model\Model;

class Supplier extends Model
{
    private $supplierId;

    private $name;

    /**
     * @param $productId
     * @param $title
     * @param $createdAt
     * @param $updatedAt
     */
    public function __construct(
        $supplierId, $name
    ) {
        parent::__construct(null, null);
        $this->supplierId = $supplierId;
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return (int) $this->supplierId;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

}