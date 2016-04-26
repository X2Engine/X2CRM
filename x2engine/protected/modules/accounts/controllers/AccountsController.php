<?php

/***********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 **********************************************************************************/

/**
 * @package application.modules.accounts.controllers
 */
class AccountsController extends x2base {

    public $modelClass = 'Accounts';

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('getItems'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'search', 'addUser', 'removeUser',
                    'addNote', 'deleteNote', 'saveChanges', 'delete', 'shareAccount', 'inlineEmail', 'qtip'),
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

    public function behaviors() {
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
                'attributesOfNewRecordToUpdate' => array(
                    'Contacts' => array(
                        'nameId' => 'company',
                        'website' => 'website',
                        'phone' => 'phone',
                    ),
                    'Opportunity' => array(
                        'accountName' => 'id',
                    )
                )
            ),
        ));
    }

    public function actions() {
        return array_merge(parent::actions(), array(
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
            'accountsReport' => array(
                'class' => 'AccountsReportAction',
            ),
            'exportAccountsReport' => array(
                'class' => 'ExportAccountsReportAction',
            ),
            'accountsCampaign' => array(
                'class' => 'AccountCampaignAction',
            ),
        ));
    }

    public function actionGetItems($term) {
        LinkableBehavior::getItems ($term);
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id) {
        $model = $this->loadModel($id);
        if (!parent::checkPermissions($model, 'view'))
            $this->denied();

        // add account to user's recent item list
        User::addRecentItem('a', $id, Yii::app()->user->getId());
        if ($model->checkForDuplicates()) {
            $this->redirect($this->createUrl('/site/duplicateCheck', array(
                        'moduleName' => 'accounts',
                        'modelName' => 'Accounts',
                        'id' => $id,
                        'ref' => 'view',
            )));
        } else {
            $model->duplicateChecked();
            parent::view($model, 'accounts');
        }
    }

    public function actionShareAccount($id) {

        $model = $this->loadModel($id);
        $body = "\n\n\n\n".Yii::t('accounts', '{module} Record Details', array(
            '{module}'=>Modules::displayName(false)
        ))." <br />
<br />".Yii::t('accounts', 'Name').": $model->name
<br />".Yii::t('accounts', 'Description').": $model->description
<br />".Yii::t('accounts', 'Revenue').": $model->annualRevenue
<br />".Yii::t('accounts', 'Phone').": $model->phone
<br />".Yii::t('accounts', 'Website').": $model->website
<br />".Yii::t('accounts', 'Type').": $model->type
<br />".Yii::t('app', 'Link') . ": " . CHtml::link($model->name,
            array('/accounts/accounts/view', 'id'=>$model->id));
        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if (isset($_POST['email'], $_POST['body'])) {

            $subject = Yii::t('accounts', "Account Record") . ": $model->name";
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if ($email['to'] === false)
                $errors[] = 'email';
            if (empty($body))
                $errors[] = 'body';

            if (empty($errors))
                $status = $this->sendUserEmail($email, $subject, $body);

            if (array_search('200', $status)) {
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if ($email['to'] === false)
                $email = $_POST['email'];
            else
                $email = $this->mailingListToString($email['to']);
        }
        $this->render('shareAccount', array(
            'model' => $model,
            'body' => $body,
            'email' => $email,
            'status' => $status,
            'errors' => $errors
        ));
    }

    public function actionCreate() {
        $model = new Accounts;
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach (Groups::model()->findAll() as $group)
            $users[$group->id] = $group->name;


        if (isset($_POST['Accounts'])) {

            $model->setX2Fields($_POST['Accounts']);

            if (isset($_POST['x2ajax'])) {
                $ajaxErrors = $this->quickCreate($model);
            } else {
                if ($model->validate () && $model->checkForDuplicates()) {
                    Yii::app()->user->setState('json_attributes', json_encode($model->attributes));
                    $this->redirect($this->createUrl('/site/duplicateCheck', array(
                                'moduleName' => 'accounts',
                                'modelName' => 'Accounts',
                                'id' => null,
                                'ref' => 'create',
                    )));
                } else {
                    if ($model->save()) {
                        $this->redirect(array('view', 'id' => $model->id));
                    }
                }
            }
        }

        if (isset($_POST['x2ajax'])) {
            $this->renderInlineCreateForm($model, isset($ajaxErrors) ? $ajaxErrors : false);
        } else {
            $this->render('create', array(
                'model' => $model,
                'users' => $users,
            ));
        }
    }

    // this nonsense is now done in Accounts::beforeValidate()
    /*         public function update($model, $oldAttributes,$api){
      // process currency into an INT
      $model->annualRevenue = Formatter::parseCurrency($model->annualRevenue,false);

      if($api==0)
      parent::update($model,$oldAttributes,$api);
      else
      return parent::update($model,$oldAttributes,$api);
      } */

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionUpdate($id) {
        $model = $this->loadModel($id);
        if (isset($_POST['Accounts'])) {
            $model->setX2Fields($_POST['Accounts']);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('update', array(
            'model' => $model,
        ));
    }

    /*
      public function actionSaveChanges($id) {
      $account=$this->loadModel($id);
      if(isset($_POST['Accounts'])) {
      $temp=$account->attributes;
      foreach($account->attributes as $field=>$value){
      if(isset($_POST['Accounts'][$field])){
      $account->$field=$_POST['Accounts'][$field];
      }
      }

      // process currency into an INT
      $account->annualRevenue = Formatter::parseCurrency($account->annualRevenue,false);
      $changes=$this->calculateChanges($temp,$account->attributes, $account);
      $account=$this->updateChangelog($account,$changes);
      $account->update();
      $this->redirect(array('view','id'=>$account->id));
      }
      }
     */

    public function actionAddUser($id) {
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach (Groups::model()->findAll() as $group) {
            $users[$group->id] = $group->name;
        }
        //$contacts = Contacts::getAllNames(); // very inefficient with large table
        $model = $this->loadModel($id);
        $users = Accounts::editUserArray($users, $model);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Accounts'])) {

            $temp = $model->assignedTo;
            $tempArr = $model->attributes;
            $model->attributes = $_POST['Accounts'];
            $arr = $_POST['Accounts']['assignedTo'];
            $model->assignedTo = Fields::parseUsers($arr);
            if ($temp != "")
                $temp.=", " . $model->assignedTo;
            else
                $temp = $model->assignedTo;
            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($tempArr,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $users,
            //'contacts' => $contacts,
            'action' => 'Add'
        ));
    }

    public function actionRemoveUser($id) {

        $model = $this->loadModel($id);

        $pieces = explode(', ', $model->assignedTo);
        $pieces = Opportunity::editUsersInverse($pieces);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if (isset($_POST['Accounts'])) {
            $temp = $model->attributes;
            $model->attributes = $_POST['Accounts'];
            $arr = $_POST['Accounts']['assignedTo'];

            foreach ($arr as $id => $user) {
                unset($pieces[$user]);
            }

            $temp = Fields::parseUsersTwo($pieces);

            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($temp,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if ($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $pieces,
            'action' => 'Remove'
        ));
    }

    public function delete($id) {

        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Actions', array(
            'criteria' => array(
                'condition' => 'associationId=' . $id . ' AND associationType=\'account\'',
        )));

        $actions = $dataProvider->getData();
        foreach ($actions as $action) {
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
    public function actionDelete($id) {
        if (Yii::app()->request->isPostRequest) {
            $model = $this->loadModel($id);
            Actions::model()->deleteAll('associationId=' . $id . ' AND associationType=\'account\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if (!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex() {

        $model = new Accounts('search');
        $this->render('index', array('model' => $model));
    }

    public function actionQtip($id) {
        $model = $this->loadModel($id);
        $this->renderPartial('qtip', array('model' => $model));
    }

    /**
     * Create a menu for Accounts
     * @param array Menu options to remove
     * @param X2Model Model object passed to the view
     * @param array Additional menu parameters
     */
    public function insertMenu($selectOptions = array(), $model = null, $menuParams = null) {
        $Accounts = Modules::displayName();
        $Account = Modules::displayName(false);
        $modelId = isset($model) ? $model->id : 0;
        $modelName = isset($model) ? $model->name : "";

        /**
         * To show all options:
         * $menuOptions = array(
         *     'all', 'create', 'report', 'import', 'export', 'view', 'edit', 'share',
         *     'delete', 'email', 'attach', 'quotes', 'quick', 'print',
         * );
         */

        $menuItems = array(
            array(
                'name'=>'all',
                'label'=>Yii::t('accounts','All {module}', array('{module}'=>$Accounts)),
                'url' => array('index'),
            ),
            array(
                'name'=>'create',
                'label'=>Yii::t('accounts','Create {module}', array('{module}'=>$Account)),
                'url'=>array('create')
            ),
            array(
                'name'=>'report',
                'label'=>Yii::t('accounts','{module} Report', array('{module}'=>$Accounts)),
                'url'=>array('accountsReport')
            ),
            array(
                'name'=>'import',
                'label'=>Yii::t('accounts',"Import {module}", array('{module}'=>$Accounts)),
                'url'=>array('admin/importModels', 'model'=>'Accounts')
            ),
            array(
                'name'=>'export',
                'label'=>Yii::t('accounts','Export {module}', array('{module}'=>$Accounts)),
                'url'=>array('admin/exportModels', 'model'=>'Accounts')
            ),
            RecordViewLayoutManager::getViewActionMenuListItem ($modelId),
            array(
                'name'=>'edit',
                'label'=>Yii::t('accounts','Edit {module}', array('{module}'=>$Account)),
                'url'=>array('update', 'id'=>$modelId)
            ),
            array(
                'name'=>'share',
                'label'=>Yii::t('accounts','Share {module}', array('{module}'=>$Account)),
                'url'=>array('shareAccount','id'=>$modelId)
            ),
            array(
                'name'=>'delete',
                'label'=>Yii::t('accounts','Delete {module}', array('{module}'=>$Account)),
                'url'=>'#',
                'linkOptions'=>array(
                    'submit'=>array('delete','id'=>$modelId),
                    'confirm'=>'Are you sure you want to delete this item?'
                )
            ),
            array(
                'name'=>'email',
                'label' => Yii::t('app', 'Send Email'),
                'url' => '#',
                'linkOptions' => array('onclick' => 'toggleEmailForm(); return false;')
            ),
            ModelFileUploader::menuLink(),
            array(
                'name'=>'quotes',
                'label' => Yii::t('quotes', 'Quotes/Invoices'),
                'url' => 'javascript:void(0)',
                'linkOptions' => array(
                    'onclick' => 'x2.inlineQuotes.toggle(); return false;')
            ),
            array(
                'name'=>'quick',
                'label'=>Yii::t('app', 'Quick Create'),
                'url'=>array('/site/createRecords', 'ret'=>'accounts'),
                'linkOptions'=>array(
                    'id'=>'x2-create-multiple-records-button',
                    'class'=>'x2-hint',
                    'title'=>Yii::t('app', 'Create a {contact}, {account}, and {opportunity}.', array(
                        '{account}' => $Account,
                        '{contact}' => Modules::displayName(false, "Contacts"),
                        '{opportunity}' => Modules::displayName(false, "Opportunities"),
                    )))
            ),
            array(
                'name'=>'print',
	            'label' => Yii::t('app', 'Print Record'),
	            'url' => '#',
	            'linkOptions' => array (
		            'onClick'=>"window.open('".
			            Yii::app()->createUrl('/site/printRecord', array (
				            'modelClass' => 'Accounts',
				            'id' => $modelId,
				            'pageTitle' => Yii::t('app', 'Account').': '.$modelName
			        ))."');"
                ),
	        ),
            RecordViewLayoutManager::getEditLayoutActionMenuListItem (),
        );

        $this->prepareMenu($menuItems, $selectOptions);
        $this->actionMenu = $this->formatMenu($menuItems, $menuParams);
    }
}
