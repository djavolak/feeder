<?php

namespace EcomHelper\MarginGroups\Repository;

use EcomHelper\MarginGroups\Mapper\MarginGroups as MarginGroupsMapper;
use Laminas\Config\Config;
use Skeletor\Model\Model;
use Skeletor\TableView\Repository\TableViewRepository;

class MarginGroups extends TableViewRepository
{

    public function __construct(MarginGroupsMapper $mapper, \DateTime $dt, Config $config)
    {
        parent::__construct($mapper, $dt);
        $this->config = $config;
    }

    function make($itemData): Model
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
        return new \EcomHelper\MarginGroups\Model\MarginGroups(...$data);
    }

    function getSearchableColumns(): array
    {
       return ['name'];
    }
}