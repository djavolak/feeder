<?php

namespace EcomHelper\MarginGroups\Model;

use Skeletor\Model\Model;

class MarginGroups extends Model
{
    public function __construct(private $marginGroupId, private $name, private $rules, private $limiter,
        private $margin, private ?\Datetime $createdAt = null, private ?\Datetime $updatedAt = null)
    {
        parent::__construct($createdAt, $updatedAt);
    }

    public function getId()
    {
       return $this->marginGroupId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function getLimiter()
    {
        return $this->limiter;
    }

    public function getMargin()
    {
        return $this->margin;
    }
}