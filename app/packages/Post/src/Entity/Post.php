<?php
namespace EcomHelper\Post\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use EcomHelper\Category\Entity\Category;
use Skeletor\Core\Entity\Timestampable;
use EcomHelper\Post\Model\Post as DtoModel;

#[ORM\Entity]
#[ORM\Table(name: 'post')]
class Post
{
    use Timestampable;

    #[ORM\Column(type: Types::STRING, length: 256)]
    private string $title;

    #[ORM\Column(type: Types::TEXT)]
    private string $description;

    #[ORM\ManyToMany(targetEntity: Category::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'categoryId', referencedColumnName: 'id', unique: false)]
    private ?Category $category;

    public function populateFromDto(DtoModel $post)
    {
        if ($post->getId()) {
            $this->id = $post->getId();
        }
        $this->title = $post->getTitle();
        $this->description = $post->getDescription();
        $this->category = $post->getCategory();
    }

    public function setCategory(Category $category)
    {
        $this->category = $category;
    }

    public function getId()
    {
        return $this->id;
    }
}