<?php
namespace EcomHelper\Image\Repository;

use League\Flysystem\Filesystem;
use Skeletor\TableView\Repository\TableViewRepository;
use EcomHelper\Image\Mapper\Image as Mapper;
use EcomHelper\Image\Model\Image as Model;
use Laminas\Config\Config;

class ImageRepository extends TableViewRepository
{

    private $filesystem;

    /**
     * userRepository constructor.
     *
     * @param Mapper $userMapper
     * @param \DateTime $dt
     * @param Config $config
     */
    public function __construct(Mapper $mapper, \DateTime $dt, Filesystem $filesystem)
    {
        parent::__construct($mapper, $dt);
        $this->filesystem = $filesystem;
    }

    public function fetchForApi($tenantId)
    {
        $items = [];
        foreach ($this->mapper->fetchForApi($tenantId) as $data) {
            $items[] = $this->make($data);
        }

        return $items;
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

        if (!isset($data['createdAt'])) {
            $data['createdAt'] = null;
        }
        if (!isset($data['updatedAt'])) {
            $data['updatedAt'] = null;
        }
        return new Model(...$data);
    }

    public function getSearchableColumns(): array
    {
        return ['filename', 'alt'];
    }
}
