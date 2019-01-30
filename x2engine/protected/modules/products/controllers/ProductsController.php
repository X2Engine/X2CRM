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
 * @package application.modules.products.controllers 
 */
class ProductsController extends x2base {
    public $modelClass = 'Product';

    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('index', 'view', 'search','getItems'),
                'users'=>array('@'),
            ),
            array('allow', // allow admin user to perform 'admin' and 'delete' actions
                'actions'=>array('admin','testScalability', 'create', 'update', 'delete'),
                'users'=>array('admin'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
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
            'QuickCreateRelationshipBehavior' => array(
                'class' => 'QuickCreateRelationshipBehavior',
            ),
        ));
    }

    public function actionGetItems($term){
        LinkableBehavior::getItems ($term);
    }
    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = $this->loadModel($id);
        if (!$this->checkPermissions($model, 'view')) $this->denied ();

        // add product to user's recent item list
        User::addRecentItem('r', $id, Yii::app()->user->getId()); 

        parent::view($model);
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionCreate() {
        $model=new Product;
        $users=User::getNames();
        if(isset($_POST['Product'])) {
            $model->setX2Fields($_POST['Product']);
            // $model->price = Formatter::parseCurrency($model->price,false);
            $model->createDate=time();

            if(isset($_POST['x2ajax'])){
                $ajaxErrors = $this->quickCreate ($model);
            }else{
                if($model->save()) {
                    $this->redirect(array('view', 'id' => $model->id));
                }
            }
        }

        if(isset($_POST['x2ajax'])){
            $this->renderInlineCreateForm ($model, isset ($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create',array(
                'model'=>$model,
                'users'=>$users,
            ));
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        $users=User::getNames(); 
        $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Product'));
        foreach($fields as $field){
            if($field->type=='link'){
                $fieldName=$field->fieldName;
                $type=ucfirst($field->linkType);
                if(is_numeric($model->$fieldName) && $model->$fieldName!=0){
                    eval("\$lookupModel=$type::model()->findByPk(".$model->$fieldName.");");
                    if(isset($lookupModel))
                        $model->$fieldName=$lookupModel->name;
                }
            }
        }
        if(isset($_POST['Product'])) {
            $temp=$model->attributes;
            $model->setX2Fields($_POST['Product']);
            
            // generate history
            $action = new Actions;
            $action->associationType = 'product';
            $action->associationId = $model->id;
            $action->associationName = $model->name;
            $action->assignedTo = Yii::app()->user->getName();
            $action->completedBy=Yii::app()->user->getName();
            $action->dueDate = time();
            $action->completeDate = time();
            $action->visibility = 1;
            $action->complete='Yes';
        
            $action->actionDescription = "Update: {$model->name}
            Type: {$model->type}
            Price: {$model->price}
            Currency: {$model->currency}
            Inventory: {$model->inventory}";
            $action->save();         
            parent::update($model,$temp,'0');
        }

        $this->render('update',array(
            'model'=>$model,
            'users'=>$users,
        ));
    }
    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id) {
        if(Yii::app()->request->isPostRequest) {    // we only allow deletion via POST request
            $model = $this->loadModel($id);
            
            $model->clearTags();
            $model->delete();
            
            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
        } else {
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
        }
    }

    // Displays all visible Contact Lists
    public function actionLists() {
        $filter = new Product('search');
        $criteria = new CDbCriteria();
        $criteria->addCondition('modelName = "Product"');
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

        $contactLists = X2Model::model('Product')->findAll($criteria);

        $totalContacts = X2Model::model('X2Leads')->count();
        $totalMyContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '"');
        $totalNewContacts = X2Model::model('X2Leads')->count('assignedTo="' . Yii::app()->user->getName() . '" AND createDate >= ' . mktime(0, 0, 0));

        $allContacts = new Product;
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
        $newContacts = new Product;
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
        $myContacts = new Product;
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
        $list = Product::load($id);

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
        $list = Product::model()->findByPk($id);

        if (!isset($list))
            throw new CHttpException(400, Yii::t('app', 'This list cannot be found.'));

        if (!$this->checkPermissions($list, 'edit'))
            throw new CHttpException(403, Yii::t('app', 'You do not have permission to modify this list.'));

        $contactModel = new X2Leads;
        $comparisonList = Product::getComparisonList();
        $fields = $contactModel->getFields(true);

        if ($list->type == 'dynamic') {
            $criteriaModels = ProductCriterion::model()->findAllByAttributes(array('listId' => $list->id), new CDbCriteria(array('order' => 'id ASC')));

            if (isset($_POST['Product'], $_POST['Product']['attribute'], $_POST['Product']['comparison'], $_POST['Product']['value'])) {

                $attributes = &$_POST['Product']['attribute'];
                $comparisons = &$_POST['Product']['comparison'];
                $values = &$_POST['Product']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {

                    $list->attributes = $_POST['Product'];
                    $list->modelName = 'X2Leads';
                    $list->lastUpdated = time();

                    if (!$list->hasErrors() && $list->save()) {
                        $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
                    }
                }
            }
        } else { //static or campaign lists
            if (isset($_POST['Product'])) {
                $list->attributes = $_POST['Product'];
                $list->modelName = 'X2Leads';
                $list->lastUpdated = time();
                $list->save();
                $this->redirect(array('/contacts/contacts/list', 'id' => $list->id));
            }
        }

        if (empty($criteriaModels)) {
            $default = new ProductCriterion;
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
        $list->modelName = 'Product';
        $list->type = 'dynamic';
        $list->assignedTo = Yii::app()->user->getName();
        $list->visibility = 1;

        $contactModel = new Product;
        $comparisonList = X2List::getComparisonList();
        if (isset($_POST['X2List'])) {
            $list->type = $_POST['X2List']['type'];
            $list->attributes = $_POST['X2List'];
            $list->modelName = 'Product';
            $list->createDate = time();
            $list->lastUpdated = time();

            if (isset($_POST['X2List'], $_POST['X2List']['attribute'], $_POST['X2List']['comparison'], $_POST['X2List']['value'])) {

                $attributes = &$_POST['X2List']['attribute'];
                $comparisons = &$_POST['X2List']['comparison'];
                $values = &$_POST['X2List']['value'];

                if (count($attributes) > 0 && count($attributes) == count($comparisons) && count($comparisons) == count($values)) {
                    $list->modelName = 'Product';
                    $list->lastUpdated = time();
                }
            }
            if (!$list->hasErrors() && $list->save()) {
                if ($ajax) {
                    echo CJSON::encode($list->attributes);
                    return;
                }
                $this->redirect(array('/products/products/list', 'id' => $list->id));
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
            $list = X2Model::model('Product')->findByPk($id);
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
    public function actionIndex() {
        $model=new Product('search');
        $this->render('index', array('model'=>$model));
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if(isset($_POST['ajax']) && $_POST['ajax']==='product-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Retrieve items for combo box
     * @param string $prefix parameter to getItems2
     * @param int $page parameter to getItems2
     */
    public function actionGetItems2 () {
        $prefix = isset ($_POST['prefix']) ? $_POST['prefix'] : '';
        $page = isset ($_POST['page']) ? $_POST['page'] : 0;
        $pageSize = isset ($_POST['pageSize']) ? $_POST['pageSize'] : 20;

        $items = X2Model::model ($this->modelClass)->getItems2 (
            $prefix, $page, $pageSize, array ('id', 'description', 'price'), 'name');
        echo CJSON::encode ($items);
    }

    /**
     * Create a menu for Products
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Products = Modules::displayName();
        $Product = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;

        /**
         * To show all options:
         * $menuOptions = array(
         *     'index', 'create', 'view', 'edit', 'delete', 'print', 'import', 'export',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'index',
                'label'=>Yii::t('products','{module} List', array(
                    '{module}'=>$Product,
                )),
                'url'=>array('index')
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('products','Create'),
                'url'=>array('create')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('products','Update'),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('products','Delete'),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>Yii::t('app','Are you sure you want to delete this item?')
                )
            ),
            array(
                'name' => 'lists',
                'label' => Yii::t('contacts', 'Lists'),
                'url' => array('lists')
            ),
            array(
                'name' => 'print',
                'label' => Yii::t('app', 'Print Record'),
                'url' => '#',
                'linkOptions' => array (
                    'onClick'=>"window.open('".
                        Yii::app()->createUrl('/site/printRecord', array (
                            'modelClass' => 'Product',
                            'id' => $modelId,
                            'pageTitle' => Yii::t('app', '{module}', array(
                                '{module}' => $Product,
                            )).': '.(isset($model) ? $model->name : "")
			        ))."');"
	            ),
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('products', 'Import {module}', array(
                    '{module}' => $Products,
                )),
                'url'=>array('admin/importModels', 'model'=>'Product'),
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('products', 'Export {module}',  array(
                    '{module}' => $Products,
                )),
                 'url'=>array('admin/exportModels', 'model'=>'Product'),
            ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }


}
