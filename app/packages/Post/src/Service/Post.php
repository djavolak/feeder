<?php

namespace EcomHelper\Post\Service;

use EcomHelper\Post\Filter\Post as Filter;
use EcomHelper\Post\Repository\PostRepository;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\TableView\Service\TableView;
use Skeletor\User\Service\Session;

class Post extends TableView
{
    /**
     * @param PostRepository $repo
     * @param Session $user
     * @param Logger $logger
     */
    public function __construct(
        PostRepository $repo,
        Session $user,
        Logger $logger,
        Filter $filter
    ) {
        parent::__construct($repo, $user, $logger, $filter);
    }

    public function getEntityData($id)
    {
        $post = $this->repo->getById($id);

        return [
            'id' => $post->getId(),
            'createdAt' => $post->getUpdatedAt()->format('m.d.Y'),
            'updatedAt' => $post->getCreatedAt()->format('m.d.Y'),
            'category' => $post->getCategory(),
        ];
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $post) {
            $itemData = [
                'id' => $post->getId(), // @TODO ovaj id je obavezan da se printa, to treba pochistimo na data tables-u
                'title' => [
                    'value' => $post->getTitle(),
                    'editColumn' => true,
                ],
                'description' => $post->getDescription(),
                'createdAt' => $post->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $post->getCreatedAt()->format('d.m.Y'),
                'category' => $post->getCategory()?->getName() ?? 'No category',
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $post->getId(),
            ];
        }
        return $items;
    }

    public function compileTableColumns()
    {
        $columnDefinitions = [
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'description', 'label' => 'Description'],
            ['name' => 'category', 'label' => 'Category'],
        ];

        return $columnDefinitions;
    }

}