<?php
namespace EcomHelper\Attribute\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Category\Entity\Category;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Attribute\Model\AttributeGroup as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'attributeGroup')]
class Group
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING)]
    private string $name;
    #[ORM\OneToOne(targetEntity: Category::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'categoryId', referencedColumnName: 'id', unique: false, nullable: true)]
    private ?Category $category;
    #[ORM\ManyToMany(targetEntity: Attribute::class, fetch: 'EAGER', inversedBy: 'groups')]
    #[ORM\JoinColumn(name: 'groupId', referencedColumnName: 'id', unique: false, nullable: true)]
    private Collection $attributes;

    public function populateFromDto(DtoModel $dto)
    {
        if ($dto->getId()) {
            $this->id = $dto->getId();
        }
        $this->name = $dto->getName();
    }

    public function setAttributes(Collection $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function addAttribute(Attribute $attribute)
    {
        $this->attributes[] = $attribute;
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }
}