<?php
namespace EcomHelper\Post\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Skeletor\Core\TableView\Repository\TableViewRepository;

class PostRepository extends TableViewRepository
{
    const ENTITY = \EcomHelper\Post\Entity\Post::class;
    const FACTORY = \EcomHelper\Post\Factory\PostFactory::class;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getSearchableColumns(): array
    {
        return ['title', 'description'];
    }
}
