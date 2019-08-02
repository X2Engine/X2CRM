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




/**
 * Track service/support cases among contacts.
 *
 * Every Service Case must be associated with a contact. It's possible to
 * create a service case from a contacts view via ajax by clicking the
 * "Create Case" button. (the new case is associated with the contact).
 *
 * @package application.modules.services.controllers
 */
class ServicesController extends x2base {

    public $modelClass = 'Services';
    public $serviceCaseStatuses = null;

    public function accessRules(){
        return array(
            array('allow',
                'actions' => array('getItems', 'webForm'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'search', 'saveChanges', 'delete', 'inlineEmail', 'createWebForm', 'statusFilter'),
                'users' => array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions' => array('admin', 'testScalability'),
                'users' => array('admin'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    public function actions(){
        return array_merge(parent::actions(), array(
            'webForm' => array(
                'class' => 'WebFormAction',
            ),
            'createWebForm' => array(
                'class' => 'CreateWebFormAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
        ));
    }

    public function behaviors(){
        return array_merge(parent::behaviors(), array(
            'MobileControllerBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileControllerBehavior'
            ),
            'MobileActionHistoryBehavior' => array(
                'class' => 
                    'application.modules.mobile.components.behaviors.MobileActionHistoryBehavior'
            ),
            'ServiceRoutingBehavior' => array(
                'class' => 'ServiceRoutingBehavior'
            ),
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
            'WebFormBehavior' => array(
                'class' => 'WebFormBehavior'
            ),
        ));
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $model = $this->loadModel($id);
        if (!$this->checkPermissions($model, 'view')) $this->denied ();

        // add service case to user's recent item list
        User::addRecentItem('s', $id, Yii::app()->user->getId()); 

        parent::view($model, 'services');
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    /* public function create($model,$oldAttributes, $api){
      //		$model->annualRevenue = Formatter::parseCurrency($model->annualRevenue,false);
      $model->createDate=time();
      $model->lastUpdated=time();
      $model->updatedBy = Yii::app()->user->name;
      if($api==0) {
      parent::create($model,$oldAttributes,'1');
      if( !$model->isNewRecord ) {
      $model->name = $model->id;
      $model->save();
      if($model->escalatedTo != '') {
      $event=new Events;
      $event->type='case_escalated';
      $event->user=Yii::app()->user->getName();
      $event->associationType=$this->modelClass;
      $event->associationId=$model->id;
      if($event->save()){
      $notif = new Notification;
      $notif->user = $model->escalatedTo;
      $notif->createDate = time();
      $notif->createdBy = Yii::app()->user->name;
      $notif->type = 'escalateCase';
      $notif->modelType = $this->modelClass;
      $notif->modelId = $model->id;
      $notif->save();
      }
      }

      $this->redirect(array('view', 'id' => $model->id));
      }
      } else {
      return parent::create($model,$oldAttributes,$api);
      }
      } */

    /**
     * Create a new Service Case
     *
     * This action can be called normally (by clicking the Create button in Service module)
     * or it can be called via ajax by clicking the "Create Case" button in a contact view.
     *
     */
    public function actionCreate(){
        $model = new Services;
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id] = $group->name;
        }

        if(isset($_POST['Services'])){
            $temp = $model->attributes;
            foreach($_POST['Services'] as $name => &$value){
                if($value == $model->getAttributeLabel($name))
                    $value = '';
            }
            $model->setX2Fields($_POST['Services']);

            if(isset($_POST['x2ajax'])){ // we're creating a case with "Create Case" button in contacts view
                /* every model needs a name field to work with X2GridView and a few other places, 
                   for service cases the id of the case is the name */
                $model->name = $model->id; 
                $ajaxErrors = $this->quickCreate ($model);

            }elseif($model->save()){
                $this->redirect(array('view', 'id' => $model->id));
                // $this->create($model,$temp, '0');
            }
        }

        // we're creating a case with "Create Case" button in contacts view
        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        }else{
            $this->render('create', array(// normal (non-ajax) create
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    /* public function update($model, $oldAttributes,$api){

      $ret = parent::update($model,$oldAttributes,'1');

      if($model->escalatedTo != '' && $model->escalatedTo != $oldAttributes['escalatedTo']) {
      $event=new Events;
      $event->type='case_escalated';
      $event->user=Yii::app()->user->getName();
      $event->associationType=$this->modelClass;
      $event->associationId=$model->id;
      if($event->save()){
      $notif = new Notification;
      $notif->user = $model->escalatedTo;
      $notif->createDate = time();
      $notif->createdBy = Yii::app()->user->name;
      $notif->type = 'escalateCase';
      $notif->modelType = $this->modelClass;
      $notif->modelId = $model->id;
      $notif->save();
      }
      }

      if($api==0)
      $this->redirect(array('view', 'id' => $model->id));
      else
      return $ret;
      } */

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id] = $group->name;
        }

        if(isset($_POST['Services'])){
            $temp = $model->attributes;
            foreach($_POST['Services'] as $name => &$value){
                if($value == $model->getAttributeLabel($name))
                    $value = null;
            }
            $model->setX2Fields($_POST['Services']);

            if($model->contactId != '' && !is_numeric($model->contactId)) // make sure an existing contact is associated with this case, otherwise don't create it
                $model->addError('contactId', Yii::t('services', 'Contact does not exist'));

            // $this->update($model,$temp,'0');
            if($model->save()){
                $this->redirect(array('view', 'id' => $model->id));
            }
        }

        $this->render('update', array(
            'model' => $model,
            'users' => $users,
        ));
    }

    public function delete($id){

        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'condition' => 'associationId='.$id.' AND associationType=\'services\'',
                        )));

        $actions = $dataProvider->getData();
        foreach($actions as $action){
            $action->delete();
        }
        $this->cleanUpTags($model);
        $model->delete();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id){
        $model = $this->loadModel($id);
        if(Yii::app()->request->isPostRequest){
            $event = new Events;
            $event->type = 'record_deleted';
            $event->associationType = $this->modelClass;
            $event->associationId = $model->id;
            $event->text = $model->name;
            $event->user = Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'services\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    // Displays all visible Contact Lists
    public function actionLists() {
        $filter = new Services('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Services"');
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

        $contactLists = X2Model::model('Services')->findAll($criteria);

        $totalContacts = X2Model::model('X2Leads')->count();
        $totalMyContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new Services;
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
        $newContacts = new Services;
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
        $myContacts = new Services;
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
        $list = Services::load($id);

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
        $list = Services::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new X2Leads;
        $comparisonList = Services::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = ServicesCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['Services'], $_POST['Services']['attribute'], $_POST['Services']['comparison'], $_POST['Services']['value'])) {

                $attributes = &$_POST['Services']['attribute'];
                $comparisons = &$_POST['Services']['comparison'];
                $values = &$_POST['Services']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['Services'];
                    $list->modelName = 'X2Leads';
                    $list->lastUpdated = time();

                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['Services'])) {
                $list->attributes = $_POST['Services'];
                $list->modelName = 'X2Leads';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new ServicesCriterion;
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
        $list->modelName = 'Services';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Services;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Services';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Services';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/services/services/list', 'id' => $list->id));
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
            $list = X2Model::model('Services')->findByPk($id);
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
     * Lists all models.
     */
    public function actionIndex(){

        $model = new Services('search');
        $this->render('index', array('model' => $model));
    }

    public function actionGetItems($term){
        LinkableBehavior::getItems ($term, 'id', 'id');
    }

    /**
     *  Show or hide a certain status in the gridview
     *
     *  Called through ajax with a status and if that status should be shown or hidden.
     *  Saves the result in the user's profile.
     *
     */
    public function actionStatusFilter(){

        if(isset($_POST['all'])){ // show all the things!!
            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode(array()); // hide none
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        }elseif(isset($_POST['none'])){ // hide all the things!!!!11
            $statuses = array();

            $dropdownId = Yii::app()->db->createCommand() // get the ID of the statuses dropdown via fields table
                    ->select('linkType')
                    ->from('x2_fields')
                    ->where('modelName="Services" AND fieldName="status" AND type="dropdown"')
                    ->queryScalar();
            if($dropdownId !== null)
                $statuses = Dropdowns::getItems($dropdownId); // get the actual statuses

            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode($statuses);
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        } elseif(isset($_POST['checked'])){

            $checked = CJSON::decode($_POST['checked']);
            $status = isset($_POST['status']) ? $_POST['status'] : false;

            // var_dump($checked);
            // var_dump($status);

            $hideStatuses = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
            if($hideStatuses === null || !is_array($hideStatuses))
                $hideStatuses = array();

            // var_dump($checked);
            // var_dump(in_array($status, $hideStatuses));
            if($checked && ($key = array_search($status, $hideStatuses)) !== false){ // if we want to show the status, and it's not being shown
                unset($hideStatuses[$key]); // show status
            }else if(!$checked && !in_array($status, $hideStatuses)){ // if we want to hide the status, and it's not being hidden
                $hideStatuses[] = $status;
            }

            Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode($hideStatuses);
            Yii::app()->params->profile->update(array('hideCasesWithStatus'));
        }
    }

    /**
     * Create a menu for Services
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Services = Modules::displayName();
        $Service = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete', 'email', 'attach', 'quotes',
         *     'createWebForm', 'print', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('services','All Cases'),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('services','Create Case'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('services','Edit Case'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('services','Delete Case'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?')
            ),
            array(
                'name' => 'lists',
                'label' => Yii::t('contacts', 'Lists'),
                'url' => array('lists')
            ),
            array(
                'name'=>'email',
                'label'=>Yii::t('app','Send Email'),
                'url'=>'#',
                'linkOptions'=>array('onclick'=>'toggleEmailForm(); return false;')
            ),
            ModelFileUploader::menuLink(),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', '{quotes}/Invoices', array(
                    '{quotes}' => Modules::displayName(true, "Quotes"),
                )),
                'url' => 'javascript:void(0)',
                'linkOptions' => array('onclick' => 'x2.inlineQuotes.toggle(); return false;')
            ),
            array(
                'name'=>'createWebForm',
                'label'=>Yii::t('services','Create Web Form'),
                'url'=>array('createWebForm')
            ),
            array(
                'name'=>'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'Services',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', '{service} Case', array(
                                '{service}' => $Service,
                            )).': '.(isset($model) ? $model->name : "")
                        ))."');"
                )
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('services', 'Import {services}', array(
                    '{services}' => $Services,
                )),
                'url'=>array('admin/importModels', 'model'=>'Services'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('services', 'Export {services}', array(
                    '{services}' => $Services,
                )),
                'url'=>array('admin/exportModels', 'model'=>'Services'),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
