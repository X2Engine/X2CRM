<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * California 95067, USA. or at email address contact@x2engine.com.
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
 *****************************************************************************************/

/**
 * @package X2CRM.modules.accounts.controllers
 */
class AccountsController extends x2base {

    public $modelClass = 'Accounts';

    public function accessRules(){
        return array(
            array('allow',
                'actions' => array('getItems'),
                'users' => array('*'),
            ),
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions' => array('index', 'view', 'create', 'update', 'search', 'addUser', 'addContact', 'removeUser', 'removeContact',
                    'addNote', 'deleteNote', 'saveChanges', 'delete', 'shareAccount', 'inlineEmail'),
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
                'class'=>'AccountCampaignAction',
            ),
        ));
    }

    public function actionGetItems(){
        $sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
        $command = Yii::app()->db->createCommand($sql);
        $qterm = $_GET['term'].'%';
        $command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
        $result = $command->queryAll();
        echo CJSON::encode($result);
        exit;
    }

    /**
     * Displays a particular model.
     * @param integer $id the ID of the model to be displayed
     */
    public function actionView($id){
        $model = $this->loadModel($id);
        if($this->checkPermissions($model, 'view')){

            // add account to user's recent item list
            User::addRecentItem('a', $id, Yii::app()->user->getId());

            parent::view($model, 'accounts');
        }else{
            $this->redirect('index');
        }
    }

    public function actionShareAccount($id){

        $model = $this->loadModel($id);
        $body = "\n\n\n\n".Yii::t('accounts', 'Account Record Details')." <br />
<br />".Yii::t('accounts', 'Name').": $model->name
<br />".Yii::t('accounts', 'Description').": $model->description
<br />".Yii::t('accounts', 'Revenue').": $model->annualRevenue
<br />".Yii::t('accounts', 'Phone').": $model->phone
<br />".Yii::t('accounts', 'Website').": $model->website
<br />".Yii::t('accounts', 'Type').": $model->type
<br />".Yii::t('app', 'Link').": ".CHtml::link($model->name, array('/accounts/accounts/view', 'id'=>$model->id));
        $body = trim($body);

        $errors = array();
        $status = array();
        $email = array();
        if(isset($_POST['email'], $_POST['body'])){

            $subject = Yii::t('accounts', "Account Record").": $model->name";
            $email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
            $body = $_POST['body'];
            // if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
            if($email['to'] === false)
                $errors[] = 'email';
            if(empty($body))
                $errors[] = 'body';

            if(empty($errors))
                $status = $this->sendUserEmail($email, $subject, $body);

            if(array_search('200', $status)){
                $this->redirect(array('view', 'id' => $model->id));
                return;
            }
            if($email['to'] === false)
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

// this nonsense is now done in Accounts::beforeValidate()
    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    /* 	public function create($model,$oldAttributes, $api){

      $model->annualRevenue = Formatter::parseCurrency($model->annualRevenue,false);
      // $model->createDate=time();
      if($api==0)
      parent::create($model,$oldAttributes,$api);
      else
      return parent::create($model,$oldAttributes,$api);
      } */

    public function actionCreate(){
        $model = new Accounts;
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group)
            $users[$group->id] = $group->name;


        if(isset($_POST['Accounts'])){
            $temp = $model->attributes;
            foreach($_POST['Accounts'] as $name => &$value){
                if($value == $model->getAttributeLabel($name))
                    $value = '';
            }
            $model->setX2Fields($_POST['Accounts']);


            if(isset($_POST['x2ajax'])){
                // if($this->create($model,$temp, '1')) { // success creating account?
                if($model->save()){ // success creating account?
                    $primaryAccountLink = '';
                    $newPhone = '';
                    $newWebsite = '';
                    if(isset($_POST['ModelName']) && isset($_POST['ModelId'])){
                        $rel = new Relationships;
                        $rel->firstType = $_POST['ModelName'];
                        $rel->firstId = $_POST['ModelId'];
                        $rel->secondType = 'Accounts';
                        $rel->secondId = $model->id;
                        $rel->save();
                        if($_POST['ModelName'] == 'Contacts'){
                            $contact = Contacts::model()->findByPk($_POST['ModelId']);
                            if($contact){
                                $changed = false;
                                if($contact->company == ''){ // if no primary account on this contact
                                    $contact->company = $model->id; // make this primary account
                                    $changed = true;
                                    $primaryAccountLink = $model->createLink();
                                }
                                if(isset($model->website) && (!isset($contact->website) || $contact->website == "")){
                                    $contact->website = $model->website;
                                    $newWebsite = $contact->website;
                                    $changed = true;
                                }
                                if(isset($model->phone) && (!isset($contact->phone) || $contact->phone == "")){
                                    $contact->phone = $model->phone;
                                    $newPhone = $contact->phone;
                                    $changed = true;
                                }

                                if($changed)
                                    $contact->update();
                            }
                        } elseif($_POST['ModelName'] == 'Opportunity'){
                            $opportunity = Opportunity::model()->findByPk($_POST['ModelId']);
                            if($opportunity){
                                if(!isset($opportunity->accountName) || $opportunity->accountName == ''){
                                    $opportunity->accountName = $model->id;
                                    $opportunity->update();
                                    $primaryAccountLink = $model->createLink();
                                }
                            }
                        }
                    }

                    echo json_encode(
                            array(
                                'status' => 'success',
                                'name' => $model->name,
                                'id' => $model->id,
                                'primaryAccountLink' => $primaryAccountLink,
                                'newWebsite' => $newWebsite,
                                'newPhone' => $newPhone,
                            )
                    );
                    Yii::app()->end();
                }else{
                    $x2ajaxCreateError = true;
                }
            }else{
                if($model->save())
                    $this->redirect(array('view', 'id' => $model->id));
            }
        }

        if(isset($_POST['x2ajax'])){
            Yii::app()->clientScript->scriptMap['*.js'] = false;
            Yii::app()->clientScript->scriptMap['*.css'] = false;
            if(isset($x2ajaxCreateError) && $x2ajaxCreateError == true){
                $page = $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'accounts'), true, true);
                echo json_encode(
                        array(
                            'status' => 'userError',
                            'page' => $page,
                        )
                );
            }else{
                $this->renderPartial('application.components.views._form', array('model' => $model, 'users' => $users, 'modelName' => 'accounts'), false, true);
            }
        }else{
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
    public function actionUpdate($id){
        $model = $this->loadModel($id);
        $model->assignedTo = explode(', ',$model->assignedTo);
        if(isset($_POST['Accounts'])){
            $model->setX2Fields($_POST['Accounts']);
            if($model->save())
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

    public function actionAddUser($id){
        $users = User::getNames();
        unset($users['admin']);
        unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id] = $group->name;
        }
        //$contacts = Contacts::getAllNames(); // very inefficient with large table
        $model = $this->loadModel($id);
        $users = Accounts::editUserArray($users, $model);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Accounts'])){

            $temp = $model->assignedTo;
            $tempArr = $model->attributes;
            $model->attributes = $_POST['Accounts'];
            $arr = $_POST['Accounts']['assignedTo'];
            $model->assignedTo = Fields::parseUsers($arr);
            if($temp != "")
                $temp.=", ".$model->assignedTo;
            else
                $temp = $model->assignedTo;
            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($tempArr,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $users,
            //'contacts' => $contacts,
            'action' => 'Add'
        ));
    }

    public function actionRemoveUser($id){

        $model = $this->loadModel($id);

        $pieces = explode(', ', $model->assignedTo);
        $pieces = Opportunity::editUsersInverse($pieces);

        // Uncomment the following line if AJAX validation is needed
        // $this->performAjaxValidation($model);

        if(isset($_POST['Accounts'])){
            $temp = $model->attributes;
            $model->attributes = $_POST['Accounts'];
            $arr = $_POST['Accounts']['assignedTo'];

            foreach($arr as $id => $user){
                unset($pieces[$user]);
            }

            $temp = Fields::parseUsersTwo($pieces);

            $model->assignedTo = $temp;
            // $changes=$this->calculateChanges($temp,$model->attributes);
            // $model=$this->updateChangelog($model,$changes);
            if($model->save())
                $this->redirect(array('view', 'id' => $model->id));
        }

        $this->render('addUser', array(
            'model' => $model,
            'users' => $pieces,
            'action' => 'Remove'
        ));
    }

    public function delete($id){

        $model = $this->loadModel($id);
        $dataProvider = new CActiveDataProvider('Actions', array(
                    'criteria' => array(
                        'condition' => 'associationId='.$id.' AND associationType=\'account\'',
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
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'account\'');
            $this->cleanUpTags($model);
            $model->delete();
        } else
            throw new CHttpException(400, 'Invalid request. Please do not repeat this request again.');
        // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
        if(!isset($_GET['ajax']))
            $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
    }

    /**
     * Lists all models.
     */
    public function actionIndex(){

        $model = new Accounts('search');
        $this->render('index', array('model' => $model));
    }
     
}
