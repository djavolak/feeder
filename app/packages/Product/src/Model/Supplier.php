<?php
namespace EcomHelper\Product\Model;

use Skeletor\Core\Model\Model;

class Supplier extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const TYPE_LOCAL = 2;
    const TYPE_HTTP = 1;

    private $id;

    private $name;

    private $code;

    private $skuPrefix;

    private $status;

    private $feedSource;

    private $sourceType;

    private $feedUsername;

    private $feedPassword;

    public function __construct(
        $id, $name, $code, $skuPrefix, $status, $feedSource, $feedUsername, $feedPassword, $sourceType,
        $createdAt = null, $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
        $this->id = $id;
        $this->name = $name;
        $this->code = $code;
        $this->skuPrefix = $skuPrefix;
        $this->status = $status;
        $this->feedSource = $feedSource;
        $this->feedUsername = $feedUsername;
        $this->feedPassword = $feedPassword;
        $this->sourceType = $sourceType;
    }

    public static function getHRStatus($status)
    {
        return static::getHRStatuses()[$status];
    }

    public static function getHRStatuses()
    {
        return [
            static::STATUS_ACTIVE => 'Aktivan',
            static::STATUS_INACTIVE => 'Neaktivan'
        ];
    }

    /**
     * @return mixed
     */
    public function getSourceType()
    {
        return $this->sourceType;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getSkuPrefix()
    {
        return $this->skuPrefix;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getFeedSource()
    {
        return $this->feedSource;
    }

    /**
     * @return mixed
     */
    public function getFeedUsername()
    {
        return $this->feedUsername;
    }

    /**
     * @return mixed
     */
    public function getFeedPassword()
    {
        return $this->feedPassword;
    }
}