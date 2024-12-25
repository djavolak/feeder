<?php
namespace EcomHelper\Attribute\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Attribute\Model\AttributeValue as DtoModel;
use Skeletor\Image\Entity\Image;

#[ORM\Entity]
#[ORM\Table(name: 'attributeValue')]
class AttributeValue
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $value;
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $link;
    #[ORM\ManyToOne(targetEntity: Image::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'imageId', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?Image $image;

    #[ORM\ManyToMany(targetEntity: Attribute::class, mappedBy: 'values', fetch: 'LAZY')]
    private Collection $attributes;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->value = $dto->getValue();
        $this->link = $dto->getLink();
    }

    public function getGroup(Group $group)
    {
        return $this->group = $group;
    }

    public function setImage(Image $image)
    {
        $this->image = $image;
    }

    public function getId()
    {
        return $this->id;
    }
}