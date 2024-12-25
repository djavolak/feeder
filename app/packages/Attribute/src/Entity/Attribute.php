<?php
namespace EcomHelper\Attribute\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Attribute\Model\Attribute as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'attribute')]
class Attribute
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $name;
    #[ORM\Column(type: Types::INTEGER)]
    private int $position;
    #[ORM\Column(type: Types::SMALLINT)]
    private int $isVisible;
    #[ORM\Column(type: Types::SMALLINT)]
    private int $isFilter;
    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'attributes', fetch: 'EAGER')]
    private Collection $groups;
    #[ORM\ManyToMany(targetEntity: AttributeValue::class, inversedBy: 'attributes', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'attributeId', referencedColumnName: 'id', onDelete: 'CASCADE')] //
    private Collection $values;
    //TODO wont remove orphans...

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->name = $dto->getName();
        $this->position = $dto->getPosition();
        $this->isVisible = $dto->getIsVisible();
        $this->isFilter = $dto->getIsFilter();
    }

    public function getGroups()
    {
        return $this->groups;
    }

    public function setGroups(Collection $groups)
    {
        $this->groups = $groups;
    }

    public function setValues(Collection $values)
    {
        $this->values = $values;
    }

    public function getValues(Collection $values)
    {
        return $this->values;
    }

    public function getId()
    {
        return $this->id;
    }
}