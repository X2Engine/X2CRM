<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/






Yii::import ('application.modules.reports.components.reports.*');

/**
 * Hello darkness, my old friend. I've come to talk with you again...
 * @package application.modules.reports.controllers
 */
class ReportsController extends x2base {

    public $modelClass = 'Reports';

    public function behaviors(){
        return array_merge(parent::behaviors(),array(
            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.'.
                        'MobileReportsControllerBehavior'
            ),
            'ImportExportBehavior' => array(
                'class' => 'ImportExportBehavior'
            ),
        ));
    }

    /**
     * Create a menu for Reports
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Reports = Modules::displayName();

        $menuItems = array(
            array(
                'name' => 'chartDashboard',
                'label' => Yii::t('reports', 'Charting Dashboard'),
                'url'=>array('chartDashboard')
            ),
            array(
                'name' => 'savedReports',
                'label' => Yii::t('reports', 'Saved {reports}', array(
                    '{reports}' => $Reports,
                )),
                'url'=>array('savedReports')
            ),
            array(
                'name' => 'grid',
                'label' => Yii::t('reports', 'Grid Builder'),
                'url' => array ('gridReport')
            ),
            array(
                'name' => 'rowsAndColumns',
                'label' => Yii::t('reports', 'Rows & Columns'),
                'url' => array ('rowsAndColumnsReport')
            ),
            array(
                'name' => 'summation',
                'label' => Yii::t('reports', 'Summation'),
                'url' => array ('summationReport')
            ),
            
            array(
                'name' => 'leadPerformance',
                'label' => Yii::t('reports', 'Lead Performance'),
                'url' => array ('leadPerformance')
            ),
            array(
                'name' => 'workflow',
                'label' => Yii::t('reports', 'Process'),
                'url' => array ('workflow')
            ),
            array(
                'name' => 'activityReport',
                'label' => Yii::t('reports', 'User Activity'),
                'url' => array ('activityReport')
            ),
            array(
                'name' => 'externalReports',
                'label' => Yii::t('reports', 'External Reports'),
                'url' => array ('externalReport')
            ),
        );

// highlights menu item corresponding to report type. Commented out since it prevents you from
// creating new reports of that type.
//        $this->prepareMenu($menuItems, $selectOptions);
//        if ($model) {
//            if (in_array ($model->type, array ('summation', 'grid', 'rowsAndColumns'))) {
//                foreach ($menuItems as &$item) {
//                    if (isset ($item['name']) && $item['name'] === $model->type) {
//                        unset ($item['url']);
//                        break;
//                    }
//                }
//            }
//        }

        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }

    /**
     * View saved reports
     */
    public function actionIndex() {
        $this->redirect('savedReports');
    }

    /**
     * Left here for potential compatibility purposes, "actionAdmin" was phased
     * out many versions ago. Any lingering ones simply redirect to the index
     * in case some URL still points here.
     */
    public function actionAdmin() {
        $this->redirect('index');
    }

    /**
     * View a saved report 
     * @param int $id
     */
    public function actionView ($id) {
        User::addRecentItem ('Reports', $id);
        $report = $this->loadModel($id);
        $reportMethodName = 'action'.ucfirst ($report->type).'Report';
        if (!isset ($_GET[$report->getFormModelName ()])) 
            $_GET[$report->getFormModelName ()] = array ();
        $settings = CJSON::decode ($report->settings);
        if (!$settings) $settings = array ();
        $_GET[$report->getFormModelName ()] = array_merge (
            $settings,
            $_GET[$report->getFormModelName ()]);
        $this->$reportMethodName ($report);
    }

    /**
     * Copy a report
     * @param int $id report id
     * @param string $name name to give the copy
     */
    public function actionCopy ($id, $name) {

        // copy the report
        $report = $this->loadModel($id);
        $attributes = $report->getAttributes ();
        unset ($attributes['id']);
        $copy = new Reports;
        $copy->setAttributes ($attributes, false);
        $copy->name = $name;
        $copy->settings = $report->settings;
        $copy->version = Yii::app()->params->version;
        $copy->createdBy = Yii::app()->user->getName ();

        if ($copy->save (false)) {
            // copy the old report's charts and update the chart ids in the new reports layout
            $widgetLayout = $copy->dataWidgetLayout;
            foreach ($report->charts as $chart) {
                $chartAttributes = $chart->getAttributes ();
                unset ($chartAttributes['id']);
                $chartAttributes['reportId'] = $copy->id;
                $chartCopy = new Charts;
                $chartCopy->setAttributes ($chartAttributes, false);
                $chartCopy->save (false);
                foreach ($widgetLayout as $widgetName => &$settings) {
                    if ($settings['chartId'] === $chart->id) {
                        $settings['chartId'] = $chartCopy->id;
                    }
                }
            }
            $copy->dataWidgetLayout = $widgetLayout;
            $copy->save (false);
            $this->redirect (Yii::app()->createUrl ('/reports/view', array (
                'id' => $copy->id,
            )));
        } 
    }

    /**
     * Update a saved report 
     * @param int $id
     */
    public function actionUpdate ($id) {
        if (!isset ($_GET['Reports'])) {
            throw new CHttpException(400,
                Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }
        $report = $this->loadModel($id);
        unset ($_GET['id']);
        $report->attributes = $_GET['Reports'];
        if ($report->save ()) {
            echo CJSON::encode (array ('flashes' => array (
                'success' => array (Yii::t('reports', 'Report saved')),
            )));
            Yii::app()->end ();
        }

        echo CJSON::encode (array ('flashes' => array (
            'error' => array (Yii::t('reports', 'Report could not be saved')),
        )));
    }

    /**
     * Delete a saved report
     * @param int $id The id of the report to be deleted
     */
    public function actionDelete($id) {
        $report = $this->loadModel($id);
        $report->delete();
	}

    // Displays all visible Contact Lists
    public function actionLists() {
        $filter = new Reports('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Reports"');
        $criteria->addCondition('type="static" OR type="dynamic"');
        if (!Yii::app()->params->isAdmin) {
            $condition = 'visibility="1" OR assignedTo="Anyone" OR 
                 assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()
                            ->select('groupId')
                            ->from('x2_group_to_user')
                            ->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks))
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';

            $condition .= 'OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId 
                     FROM x2_group_to_user 
                     WHERE userId=' . Yii::app()->user->getId() . ')
                )
            )';
            $criteria->addCondition($condition);
        }

        $perPage = Profile::getResultsPerPage();

        //$criteria->offset = isset($_GET['page']) ? $_GET['page'] * $perPage - 3 : -3;
        //$criteria->limit = $perPage;
        $criteria->order = 'createDate DESC';
        $filter->compareAttributes($criteria);

        $contactLists = X2Model::model('Reports')->findAll($criteria);

        $totalContacts = X2Model::model('X2Leads')->count();
        $totalMyContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new Reports;
        $allContacts->attributes = array(
            'id' => 'all',
            'name' => Yii::t('contacts', 'All {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $newContacts = new Reports;
        $newContacts->attributes = array(
            'id' => 'new',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'New {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalNewContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $myContacts = new Reports;
        $myContacts->attributes = array(
            'id' => 'my',
            'assignedTo' => Yii::app()->user->getName(),
            'name' => Yii::t('contacts', 'My {module}', array('{module}' => Modules::displayName())),
            'description' => '',
            'type' => 'dynamic',
            'visibility' => 1,
            'count' => $totalMyContacts,
            'createDate' => 0,
            'lastUpdated' => 0,
        );
        $contactListData = array(
            $allContacts,
            $myContacts,
            $newContacts,
        );

        $filteredPseudoLists = $filter->filter($contactListData);
        $lists = array_merge($filteredPseudoLists, $contactLists);
        $dataProvider = new CArrayDataProvider($lists, array(
            'pagination' => array('pageSize' => $perPage),
            'sort' => array(
                'attributes' => array(
                    'name' => array(
                        'asc' => 'name asc, id desc',
                        'desc' => 'name desc, id desc',
                    ),
                    // secondary order is needed to fix https://github.com/yiisoft/yii/issues/2082
                    'type' => array(
                        'asc' => 'type asc, id desc',
                        'desc' => 'type desc, id desc',
                    ),
//                    'count' => array (
//                        'asc' => 'count asc, id desc',
//                        'desc' => 'count desc, id desc',
//                    ),
                    'assignedTo' => array(
                        'asc' => 'assignedTo asc, id desc',
                        'desc' => 'assignedTo desc, id desc',
                    ),
                )),
            'totalItemCount' => count($contactLists) + 3,
        ));

        $this->render('listIndex', array(
            'contactLists' => $dataProvider,
            'filter' => $filter,
        ));
    }
    
    /**
     * Return a JSON encoded list of Contact lists
     */
    public function actionGetLists() {
        if (!Yii::app()->user->checkAccess('ContactsAdminAccess')) {
            $condition = ' AND (visibility="1" OR assignedTo="Anyone"  OR assignedTo="' . Yii::app()->user->getName() . '"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId=' . Yii::app()->user->getId())->queryColumn();
            if (!empty($groupLinks)) {
                $condition .= ' OR assignedTo IN (' . implode(',', $groupLinks) . ')';
            }

            $condition .= ' OR (visibility=2 AND assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                (SELECT groupId FROM x2_group_to_user WHERE userId=' . Yii::app()->user->getId() . '))))';
        } else {
            $condition = '';
        }
        // Optional search parameter for autocomplete
        $qterm = isset($_GET['term']) ? $_GET['term'] . '%' : '';
        $static = isset($_GET['static']) && $_GET['static'];
        $weblist = isset($_GET['weblist']) && $_GET['weblist'];
        $result = Yii::app()->db->createCommand()
                ->select('id,name as value')
                ->from('x2_lists')
                ->where(
                        ($static ? 'type="static" AND ' : '') .
                        ($weblist ? 'type="weblist" AND ' : '') .
                        'modelName="X2Leads" AND type!="campaign" 
                    AND name LIKE :qterm' . $condition, array(':qterm' => $qterm))
                ->order('name ASC')
                ->queryAll();
        echo CJSON::encode($result);
    }

    // Shows contacts in the specified list
    public function actionList($id = null) {
        $list = Reports::load($id);

        if (!isset($list)) {
            Yii::app()->user->setFlash(
                    'error', Yii::t('app', 'The requested page does not exist.'));
            $this->redirect(array('lists'));
        }

        $model = new X2Leads('search');
        Yii::app()->user->setState('vcr-list', $id);
        $dataProvider = $model->searchList($id);
        $list->count = $dataProvider->totalItemCount;
        $list->runWithoutBehavior('FlowTriggerBehavior', function () use ($list) {
            $list->save();
        });

        X2Flow::trigger('RecordViewTrigger', array('model' => $list));
        $this->render('list', array(
            'listModel' => $list,
            'dataProvider' => $dataProvider,
            'model' => $model,
        ));
    }
    
    public function actionUpdateList($id) {
        $list = Reports::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new X2Leads;
        $comparisonList = Reports::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = ReportsCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['Reports'], $_POST['Reports']['attribute'], $_POST['Reports']['comparison'], $_POST['Reports']['value'])) {

                $attributes = &$_POST['Reports']['attribute'];
                $comparisons = &$_POST['Reports']['comparison'];
                $values = &$_POST['Reports']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['Reports'];
                    $list->modelName = 'X2Leads';
                    $list->lastUpdated = time();

                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['Reports'])) {
                $list->attributes = $_POST['Reports'];
                $list->modelName = 'X2Leads';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new ReportsCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        } else {
            if ($list->type = 'dynamic') {
                foreach ($criteriaModels as $criM) {
                    if (isset($fields[$criM->attribute])) {
                        if ($fields[$criM->attribute]->type == 'link') {
                            $criM->value = implode(',', array_map(function($c) {
                                        list($name, $id) = Fields::nameAndId($c);
                                        return $name;
                                    }, explode(',', $criM->value)
                                    )
                            );
                        }
                    }
                }
            }
        }

        $this->render('updateList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('contacts', 'Dynamic'),
                'static' => Yii::t('contacts', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }
    
    public function actionCreateList($ajax = false) {
        $list = new X2List;
        $list->modelName = 'Reports';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Reports;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Reports';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Reports';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/reports/reports/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new X2ListCriterion;
            $default->value = '';
            $default->attribute = '';
            $default->comparison = 'contains';
            $criteriaModels[] = $default;
        }

        if ($ajax) {
            $html = $this->renderPartial('createList', array(
                'model' => $list,
                'criteriaModels' => $criteriaModels,
                'users' => User::getNames(),
                // 'attributeList'=>$attributeList,
                'comparisonList' => $comparisonList,
                'listTypes' => array(
                    'dynamic' => Yii::t('contacts', 'Dynamic'),
                    'static' => Yii::t('contacts', 'Static')
                ),
                'itemModel' => $contactModel,
                    ), false);
            echo $this->processOutput($html);
            return;
        }

        $this->render('createList', array(
            'model' => $list,
            'criteriaModels' => $criteriaModels,
            'users' => User::getNames(),
            // 'attributeList'=>$attributeList,
            'comparisonList' => $comparisonList,
            'listTypes' => array(
                'dynamic' => Yii::t('contacts', 'Dynamic'),
                'static' => Yii::t('contacts', 'Static')
            ),
            'itemModel' => $contactModel,
        ));
    }
    
    
    public function actionDeleteList() {

        $id = isset($_GET['id']) ? $_GET['id'] : 'all';

        if (is_numeric($id))
            $list = X2Model::model('Reports')->findByPk($id);
        if (isset($list)) {

            // check permissions
            if ($this->checkPermissions($list, 'edit'))
                $list->delete();
            else
                throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));
        }
        $this->redirect(array('/contacts/contacts/lists'));
    }
        
    /**
     * Save report settings
     * @param string $type
     */
    public function actionSaveReport () {
        if (!isset ($_POST['Reports'])) {
            throw new CHttpException(400,
                Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
        }

        $report = new Reports;
        $report->attributes = $_POST['Reports'];
        // AuxLib::debugLogR($_POST);
        $report->version = Yii::app()->params->version;
        $report->createdBy = Yii::app()->user->getName ();
        if ($report->save ()) {
            $this->redirect (Yii::app()->createUrl('/reports/', 
                array(
                    'id'=>$report->id
                    )
            ));
        }
        throw new CHttpException(500, Yii::t('reports', 'Report settings could not be saved.'));
    }

    /**
     * View an index of saved reports
     */
    public function actionChartDashboard() {
        $this->insertMenu(true);
        $this->render('chartDashboard');
    }

    /**
     * View an index of saved reports
     */
    public function actionSavedReports(){
        $model = new Reports ('search');
        $dataProvider = $model->search ();
        $this->render ('savedReports',array(
            'dataProvider'=>$dataProvider,
            'model' => $model,
        ));
    }

    public function actionExternalReport ($report=null) {
        $formModel = new ExternalReportFormModel;
        $reportPath = null;
        if (isset ($_GET['ExternalReportFormModel'])) {
            $formModel->setAttributes ($_GET['ExternalReportFormModel']);
            if ($formModel->validate ())
                $reportPath = $formModel->reportPath;
        }

        $type = $formModel->getReportType ();
        $this->render ('externalReport',array(
            'report' => $report,
            'reportPath' => $reportPath,
            'type' => $type,
            'formModel' => $formModel,
        ));
    }

    public function actionGridReport ($report=null) {
        // grid report updates are made as POST requests to avoid issues with GET parameter length
        // restrictions. Data providers, grid view, and filters behavior expect GET superglobal
        // to be populated
        if (Yii::app()->request->getRequestType () === 'POST') {
            $_GET = $_POST;
        }
        $formModel = new GridReportFormModel;
        if (isset ($_GET['GridReportFormModel'])) {
            $formModel->setAttributes ($_GET['GridReportFormModel'], false);
            if ($formModel->validate ()) {
                if (isset ($_GET['generate'])) {
                    $this->generate ('X2GridReport', $formModel->getReportAttributes ());
                    Yii::app()->end ();
                }
            }
        }

        $primaryModelType = $formModel->primaryModelType;
        $fieldOptions = $primaryModelType::model ()->getFieldsForDropdown (true, false);

        $cellDataFieldOptions = $primaryModelType::model ()->getFieldsForDropdown (
            true, false, function ($field) { 
                return in_array ($field->type, array ('int', 'currency', 'percentage')); 
            });
        $type = $formModel->getReportType ();
        $this->renderReport ($type, array (
            'type' => $type,
            'formModel' => $formModel,
            'report' => $report,
            'data' => array ( 
                'fieldOptions' => $fieldOptions,
                'cellDataFieldOptions' => $cellDataFieldOptions,
            ),
        ), $formModel->refreshForm);
    }

    public function actionRowsAndColumnsReport ($report=null) {
        $formModel = new RowsAndColumnsReportFormModel;
        if (isset ($_GET['RowsAndColumnsReportFormModel'])) {
            $formModel->setAttributes ($_GET['RowsAndColumnsReportFormModel']);
            if ($formModel->validate ()) {
                if (isset ($_GET['generate'])) {
                    $this->generate ('X2RowsAndColumnsReport', $formModel->getReportAttributes ());
                    Yii::app()->end ();
                }
            }
        }

        $type = $formModel->getReportType ();
        $this->renderReport ($type, array (
            'type' => $type,
            'formModel' => $formModel,
            'report' => $report,
        ), $formModel->refreshForm);

    }

    protected function generate ($className,$properties=array()) {
        if ((!isset ($_GET['ajax']) || $_GET['ajax'] !== 'generated-report') &&
            (!isset ($properties['print']) || !$properties['print']) &&
            (!isset ($properties['email']) || !$properties['email'])) {
            echo CJSON::encode (array (
                'status' => 'success',
                'report' => parent::widget ($className, $properties, true),
            )) ;
        } else {
            echo parent::widget ($className, $properties, true);
        }
    }

    public function actionSummationReport ($report=null) {
        $formModel = new SummationReportFormModel;
        if (isset ($_GET['SummationReportFormModel'])) {
            $formModel->setAttributes ($_GET['SummationReportFormModel']);
            if ($formModel->validate ()) {
                if (isset ($_GET['generate'])) {
                    $this->generate ('X2SummationReport', $formModel->getReportAttributes ());
                    Yii::app()->end ();
                }
            } 
        }

        $type = $formModel->getReportType ();
        $this->renderReport ($type, array (
            'type' => $type,
            'formModel' => $formModel,
            'report' => $report,
        ), $formModel->refreshForm);
    }
    
    

    /**
     * Handles both report page rendering and report form refresh 
     */
    public function renderReport ($type, $data, $refreshForm = false) {
        if (isset ($_GET['generate'])) { 
            $refreshForm = true;
            ob_clean (); 
            ob_start ();
        }
        if ($refreshForm) {
            $this->renderPartial (
                $type.'Report', 
                array_merge (
                    $data, isset ($data['data']) ? $data['data'] : array ()), false, true);
        } else {
            $this->render ('report', $data);
        }
        if (isset ($_GET['generate'])) { 
            $form = ob_get_clean (); 
            echo CJSON::encode (array (
                'status' => 'failure',
                'form' => $form
            ));
        }
    }

    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
    }

    /***********************************************************************
    * Charts
    ***********************************************************************/

    /**
     * Views the report that a chart belongs to 
     * @param  integer $id the ID of the chart
     */
    public function actionViewChart ($id) {
        $chart = X2Model::model('Charts')->findByPk($id);
        $reportId = $chart->report->id;
        $this->redirect(Yii::app()->createUrl('reports', array('id' => $reportId)));
    }


    /**
     * Clones a chart to the same layout
     * @param string $widgetClass name of widget class to clone
     * @param string $widgetUID unique id of widget to recieve settings from
     * @param string modelName Name of model to fetch settings from. As of now, either reports or 
     * profile
     * @param int    modelID   Primary key of model to retrieve chart settings from
     * @return json  (echo) echos out the widget cloned
     */
    public function actionCloneChart (
        $widgetClass, $widgetUID,  $settingsModelName, $settingsModelId, $widgetType) {

        if (!$settingsModelName) {
            $profile = Yii::app()->params->profile;
        } else {
            $profile = X2Model::model($settingsModelName)->findByPk($settingsModelId);
        }

        $settings = $widgetClass::getJSONProperties (
            $profile, $widgetType, $widgetUID);

        echo $this->createChartWidget($widgetClass, $settings, $profile, $widgetType);

    }

    /**
     * Adds a widget to the current users dashboard
     * @param string $widgetClass name of widget class to call a static method upon
     * @param string $widgetUID unique id of widget to recieve settings from
     * @param string settingsModelName Name of model to fetch settings from. As of now, either 
     *  reports or profile
     * @param int modelID   Primary key of model to retrieve chart settings from
     * @return json  (echo) echos out a json formatted data object
     */
    public function actionAddToDashboard (
        $widgetClass, $widgetUID,  $settingsModelName, $settingsModelId, $widgetType='data',
        $destination) {

        $profile = Yii::app()->params->profile;
        $report = X2Model::model($settingsModelName)->findByPk($settingsModelId);

        $settings = $widgetClass::getJSONProperties ($report, $widgetType, $widgetUID);

        echo $this->createChartWidget($widgetClass, $settings, $profile, $destination);
    }



    /** 
     * Ajax Call to create a new entry in the charts table
     * @param array attributes to be set to the new chart object
     * attributes require several important 
     * entries for the chart table entries such as
     *      reportId
     *      type
     */
    public function actionCreateChart($attributes) {
        $attributes = CJSON::decode($attributes);
        $chart = new Charts;
        $chart->settings = $attributes;

        if ($chart->save()) {
            $settings = array(
                'label' => $chart->report->name,
                'chartId' => $chart->id
            );

            // Since the chart is always created on the reports page, 
            // It only creates a widget with an entry into the report layout
            $widgetClass = $chart->getWidgetName();
            $profile = $chart->report;

            $contents = $this->createChartWidget($widgetClass, $settings, $profile);
            echo $contents;
            return;
        }

        if ($chart->hasErrors()) {
            echo CJSON::encode($chart->errors);
            return;
        }

        // Print and error that is in the correct nesting level to display an error in an error box
        echo CJSON::encode(
            array(
                array(
                    Yii::t('charts', 'There was an error saving the chart')
                    )
                )
            );

    }

    /**
     * Fetches the data for ajax requests from widgets
     * @param string $widgetClass name of widget class to call a static method upon
     * @param string $widgetUID unique id of widget to recieve settings from
     * @param string settingsModelName Name of model to fetch settings from. As of now, either 
     * reports or profile
     * @param int    modelID   Primary key of model to retrieve chart settings from
     * @return json  (echo) echos out a json formatted data object
     */
    public function actionFetchData(
        $widgetClass, $widgetUID, $settingsModelName=null, $settingsModelId=null,
        $widgetType='data') {

        if (!$settingsModelName) {
            $profile = Yii::app()->params->profile;
        } else {
            $profile = X2Model::model($settingsModelName)->findByPk($settingsModelId);
        }

        // Data is commonly refetched when new settings are chosen
        if (isset($_GET['settings'])) {
            foreach($_GET['settings'] as $key => $value) {
                $widgetClass::setJSONProperty (
                    $profile, $key, $value, $widgetType, $widgetUID);
            }
        }
            
        $settings = $widgetClass::getJSONProperties (
                    $profile, $widgetType, $widgetUID);
        $data = $widgetClass::getChartData($settings);
        echo CJSON::encode($data);

    }

    /**
     * Creates a chart widget in the dataWidgetLayout field 
     * @param string $widgetClass class of ChartWidget to instantiate
     * @param array $widgetSettings array of chart settings to create
     * @param model $profile model that contains the layout
     */
    private function createChartWidget (
        $widgetClass, $widgetSettings, $profile=null, $widgetLayoutName='data') {

        if(!$profile) {
            $profile = Yii::app()->params->profile;
        }

        list ($success, $uid) = SortableWidget::createSortableWidget (
            $profile, $widgetClass, $widgetLayoutName, $widgetSettings);

        if ($success) {
            return $widgetClass::getWidgetContents(
                $this, $profile, $widgetLayoutName, $uid);
        } else {
            return 'failure';
        }
    }

    /**
     * General Method for calling a static method on a chart.
     * Right now, only used to call RenderInline report
     * @param  int $chartId Id of the chart to fall function
     * @param  array params An array of params to be sent to the chart
     */
    public function actionCallChartFunction($chartId, $fnName) {
        $chart = Charts::model()->findByPk($chartId);
        $widgetName = $chart->getWidgetName();

        $params = array();
        if (isset($_GET['params'])) {
            $params = $_GET['params'];
        }

        if (method_exists($widgetName, $fnName)) {
            $widgetName::$fnName ($chart, $params);
        }

    }

    /**
     * Prints a report using the print layout
     * @param  int $id Id of the report to print
     */
    public function actionPrintReport() {
        if(isset($_GET['id'])) {
            $report = Reports::model()->findByPk ($_GET['id']);
        } else {
            $type = $_GET['type'];
            $formModelName = ucfirst($type).'ReportFormModel';

            // Create  Form Model to set default attributes
            $formModel = new $formModelName;
            $formModel->setAttributes ($_GET[$formModelName]);
            $formModel->validate ();

            // Create a report object to leverage the usage
            // of report->getInstance()
            $report = new Reports;
            $report->type = $type;
            $report->settings = CJSON::encode($formModel->attributes);
        }

        $this->render('printChart', array(
            'report' => $report,
        ));
    }

    /**
     * Creates a Print view of a chart using the print layout
     * Uses a POST arranged as so: 
     * $_POST[charts] = array (
     *     'settings' => array( <JSON encoded setting objects for charts>,  ...)
     *     'ids' => array( <Chart ID's in same order,  ...)
     *     )
     * This action will fetch the reports of the charts and only display a
     * report if there is only one
     */
    public function actionPrintChart(){
        Yii::app()->clientScript
            ->registerScriptFile(Yii::app()->baseUrl.'/js/d3/d3.min.js')
            ->registerScriptFile(Yii::app()->baseUrl.'/js/c3/c3.min.js')
            ->registerCssFile(Yii::app()->baseUrl.'/js/c3/c3.css');

        $chartIds = $_POST['ids'];
        $report = null;
        $charts = array();

        foreach($chartIds as $index => $chartId) {
            $chart = Charts::model()->findByPk($chartId);
            $charts[$index]['title'] = $_POST['titles'][$index];
            $charts[$index]['settings'] = $_POST['settings'][$index];

            // If there are multiple reports (printing a mixed dashboard), 
            // set the report to null
            if($report && $report->id != $chart->report->id) {
                $report = null;
                continue;
            }

            // set the report to the chart report
            $report = $chart->report;
        }

        $this->render('printChart', array(
            'charts' => $charts,
            'report' => $report,
        ));
    }



    /***********************************************************************
    * Reports 1.0 
    ***********************************************************************/

    /**
     * Legacy report helper action.
     * Get a list of possible values for a given field from the Contacts table and
     * a search term.
     * This function selects one field and groups by that field so that all that's
     * returned is an array of every individual value from that field which also
     * matches the search term.
     */
    public function actionGetOptions() {
        if (isset($_GET['field'])) {
            echo "";
        } elseif (isset($_GET['fieldType'])) {
            $field = $_GET['fieldType'];
            $term = $_GET['term'] . "%";
            $options = Yii::app()->db->createCommand()
                    ->select($field)
                    ->from('x2_contacts')
                    ->group($field)
                    ->where($field . ' LIKE :term', array('term' => $term))
                    ->queryAll();
            $data = array();
            foreach ($options as $item) {
                if (empty($item[$field])) {

                } else {
                    $data[] = $item[$field];
                }
            }
            echo CJSON::encode($data);
        }
    }

    /**
     * Generate a report on User activity. This will provide information on the
     * last login time, number of records updated, and the number of actions
     * due & completed in a given time frame.
     */
    public function actionActivityReport(){
        $dataProvider = null;
        $trueData=array();
        $dateRange = X2DateUtil::getDateRange();
		if(isset($_GET['range'])){ // Set date range, like all the other reports.

            $attributeConditions = '';

            $attributeParams = array(
                ':date1'=> $dateRange['start'],
                ':date2'=> $dateRange['end'],

            );
            $users=User::getNames();
            $data=array();
            foreach($users as $user=>$name){
                // Generate our user data, we need to format $data in a way for our
                // CArrayDataProvider
                $data[$user]=array(
                    'id'=>array_search($user,array_keys($users)),
                    'username'=>$user,
                    'fullName'=>$name,
                );
            }
            /*
             * All of the queries below use grouping to fill each piece of returned
             * data with information about a given user and some value associated
             * with that particular user.
             */
            $dueActionsData=Yii::app()->db->createCommand()
                    ->select('assignedTo, COUNT(*)')
                    ->from('x2_actions')
                    ->group('assignedTo')
                    ->where('dueDate BETWEEN :date1 AND :date2',$attributeParams)
                    ->queryAll();
            $completeActionsData=Yii::app()->db->createCommand()
                    ->select('assignedTo, COUNT(*)')
                    ->from('x2_actions')
                    ->group('assignedTo')
                    ->where('completeDate BETWEEN :date1 AND :date2',$attributeParams)
                    ->queryAll();
            $recordsUpdated=Yii::app()->db->createCommand()
                    ->select('changedBy, COUNT(*)')
                    ->from('x2_changelog')
                    ->group('changedBy')
                    ->where('timestamp BETWEEN :date1 AND :date2',$attributeParams)
                    ->queryAll();
            $login=Yii::app()->db->createCommand()
                    ->select('username, login')
                    ->from('x2_users')
                    ->where('status!=0')
                    ->group('username')
                    ->queryAll();
            /*
             * The next set of loops goes through all the selected data and adds
             * to the $data array for each type of information.
             */
            foreach($login as $index=>$arr){
                $data[$arr['username']]['login']=$arr['login'];
            }
            foreach($data as $user=>&$array){
                if(!isset($array['login']))
                    $array['login']=0;
            }
            foreach($recordsUpdated as $index=>$arr){
                $data[$arr['changedBy']]['records']=$arr['COUNT(*)'];
            }
            foreach($data as $user=>&$array){
                if(!isset($array['records']))
                    $array['records']=0;
            }
            foreach($completeActionsData as $index=>$arr){
                $data[$arr['assignedTo']]['completed']=$arr['COUNT(*)'];
            }
            foreach($data as $user=>&$array){
                if(!isset($array['completed']))
                    $array['completed']=0;
            }
            foreach($dueActionsData as $index=>$arr){
                $data[$arr['assignedTo']]['due']=$arr['COUNT(*)'];
            }
            foreach($data as $user=>&$array){
                if(!isset($array['due']))
                    $array['due']=0;
            }
            // Remove the API user because he isn't real
            unset($data['api']);
            $trueData=array();
            // Finally, remove any data for users who don't exist in the Users table.
            // This could probably be refactored by using X2Model::getAssignmentOptions(false,false)
            // above instead of User::getNames, but I'm not entirely sure.
            foreach($data as $key=>$arr){
                $userRecord=User::model()->findByAttributes(array('username'=>$key));
                if(isset($userRecord) && isset($arr['fullName'])){
                    $trueData[]=$arr;
                }
            }
        }

        $dataProvider = new CArrayDataProvider($trueData,array(
            'id'=>'activity-dataprovider',
            'pagination'=>array(
                'pageSize'=>Profile::getResultsPerPage(),
            ),
            'sort'=>array(
                'attributes'=>array(
                    'fullName',
                ),
            ),
        ));
		$this->render('activityReport', array(
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange,
		));
    }

    /**
     * Displays data of the number of Contacts in each workflow stage for a given
     * workflow grouped by user for a date range.
     */
    public function actionLeadPerformance() {
        //print_r($_GET);exit;
        // Grab the additional filter field, or set it to leadSource if none provided.
        $fieldVar = isset($_GET['field']) ? $_GET['field'] : "leadSource";
        $dateRange = X2DateUtil::getDateRange();
        $model = new Contacts('search');
        // Any field filter data will be in the GET superglobal.
        if (isset($_GET['Contacts']))
            $model->attributes = $_GET['Contacts'];
        $input = $model->$fieldVar;
        // Ifi t's a link type field like Company, we need to actually look up and grab the ID instead of the human entered value.
        if (isset($_GET['Contacts']['company_id'], $_GET['Contacts']['company']) && !empty($_GET['Contacts']['company'])) { // check the ID, if provided
            $linkId = $_GET['Contacts']['company_id'];
            if (!empty($linkId) && X2Model::model('Accounts')->countByAttributes(array('id' => $linkId))) // if the link model actually exists,
                $model->company = $linkId;                 // then use the ID as the field value
        }
        if (!empty($_GET['Contacts']['company']) && !ctype_digit((string)$_GET['Contacts']['company'])) { // if the field is sitll text, try to find the ID based on the name
            $linkModel = X2Model::model('Accounts')->findByAttributes(array('name' => $_GET['Contacts']['company']));
            if (isset($linkModel))
                $model->company = $linkModel->id;
        }

        // No filter conditions by default
        $attributeConditions = '';
        // No params either
        $attributeParams = array();

        // Loop through all provided filter values and add the condition + the bind parameter
        foreach ($model->attributes as $key => $value) {
            if (!empty($value)) {
                $attributeConditions .= ' AND x2_contacts.' . $key . '=:' . $key;
                $attributeParams[':' . $key] = $value;
            }
        }
        // Strict mode means that in addition to the workflow complete date being
        // in the date range, the create date must be in the range as well
        if ($dateRange['strict'])
            $attributeConditions .= ' AND createDate BETWEEN ' . $dateRange['start'] . ' AND ' . $dateRange['end'];

        // Assume workflow #1
        $workflow = 1;

        if (isset($_GET['workflow'])) {
            // If we passed a workflow in the params and it's a valid int, use it
            if (ctype_digit($_GET['workflow']))
                $workflow = $_GET['workflow'];


            $workflowStages = WorkflowStage::model()->findAllByAttributes(array('workflowId' => $workflow), array('order' => 'stageNumber ASC'));
            // Prepare our stage ID values in an array
            $stageIds = array();
            foreach ($workflowStages as $stage) {
                $stageIds[$stage->name] = $stage->id;
            }
            // This could probably be refactored to use newer methods of getting usernames
            $users = User::getNames();

            $assignedTo = array_keys($users);

            // Set our blank initial data and totals, these are stored separately.
            $data = array();
            $totals = array(
                'id' => '',
                'name' => Yii::t('reports', 'Total'),
                'leads' => 0,
            );
            // Loop through all assigned users.
            for ($i = 0, $size = sizeof($assignedTo); $i < $size; $i++) {
                // Start setting the data for this row, we need the assignment and full name
                $data[$i]['id'] = $assignedTo[$i];
                $data[$i]['name'] = $users[$assignedTo[$i]];

                // If the user is "Anyone" we have to add an additional check for "IS NULL" or ""
                if ($data[$i]['id'] == 'Anyone') {
                    $assignmentCheck = '(x2_contacts.assignedTo IS NULL OR x2_contacts.assignedTo="" OR x2_contacts.assignedTo="Anyone")';
                    $data[$i]['id'] = '';
                } else // Otherwise, the assigned to is just the value
                    $assignmentCheck = 'x2_contacts.assignedTo="' . $data[$i]['id'] . '"';

                // The number of leads is the number of contacts assigned to the user with the conditions
                $data[$i]['leads'] = Yii::app()->db->createCommand()
                        ->select('COUNT(*)')->from('x2_contacts')
                        ->where('assignedTo="' . $assignedTo[$i] . ($dateRange['strict'] ? '"' : '" AND createDate BETWEEN ' . $dateRange['start'] . ' AND ' . $dateRange['end']) . $attributeConditions, $attributeParams)
                        ->queryScalar();
                // Also need to increment totals for this.
                $totals['leads'] += $data[$i]['leads'];
                $str = "";
                // Each stage present needs to be summed to get a count for that stage
                foreach ($stageIds as $name => $id) {
                    $str.=" SUM(IF(x2_actions.stageNumber='$id',1,0)) AS `$name`, ";
                }
                // Select all data for a particular row of workflow data
                $row = Yii::app()->db->createCommand()
                        ->select($str . ' x2_contacts.assignedTo AS assignedTo')
                        ->from('x2_contacts')
                        ->join('x2_actions', 'x2_actions.associationId=x2_contacts.id AND x2_actions.associationType="contacts"')
                        ->where('x2_actions.type="workflow" AND x2_actions.workflowId=' . $workflow . ' AND x2_actions.completeDate BETWEEN ' . $dateRange['start'] . ' AND ' . $dateRange['end'] . ' AND ' . $assignmentCheck
                                . ' AND (SELECT COUNT(*) FROM x2_contacts WHERE x2_contacts.id=x2_actions.associationId ' . $attributeConditions . ') > 0', $attributeParams)
                        ->queryRow();
                foreach ($stageIds as $name => $id) {
                    // Loop through our stages and set the data for that stage to the correct value
                    $data[$i][$name] = isset($row[$name]) ? $row[$name] : 0;
                    $totals[$name] = isset($totals[$name]) ? $totals[$name] + $data[$i][$name] : $data[$i][$name];
                }

                // If the whole row adds up to 0, remove it because no data is present
                if (array_sum($data[$i]) == 0)
                    unset($data[$i]);
            }
            // After we've finished all our user data, add our totals to the end of the data
            $data[] = $totals;

            // die(var_dump($data));
            // $sql = 'SELECT COUNT(*) as `count`,assignedTo, stageNumber FROM `x2_actions` WHERE type="workflow" AND workflowId=1 GROUP BY assignedTo, stageNumber';
            $dataProvider = new CArrayDataProvider($data, array(
                        // 'totalItemCount'=>$count,
                        // 'sort'=>'assignedTo ASC',
                        'sort' => array(
                        // 'defaultOrder'=>'name ASC',
                        // 'attributes'=>array(
                        // 'id', 'username', 'email',
                        // ),
                        ),
                        'pagination' => array(
                            'pageSize' => Yii::app()->params->profile->resultsPerPage,
                        ),
                    ));
        } else {
            $dataProvider = null;
            $stageIds = array();
        }
        $model->unsetAllFilters();
        $this->render('leadPerformance', array(
            'model' => $model,
            'workflow' => $workflow,
            'dataProvider' => $dataProvider,
            'dateRange' => $dateRange,
            'stageIds' => $stageIds,
            'fieldName' => $fieldVar,
            'input' => $input,
        ));
    }

    /**
     * Generates a list of contacts based on a particular workflow / stage
     * combination for a specified date range.
     */
	public function actionWorkflow() {
		$dataProvider = null;
        $dateRange = X2DateUtil::getDateRange();

		$model = new Contacts('search');
		if(isset($_GET['Contacts']))
			$model->attributes = $_GET['Contacts'];
		// $input=$model->$fieldVar;

		if(isset($_GET['Contacts']['company_id'],$_GET['Contacts']['company']) && !empty($_GET['Contacts']['company'])) {	// check the ID, if provided
			$linkId = $_GET['Contacts']['company_id'];
			if(!empty($linkId) && X2Model::model('Accounts')->countByAttributes(array('id'=>$linkId)))	// if the link model actually exists,
				$model->company = $linkId;																	// then use the ID as the field value
		}
		if(!empty($_GET['Contacts']['company']) && !ctype_digit($_GET['Contacts']['company'])) {	// if the field is sitll text, try to find the ID based on the name
			$linkModel = X2Model::model('Accounts')->findByAttributes(array('name'=>$_GET['Contacts']['company']));
			if(isset($linkModel))
				$model->company = $linkModel->id;
		}


		$attributeConditions = '';
        if($dateRange['strict'])
			$attributeConditions = 't.createDate BETWEEN :date1 AND :date2';
		$attributeParams = array(
			':date1'=> $dateRange['start'],
			':date2'=> $dateRange['end'],

		);
        // If the user is constrained by permissions, only let them see data they have access to
        if(!Yii::app()->user->checkAccess('ReportsAdminAccess')){
            $attributeParams[':user']=Yii::app()->user->name;
        }
        // Allow for any attribute conditions provided (I don't think this code ever executes as of right now)
		foreach($model->attributes as $key=>$value) {
			if(!empty($value)) {
				$attributeConditions .= ' AND x2_contacts.'.$key.'=:'.$key;
				$attributeParams[':'.$key] = $value;
			}
		}

		$workflow = null;
		$stage = '';
		if(isset($_GET['stage']) && ctype_digit($_GET['stage']))
			$stage = $_GET['stage'];
		if(isset($_GET['workflow']) && ctype_digit($_GET['workflow']))
			$workflow = $_GET['workflow'];

		if(isset($workflow,$stage)) {

			$attributeParams[':workflowId'] = $workflow;
            // Apply permissions filters
            if(!Yii::app()->user->checkAccess('ReportsAdminAccess')){
                $condition = 't.visibility="1" OR t.assignedTo="Anyone"  OR t.assignedTo=:user';
                /* x2temp */
                $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
                if(!empty($groupLinks))
                    $condition .= ' OR t.assignedTo IN ('.implode(',', $groupLinks).')';

                $condition .= 'OR (t.visibility=2 AND t.assignedTo IN
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
            }else{
                $condition = "";
            }
			$attributeConditions = $condition.$attributeConditions;
            // Need to join to action table because of how workflow events are stored
			$join = 'JOIN x2_actions ON x2_actions.associationId=t.id
				AND x2_actions.associationType="contacts"
				AND x2_actions.type="workflow"
				AND x2_actions.workflowId=:workflowId
				AND x2_actions.complete="Yes"
				AND x2_actions.completeDate BETWEEN :date1 AND :date2';

			if(!empty($stage)) {
				$join .= ' AND x2_actions.stageNumber=:stage';
				$attributeParams[':stage'] = $stage;
			}
			$criteria = new CDbCriteria(array(
				'condition'=>$attributeConditions,
				'join'=>$join,
				'params'=>$attributeParams,
				'distinct'=>true,
			));


			// $dataProvider = new CArrayDataProvider($data,array(
			$dataProvider = new CActiveDataProvider('Contacts',array(
				'criteria'=>$criteria,

				// 'totalItemCount'=>$count,
				// 'sort'=>'assignedTo ASC',
				'sort'=>array(
					// 'defaultOrder'=>'name ASC',
					// 'attributes'=>array(
						 // 'id', 'username', 'email',
					// ),
				),
				'pagination'=>array(
					'pageSize'=>Yii::app()->params->profile->resultsPerPage,
				),
			));
		} else {
			// $dataProvider = null;
			// $stageIds=array();
		}
		$model->unsetAllFilters();

		$workflowOptions = array();
		$stageOptions = array(''=>Yii::t('workflow','Any stage'));

		$query = Yii::app()->db->createCommand()
		->select('id,name')
		->from('x2_workflows')->query();
		while(($row = $query->read()) !== false)
			$workflowOptions[$row['id']] = $row['name'];

		// use the first workflow if none was selected
		if(!isset($workflow) && count($workflowOptions)) {
			reset($workflowOptions);
			$workflow = key($workflowOptions);
		}

		if(isset($workflow)) {
			$query = Yii::app()->db->createCommand()
				->select('id,name')
				->from('x2_workflow_stages')
				->where('workflowId=:id',array(':id'=>$workflow))
				->order('stageNumber ASC')
				->queryAll();

			for($i=0; $i<$size=count($query); $i++)
				$stageOptions[$query[$i]['id']] = $query[$i]['name'];
		}

		$this->render('workflow', array(
			'model'=>$model,
			'workflow'=>$workflow,
			'stage'=>$stage,
			'stageOptions'=>$stageOptions,
			'workflowOptions'=>$workflowOptions,
			'dataProvider'=>$dataProvider,
			'dateRange'=>$dateRange,
			// 'stageIds'=>$stageIds,
			// 'fieldName'=>$fieldVar,
			// 'input'=>$input,
		));
	}


    /**
     * A function to save a report so that it can be re-accessed in the future.
     */
