<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * @package X2CRM.modules.opportunities.controllers
 */
class OpportunitiesController extends x2base {

	public $modelClass = 'Opportunity';
		
	public function accessRules() {
		return array(
			array('allow',
				'actions'=>array('getItems'),
				'users'=>array('*'), 
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','addUser','addContact','removeUser','removeContact',
									'saveChanges','delete','shareOpportunity','inlineEmail'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' and 'delete' actions
				'actions'=>array('admin','testScalability'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	public function actions() {
		return array(
			'inlineEmail'=>array(
				'class'=>'InlineEmailAction',
			),
		);
	}

	public function actionGetItems(){
		$sql = 'SELECT id, name as value FROM x2_opportunities WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result);
		Yii::app()->end();
	}
		
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$type = 'opportunities';
		$model = $this->loadModel($id);
		$model->associatedContacts = Contacts::getContactLinks($model->associatedContacts);
		
		parent::view($model, $type);
	}
	
	public function actionShareOpportunity($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('opportunities','Opportunity Record Details')." <br />
<br />".Yii::t('opportunities','Name').": $model->name
<br />".Yii::t('opportunities','Description').": $model->description
<br />".Yii::t('opportunities','Quote Amount').": $model->quoteAmount
<br />".Yii::t('opportunities','Opportunities Stage').": $model->salesStage
<br />".Yii::t('opportunities','Lead Source').": $model->leadSource
<br />".Yii::t('opportunities','Probability').": $model->probability
<br />".Yii::t('app','Link').": ".CHtml::link($model->name,'http://'.Yii::app()->request->getServerName().$this->createUrl('/opportunities/'.$model->id));
		
		$body = trim($body);

		$errors = array();
		$status = array();
		$email = array();
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('opportunities','Opportunity Record Details');
			$email['to'] = $this->parseEmailTo($this->decodeQuotes($_POST['email']));
			$body = $_POST['body'];
			// if(empty($email) || !preg_match("/[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}/",$email))
			if($email['to'] === false)
				$errors[] = 'email';
			if(empty($body))
				$errors[] = 'body';
			
			if(empty($errors))
				$status = $this->sendUserEmail($email,$subject,$body);

			if(array_search('200',$status)) {
				$this->redirect(array('view','id'=>$model->id));
				return;
			}
			if($email['to'] === false)
				$email = $_POST['email'];
			else
				$email = $this->mailingListToString($email['to']);
		}
		$this->render('shareOpportunity',array(
			'model'=>$model,
			'body'=>$body,
			'currentWorkflow'=>$this->getCurrentWorkflow($model->id,'opportunities'),
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}
	
	/* public function create($model,$oldAttributes,$api=0) {
		
		// process currency into an INT
//		$model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);
		
		if(isset($model->associatedContacts))
			$model->associatedContacts = Opportunity::parseContacts($model->associatedContacts);
		$model->createDate = time();
		$model->lastUpdated = time();
		// $model->expectedCloseDate = Formatter::parseDate($model->expectedCloseDate);
		if($api == 1) {
			return parent::create($model,$oldAttributes,$api);
		} else {
			parent::create($model,$oldAttributes,'0');
		}
	} */

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new Opportunity;
		$users = User::getNames();
		foreach(Groups::model()->findAll() as $group){
			$users[$group->id]=$group->name;
		}
		unset($users['admin']);
		unset($users['']);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Opportunity'])) {
			$temp=$model->attributes;
			
			$model->setX2Fields($_POST['Opportunity']);
			// die(var_dump($model));
			/* foreach($_POST['Opportunity'] as $name => &$value) {
				if($value == $model->getAttributeLabel($name))
					$value = '';
			}
			foreach($_POST as $key=>$arr){
				$pieces=explode("_",$key);
				if(isset($pieces[0]) && $pieces[0]=='autoselect'){
					$newKey=$pieces[1];
					if(isset($_POST[$newKey."_id"]) && $_POST[$newKey."_id"]!=""){
						$val=$_POST[$newKey."_id"];
					}else{
						$field=Fields::model()->findByAttributes(array('fieldName'=>$newKey));
						if(isset($field)){
							$type=ucfirst($field->linkType);
							if($type!="Contacts"){
								eval("\$lookupModel=$type::model()->findByAttributes(array('name'=>'$arr'));");
							}else{
								$names=explode(" ",$arr);
								$lookupModel=X2Model::model('Contacts')->findByAttributes(array('firstName'=>$names[0],'lastName'=>$names[1]));
							}
							if(isset($lookupModel))
								$val=$lookupModel->id;
							else
								$val=$arr;
						}
					}
					$model->$newKey=$val;
				}
			}
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['Opportunity'][$field])){
					$model->$field=$_POST['Opportunity'][$field];
					$fieldData=Fields::model()->findByAttributes(array('modelName'=>'Opportunity','fieldName'=>$field));
						if($fieldData->type=='assignment' && $fieldData->linkType=='multiple'){
							$model->$field=Accounts::parseUsers($model->$field);
						}elseif($fieldData->type=='date'){
							$model->$field=strtotime($model->$field);
						}
				}
			} */
			if(isset($_POST['x2ajax'])) {
				// if($this->create($model,$temp, '1')) { // success creating account?
				if($model->save()) { // success creating account?
					$primaryAccountLink = '';
					if(isset($_POST['ModelName']) && isset($_POST['ModelId'])) {
						Relationships::create($_POST['ModelName'], $_POST['ModelId'], 'Opportunity', $model->id);
						if($_POST['ModelName'] == 'Contacts') {
							$contact = Contacts::model()->findByPk($_POST['ModelId']);
							if($contact) {
								// Note: Account ID is saved in Contacts Model as "company" but in Opportunity Model as "accountName"
								if(isset($model->accountName) && $model->accountName != '' && (!isset($contact->company) || $contact->company == "")) {
									$contact->company = $model->accountName;
									$contact->update();
									$account = Accounts::model()->findByPk($contact->company);
									if($account) {
										$primaryAccountLink = $account->createLink();
									}
								}
							}
						}
					}
					echo json_encode(array('status'=>'success', 'primaryAccountLink'=>$primaryAccountLink));
					Yii::app()->end();
				}
			} else {
				// $this->create($model,$temp);
				if($model->save())
					$this->redirect(array('view','id'=>$model->id));
			}
		}
		
		if(isset($_POST['x2ajax'])) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			$this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'Opportunity'), false, true);
		} else {
			$this->render('create',array(
				'model'=>$model,
				'users'=>$users,
			));
		}
	}
        
	/* public function update($model,$oldAttributes,$api=0){
		
		// process currency into an INT
		// $model->quoteAmount = $this->parseCurrency($model->quoteAmount,false);

		$arr=$model->associatedContacts;
		if(isset($model->associatedContacts)) {
			foreach($model->associatedContacts as $contact) {
				$rel=new Relationships;
				$rel->firstType='Contacts';
				$rel->firstId=$contact;
				$rel->secondType='Opportunity';
				$rel->secondId=$model->id;
				if($rel->firstId!="" && $rel->secondId!="")
					$rel->save();
			}
				$model->associatedContacts=Opportunity::parseContacts($arr);
		}
		$model->lastUpdated = time();
		// if($model->expectedCloseDate!=""){
			// $model->expectedCloseDate=strtotime($model->expectedCloseDate);
		// }
		
		parent::update($model,$oldAttributes,'0');
	} */

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);
		foreach(Groups::model()->findAll() as $group)
			$users[$group->id]=$group->name;
		
		$model->assignedTo = explode(' ',$model->assignedTo);
		
		$model->associatedContacts = explode(' ',$model->associatedContacts);
		
		if(isset($_POST['Opportunity'])) {
			$model->setX2Fields($_POST['Opportunity']);

			// $this->update($model,$temp);
			$model->save();
			$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	/*
	public function actionSaveChanges($id) {
		$opportunity=$this->loadModel($id);
		if(isset($_POST['Opportunity'])) {
			$temp=$opportunity->attributes;
			foreach($opportunity->attributes as $field=>$value){
                            if(isset($_POST['Opportunity'][$field])){
                                $opportunity->$field=$_POST['Opportunity'][$field];
                            }
                        }
			
			// process currency into an INT
			$opportunity->quoteAmount = $this->parseCurrency($opportunity->quoteAmount,false);
			
			
			if($opportunity->expectedCloseDate!=""){
				$opportunity->expectedCloseDate=strtotime($opportunity->expectedCloseDate);
			}
			$changes=$this->calculateChanges($temp,$opportunity->attributes, $opportunity);
			$opportunity=$this->updateChangelog($opportunity,$changes);
			$opportunity->save();
			$this->redirect(array('view','id'=>$opportunity->id));
		}
	}
        */
	public function actionAddUser($id) {
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);
        foreach(Groups::model()->findAll() as $group){
            $users[$group->id]=$group->name;
        }
		$model=$this->loadModel($id);
		$users=Opportunity::editUserArray($users,$model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Opportunity'])) {
			$temp=$model->assignedTo; 
                        $tempArr=$model->attributes;
			$model->attributes=$_POST['Opportunity'];  
			$arr=$_POST['Opportunity']['assignedTo'];
			

			$model->assignedTo=Opportunity::parseUsers($arr);
			if($temp!="")
				$temp.=", ".$model->assignedTo;
			else
				$temp=$model->assignedTo;
			$model->assignedTo=$temp;
			// $changes=$this->calculateChanges($tempArr,$model->attributes);
			// $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$users,
			'action'=>'Add'
		));
	}

	public function actionAddContact($id) {
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);
		foreach(Groups::model()->findAll() as $group)
			$users[$group->id]=$group->name;

		$contacts=Contacts::getAllNames();
        unset($contacts['0']);
		$model=$this->loadModel($id);

		$contacts=Opportunity::editContactArray($contacts, $model);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Opportunity'])) {
			$temp=$model->associatedContacts; 
            $tempArr=$model->attributes;
			$model->attributes=$_POST['Opportunity'];  
			$arr=$_POST['Opportunity']['associatedContacts'];
			foreach($arr as $contactId) {
				$rel=new Relationships;
				$rel->firstType='Contacts';
				$rel->firstId=$contactId;
				$rel->secondType='Opportunity';
				$rel->secondId=$model->id;
				$rel->save();
			}
            // $changes=$this->calculateChanges($tempArr,$model->attributes, $model);
            // $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array( 
			'model'=>$model,
			'users'=>$users,
			'contacts'=>$contacts,
			'action'=>'Add'
		));
	}

	public function actionRemoveUser($id) {

		$model=$this->loadModel($id);

		$pieces=explode(', ',$model->assignedTo);
		$pieces=Opportunity::editUsersInverse($pieces);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Opportunity'])) {
                        $temp=$model->attributes;
			$model->attributes=$_POST['Opportunity'];  
			$arr=$_POST['Opportunity']['assignedTo'];
			
			
			foreach($arr as $id=>$user){
				unset($pieces[$user]);
			}
			
			$temp=Opportunity::parseUsersTwo($pieces);

			$model->assignedTo=$temp;
            // $changes=$this->calculateChanges($temp,$model->attributes, $model);
            // $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addUser',array(
			'model'=>$model,
			'users'=>$pieces,
			'action'=>'Remove'
		));
	}

	public function actionRemoveContact($id) {

		$model=$this->loadModel($id);
		$rels=Relationships::model()->findAllByAttributes(array('firstType'=>'Contacts','secondType'=>'Opportunity','secondId'=>$id));
        $pieces=array();
        foreach($rels as $relationship){
            $contact=X2Model::model('Contacts')->findByPk($relationship->firstId);
            if(isset($contact)){
                $pieces[$relationship->firstId]=$contact->name;
            }
        }
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Opportunity'])) {
			$temp=$model->attributes;
			$model->attributes=$_POST['Opportunity'];  
			$arr=$_POST['Opportunity']['associatedContacts'];
			
			
			foreach($arr as $id=>$contact) {
				$rel=X2Model::model('Relationships')->findByAttributes(array('firstType'=>'Contacts','firstId'=>$contact,'secondType'=>'Opportunity','secondId'=>$model->id));
				if(isset($rel))
					$rel->delete();
				unset($pieces[$contact]);
			}
			// $changes=$this->calculateChanges($temp,$model->attributes);
			// $model=$this->updateChangelog($model,$changes);
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('addContact',array(
			'model'=>$model,
			'contacts'=>$pieces,
			'action'=>'Remove'
		));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Opportunity('search');
		$this->render('index', array('model'=>$model));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = X2Model::model('Opportunity')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app','The requested page does not exist.'));
		return $model;
	}
	
	public function delete($id) {
		$model = $this->loadModel($id);
		
		CActiveDataProvider::model('Actions')->deleteAllByAttributes(array('associationType'=>'opportunities','associationId'=>$id));
		
		$this->cleanUpTags($model);
		$model->delete();
	}

	public function actionDelete($id) {
		$model=$this->loadModel($id);
        
		if(Yii::app()->request->isPostRequest) {
            $event=new Events;
            $event->type='record_deleted';
            $event->associationType=$this->modelClass;
            $event->associationId=$model->id;
            $event->text=$model->name;
            $event->user=Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'opportunities\'');
            $this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,Yii::t('app','Invalid request. Please do not repeat this request again.'));
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	public function actionGetTerms(){
		$sql = 'SELECT id, name as value FROM x2_accounts WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
}
