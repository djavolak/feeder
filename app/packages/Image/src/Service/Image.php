<?php
namespace EcomHelper\Image\Service;

use EcomHelper\Image\Repository\ImageRepository;
use GuzzleHttp\Psr7\ServerRequest as Request;
use GuzzleHttp\Psr7\UploadedFile;
use Skeletor\TableView\Model\Column;
use Skeletor\TableView\Service\Table as TableView;
use Psr\Log\LoggerInterface as Logger;
use Skeletor\Activity\Repository\ActivityRepository;
use Skeletor\User\Service\User;

class Image extends TableView
{
    protected $processor;

    /**
     * @param ImageRepository $repo
     * @param User $user
     * @param Logger $logger
     * @param ActivityRepository $activity
     * @param Image $imageService
     */
    public function __construct(
        ImageRepository $repo, User $user, Logger $logger, Processor $processor, ActivityRepository $activity
    ) {
        parent::__construct($repo, $user, $logger, null, null, $activity);
        $this->processor = $processor;
    }

    public function getEntityData(int $id)
    {
        $image = $this->repo->getById($id);

        return [
            'id' => $image->getId(),
            'createdAt' => $image->getUpdatedAt()->format('m.d.Y'),
            'updatedAt' => $image->getCreatedAt()->format('m.d.Y'),
        ];
    }

    public function fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter = null)
    {
        $data = $this->repo->fetchTableData($search, $filter, $offset, $limit, $order, $uncountableFilter);
        if ($data['count'] === "0") {
            return [
                'count' => 0,
                'entities' => [],
            ];
        }
        $items = [];
        foreach ($data['entities'] as $image) {
            $imgHtml = '';
            if ($image->getFilename() !== '') {
                $imageUrl = "/images" . $image->getFilename();
                $imgHtml = '<img width="80px" src="'.$imageUrl.'" alt="image">';
            }

            $items[] = [
                'id' => $image->getId(),
                'filename' => $image->getFilename(),
                'img' => $imgHtml,
                'mimeType' => $image->getMimeType(),
                'alt' => $image->getAlt(),
                'type' => \EcomHelper\Image\Model\Image::getHrType($image->getType()),
                'createdAt' => $image->getCreatedAt()->format('d.m.Y'),
                'updatedAt' => $image->getCreatedAt()->format('d.m.Y'),
            ];
        }
        return [
            'count' => $data['count'],
            'entities' => $items,
        ];
    }

    public function compileTableColumns()
    {
        $columnDefinitions = [
            new Column('id', 'ID'),
            (new Column('filename', 'Filename'))->addJsDataParam('orderable', true)
                ->addJsDataParam('searchable', true)
                ->addJsDataParam('editColumn', true),
            new Column('img', 'Image'),
            new Column('mimeType', 'Type'),
            new Column('alt', 'Alt text'),
            new Column('updatedAt', 'Updated at'),
            new Column('createdAt', 'Created at'),
        ];

        return $columnDefinitions;
    }

    /**
     * @throws \Exception
     */
    public function update(Request $request)
    {
        $data = $request->getParsedBody();
        $data['image'] = '';

        var_dump($data);
        die();

        if ($request->getUploadedFiles()['image']->getSize() !== 0) {
            $imagePath = $this->processor->processUploadedFile($request->getUploadedFiles()['image']);
            $data['image'] = $imagePath;
        }
        if ($data['image'] === '' && $data['oldImage'] !== '') {
                $data['image'] = $data['oldImage'];
        }
        unset($data['oldImage']);
        $oldModel = $this->repo->getById((int) $request->getAttribute('id'));

        $model = $this->repo->update($data);
        $this->activity?->create('update', $model, $this->user->getLoggedInUserId(), $oldModel);
        return $model;
    }

    /**
     * @throws \Exception
     */
    public function create(Request $request)
    {
        $data = $request->getParsedBody();

        return $this->repo->create([
            'type' => 1,
            'mimeType' => 'jpg/jpeg',
            'filename' => $this->processor->processUploadedFile($request->getUploadedFiles()['image']),
            'alt' => $data['alt'] ?? '',
        ]);
    }

    /**
     * @throws \Exception
     */
    public function createFromFile(UploadedFile $file)
    {
        return $this->repo->create([
            'type' => 1,
            'mimeType' => 'jpg/jpeg',
            'filename' => $this->processor->processUploadedFile($file),
            'alt' => $data['alt'] ?? '',
        ]);
    }

    public function createFromPath($path)
    {
        return $this->repo->create([
            'type' => 1,
            'mimeType' => 'jpg/jpeg',
            'filename' => $this->processor->processImage($path),
            'alt' => $data['alt'] ?? '',
        ]);
    }
}