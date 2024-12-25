<?php
namespace EcomHelper\Tenant\Model;

use Skeletor\Core\Model\Model;

class Tenant extends Model
{

    /**
     * @param int $id
     * @param string $name
     * @param string $productionUrl
     * @param string $developmentUrl
     * @param string $prodAuthToken
     * @param string $devAuthToken
     * @param \DateTime|null $createdAt
     * @param \DateTime|null $updatedAt
     */
    public function __construct(
        private string $id,
        private string $name,
        private string $productionUrl,
        private string $developmentUrl,
        private string $prodAuthToken,
        private string $devAuthToken,
        private ?\DateTime $createdAt = null,
        private ?\DateTime $updatedAt = null
    ) {
        parent::__construct($createdAt, $updatedAt);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getProductionUrl(): string
    {
        return $this->productionUrl;
    }

    /**
     * @return string
     */
    public function getDevelopmentUrl(): string
    {
        return $this->developmentUrl;
    }

    /**
     * @return string
     */
    public function getProdAuthToken(): string
    {
        return $this->prodAuthToken;
    }

    /**
     * @return string
     */
    public function getDevAuthToken(): string
    {
        return $this->devAuthToken;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }
}