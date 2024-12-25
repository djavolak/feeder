<?php
namespace EcomHelper\Product\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Product\Model\Supplier as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'supplier')]
class Supplier
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $name;

    #[ORM\Column(type: Types::STRING)]
    private string $code;

    #[ORM\Column(type: Types::STRING)]
    private string $skuPrefix;

    #[ORM\Column(type: Types::INTEGER)]
    private string $status;
    #[ORM\Column(type: Types::STRING)]
    private string $feedSource;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $feedUsername = null;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $feedPassword = null;
    #[ORM\Column(type: Types::STRING)]
    private string $sourceType;

    public function populateFromDto(DtoModel $dto)
    {
        $this->id = $dto->getId();
        $this->name = $dto->getName();
        $this->code = $dto->getCode();
        $this->skuPrefix = $dto->getSkuPrefix();
        $this->status = $dto->getStatus();
        $this->feedSource = $dto->getFeedSource();
        $this->feedUsername = $dto->getFeedUsername();
        $this->feedPassword = $dto->getFeedPassword();
        $this->sourceType = $dto->getSourceType();
    }

    public function getId()
    {
        return $this->id;
    }
}