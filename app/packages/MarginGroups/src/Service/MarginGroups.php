<?php

namespace EcomHelper\MarginGroups\Service;

use EcomHelper\Category\Service\CategoryMapper;
use EcomHelper\MarginGroups\Repository\MarginGroups as MarginGroupsRepo;
use GuzzleHttp\Psr7\ServerRequest as Request;
use Psr\Log\LoggerInterface;
use Skeletor\Activity\Service\Activity;
use Skeletor\TableView\Model\Column;
use Skeletor\TableView\Service\Table as TableView;
use Skeletor\User\Service\User;

class MarginGroups extends TableView
{

    public function __construct(MarginGroupsRepo $repo, User $user, LoggerInterface $logger,
        \EcomHelper\MarginGroups\Filter\MarginGroups $filter, Activity $activity, private CategoryMapper $categoryMapperService)
    {
        parent::__construct($repo, $user, $logger, null, $filter, null);
    }

    public function compileTableColumns()
    {
       $defs = [
           new Column('name', 'Name'),
           new Column('updatedAt', 'Updated at'),
           new Column('createdAt', 'Created at'),
       ];

       return $defs;
    }

    public function fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter = null)
    {

        $data = $this->repo->fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter);
        $items = [];
        foreach ($data['entities'] as $marginGroup) {
            $items[] = [
                'id' => $marginGroup->getId(),
                'name' => $marginGroup->getName(),
                'createdAt' => $marginGroup->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $marginGroup->getCreatedAt()->format('d.m.Y'),
            ];
        }
        return [
            'count' => $data['count'],
            'entities' => $items,
        ];
    }

    public function getGroups() {
        return  $this->repo->fetchAll();
    }

    public function create(Request $request)
    {
        if ($this->filter) {
            $data = $this->filter->filter($request);
        } else {
            $data = $request->getParsedBody();
        }
        return $this->repo->create($data);
    }

    public function update(Request $request)
    {
        if ($this->filter) {
            $data = $this->filter->filter($request);
        } else {
            $data = $request->getParsedBody();
        }
        $oldModel = $this->repo->getById((int) $request->getAttribute('id'));
        $model = $this->repo->update($data);

        $marginGroupId = $model->getId();
        $rules = $model->getRules();
        $limiter = $model->getLimiter();
        $margin = $model->getMargin();

        $categoryMappingsWithMarginGroupId = $this->categoryMapperService->getMappingsByMarginGroupId($marginGroupId);
        foreach($categoryMappingsWithMarginGroupId as $categoryMapping) {
            $this->categoryMapperService->updateField('rules', $rules, $categoryMapping['categoryMappingId']);
            $this->categoryMapperService->updateField('limiter', $limiter, $categoryMapping['categoryMappingId']);
            $this->categoryMapperService->updateField('margin', $margin, $categoryMapping['categoryMappingId']);
        }

        $this->createActivity($model, $oldModel);

        return $model;
    }

}