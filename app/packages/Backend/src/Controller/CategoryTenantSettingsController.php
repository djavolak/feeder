<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Category\Repository\CategoryRepository;
use EcomHelper\Category\Repository\CategoryTenantSettings;
use Skeletor\Controller\CrudController;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager as Session;
use League\Plates\Engine;
use Psr\Log\LoggerInterface as Logger;
use Tamtamchik\SimpleFlash\Flash;

class CategoryTenantSettingsController extends CrudController
{

    public function __construct(
        private CategoryRepository $categoryRepository,
        private Session $session,
        private Config $config,
        private Flash $flash,
        private Engine $template,
        private Logger $logger,
        private CategoryTenantSettings $categoryTenantSettingsRepository
    )
    {
        parent::__construct($categoryRepository, $session, $config, $flash, $template, $logger);
    }

    /**
     * @throws \Exception
     */
    public function view(): Response
    {
        $this->setGlobalVariable('pageTitle', self::TITLE_VIEW);
        $data = $this->getRequest()->getAttributes();
        $tenantId = $data['id'] ?? null;
        if ($tenantId) {
            $categories = $this->categoryTenantSettingsRepository->fetchAll(['tenantId' => $tenantId]);
            if ($categories !== []) {
                return $this->respond('view', [
                    'models' => $categories,
                    'update' => true,
                    'tenantId' => $tenantId,
                ]);
            }
        }
        $categories = $this->categoryRepository->fetchAll();
        return $this->respond('view', [
            'models' => $categories,
            'update' => false
        ]);
    }

    /**
     * @throws \Exception
     */
    public function create(): Response
    {
        $tenantId = $_POST['tenantId'] ?? null;
        if ($tenantId) {
            foreach ($_POST['categories'] as $categoryId => $remoteData) {
                $label = $remoteData['label'];
                $slug = $remoteData['slug'];
                $category = $this->categoryRepository->getById($categoryId);
                if (strlen($label) === 0) {
                    $label = $category->getTitle();
                }
                if (strlen($slug) === 0) {
                    $slug = $category->getSlug();
                }
                $this->categoryTenantSettingsRepository->create(
                    [
                        'categoryId' => $categoryId,
                        'tenantId' => $tenantId,
                        'label' => $label,
                        'slug' => $slug
                    ]);
            }
        }
        return $this->redirect('/category-tenant-settings/view/'.$tenantId.'/');
    }

    /**
     * @throws \Exception
     */
    public function update(): Response
    {
        $data = $this->getRequest()->getAttributes();
        $tenantId = $data['id'] ?? null;
        if ($tenantId) {
            foreach ($_POST['categories'] as $categoryId => $data) {
                $label = $data['label'];
                $slug = $data['slug'];
                $category = $this->categoryRepository->getById($categoryId);
                if (strlen($label) === 0) {
                    $label = $category->getTitle();
                }
                if (strlen($slug) === 0) {
                    $slug = $category->getSlug();
                }
                $this->categoryTenantSettingsRepository->update(
                    [
                        'categoryId' => $categoryId,
                        'tenantId' => $tenantId,
                        'label' => $label,
                        'slug' => $slug,
                        'id' => $data['settingId']
                    ]);
            }
        }
        return $this->redirect('/category-tenant-settings/view/'.$tenantId .'/');
    }
}