<?php
namespace EcomHelper\Tenant\Service;

use EcomHelper\Tenant\Repository\TenantRepository;
use EcomHelper\Tenant\Filter\Tenant as Filter;
use Skeletor\Core\TableView\Service\TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Core\Activity\Repository\ActivityRepository;
use Skeletor\User\Service\Session;

class Tenant extends TableView
{

    /**
     * @param TenantRepository $repo
     * @param Session $user
     * @param Logger $logger
     * @param ActivityRepository $activity
     */
    public function __construct(
        TenantRepository $repo, Session $user, Logger $logger, Filter $filter
    ) {
        parent::__construct($repo, $user, $logger, $filter);
    }

    public function getEntityData($id)
    {
        $image = $this->repo->getById($id);

        return [
            'id' => $image->getId(),
            'createdAt' => $image->getUpdatedAt()->format('m.d.Y'),
            'updatedAt' => $image->getCreatedAt()->format('m.d.Y'),
        ];
    }

    public function prepareEntities($entities)
    {
        $items = [];
        foreach ($entities as $tenant) {
            $itemData = [
                'id' => $tenant->getId(),
                'name' =>  [
                    'value' => $tenant->getName(),
                    'editColumn' => true,
                ],
                'productionUrl' => $tenant->getProductionUrl(),
                'developmentUrl' => $tenant->getDevelopmentUrl(),
//                'prodAuthToken' => $tenant->getProdAuthToken(),
//                'devAuthToken' => $tenant->getDevAuthToken(),
                'createdAt' => $tenant->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $tenant->getCreatedAt()->format('d.m.Y'),
            ];
            $items[] = [
                'columns' => $itemData,
                'id' => $tenant->getId(),
            ];
        }
        return $items;
    }

    public function compileTableColumns()
    {
        $columnDefinitions = [
//            ['name' => 'id', 'label' => 'ID'],
            ['name' => 'name', 'label' => 'Name'],
            ['name' => 'productionUrl', 'label' => 'Production URL'],
            ['name' => 'developmentUrl', 'label' => 'Development URL'],
            ['name' => 'updatedAt', 'label' => 'Updated at'],
            ['name' => 'createdAt', 'label' => 'Created at'],
        ];

        return $columnDefinitions;
    }

}