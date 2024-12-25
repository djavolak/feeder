<?php

namespace EcomHelper\Tenant\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Tenant\Model\Tenant as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'tenant')]
class Tenant
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    private string $productionUrl;

    #[ORM\Column(type: Types::STRING)]
    private string $developmentUrl;

    #[ORM\Column(type: Types::STRING)]
    private string $prodAuthToken;
    #[ORM\Column(type: Types::STRING)]
    private string $devAuthToken;

    public function populateFromDto(DtoModel $dto)
    {
        $this->id = $dto->getId();
        $this->name = $dto->getName();
        $this->productionUrl = $dto->getProductionUrl();
        $this->developmentUrl = $dto->getDevelopmentUrl();
        $this->prodAuthToken = $dto->getProdAuthToken();
        $this->devAuthToken = $dto->getDevAuthToken();
    }

    public function getId()
    {
        return $this->id;
    }
}