//    public function actionSaveReport(){
//        $report=new Reports;
//        // All of the report parameters are pased as GET variables so we can just
//        // grab them all and use them.
//        $type=$_GET['type'];
//        $start=$_GET['start'];
//        $end=$_GET['end'];
//        $range=$_GET['range'];
//
//        $report->type=$type;
//        if($type=='grid'){
//            // Grid reports have extra additional paramters to keep track of.
//            $field1=$_GET['field1'];
//            $field2=$_GET['field2'];
//            $cellType=$_GET['cellType'];
//            $cellData=$_GET['cellData'];
//            $zero=$_GET['zero'];
//
//            $report->cellType=$cellType;
//            $report->cellData=$cellData;
//            $report->field1=$field1;
//            $report->field2=$field2;
//            $report->zero=$zero;
//            // Save charts too
//          if(isset($_GET['chartValue']) && isset($_GET['chartType'])) {
//              $rowPie = array();
//              for($i=0; $i<sizeof($_GET['chartValue']); $i++) {
//                  $rowPie[] = $_GET['chartValue'][$i];
//                  $rowPie[] = $_GET['chartType'][$i];
//              }
//              $report->rowPie = json_encode($rowPie);
//          }
//            $report->parameters=$_GET['parameters'];
//        }elseif($type=='deal'){
//            $report->parameters=$_GET['parameters'];
//        }
//        // A lot of this data could probably be condensed into JSON
//        $report->start=$start;
//        $report->end=$end;
//        $report->dateRange=$range;
//
//        $report->createDate=time();
//        $report->createdBy=Yii::app()->user->getName();
//
//        if($report->save()){
//
//        }
//        $this->redirect('savedReports');
//
//    }


}
