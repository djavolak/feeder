<?php

namespace EcomHelper\Backend\Controller;

use EcomHelper\Category\Model\MarginRule;
use GuzzleHttp\Psr7\Response;
use Laminas\Config\Config;
use Laminas\Session\SessionManager;
use League\Plates\Engine;
use Skeletor\Controller\AjaxCrudController;
use Skeletor\TableView\Service\TableDecorator;
use Tamtamchik\SimpleFlash\Flash;

class MarginGroups extends AjaxCrudController
{
    const TITLE_VIEW = "Margin Groups";
    const TITLE_CREATE = "Create new margin group";
    const TITLE_UPDATE = "Edit margin group: ";
    const TITLE_UPDATE_SUCCESS = "Margin group updated successfully.";
    const TITLE_CREATE_SUCCESS = "Margin group created successfully.";
    const TITLE_DELETE_SUCCESS = "Margin group deleted successfully.";
    const PATH = 'margin-groups';

    protected $tableViewConfig = ['writePermissions' => true, 'useModal' => true];

    public function __construct(\EcomHelper\MarginGroups\Service\MarginGroups $service, SessionManager $session, Config $config, Flash $flash,
    Engine $template)
    {
        parent::__construct($service, $session, $config, $flash, $template);
    }


    public function view(): Response
    {
        $this->setGlobalVariable('pageTitle', static::TITLE_VIEW);
        $tableDecorator = new TableDecorator($this->service->compileTableColumns());
        return $this->respond('view', [
            'tableFilters' => $tableDecorator->generateFiltersHtml(),
            'columnHeaders' => $tableDecorator->generateColumnHeadersForView(),
            'createAction' => $this->tableViewConfig['createAction'],
            'entityPath' => $this->tableViewConfig['entityPath']
        ]);
    }

    public function form(): Response
    {
        $id = (int) $this->getRequest()->getAttribute('id');
        $marginGroup = null;
        $data = [];
        if ($id) {
            $marginGroup = $this->service->getById($id);
            $data = [];
            $rules = unserialize($marginGroup->getRules(),['allowed_classes' => false]);
            $limiters = unserialize($marginGroup->getLimiter(),['allowed_classes' => false]);
            $margins = unserialize($marginGroup->getMargin(),['allowed_classes' => false]);
            if($rules && $limiters && $margins) {
                foreach($rules as $key => $rule) {
                    $data[] = new MarginRule((int) $limiters[$key], $margins[$key]);
                }
            }
            $this->setGlobalVariable('pageTitle', self::TITLE_UPDATE . $marginGroup->getName());
        }
        return $this->respondPartial('form', [
            'model' => $marginGroup,
            'data' => $data
        ]);
    }

    public function getGroupHtmlRules()
    {
        $groupId = (int)$this->getRequest()->getParsedBody()['groupId'];
        $html = '';
        if($groupId) {
            $entity = $this->service->getById($groupId);
            $data = [];
            $rules = unserialize($entity->getRules(),['allowed_classes' => false]);
            $limiters = unserialize($entity->getLimiter(),['allowed_classes' => false]);
            $margins = unserialize($entity->getMargin(),['allowed_classes' => false]);
            if($rules && $limiters && $margins) {
                foreach($rules as $key => $rule) {
                    $data[] = new MarginRule((int) $limiters[$key],  $margins[$key]);
                }
            }
            foreach($data as $marginRule) {
                $html .= '<div class="form-group marginRuleFormGroup marginRuleFromGroup"> <input type="hidden" name="rules[]" value=">=">';
                $html .= '<span>From:</span>';
                $html .= '<input class="form-control" type="number" name="prices[]" aria-label="from" value="' . $marginRule->getPrice() .'">';
                $html .= '<span>Margin:</span>';
                $html .= '<input class="form-control" type="text" name="margins[]" aria-label="margin" value="' .$marginRule->getMargin() . '">';
                $html .= '<div title="delete" class="deleteMargin deleteMarginNew"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg></div>';
                $html .= '</div>';
            }
        }
        $this->getResponse()->getBody()->write(json_encode($html));
        $this->getResponse()->getBody()->rewind();

        return $this->getResponse()->withHeader('Content-Type', 'application/json');
    }
}