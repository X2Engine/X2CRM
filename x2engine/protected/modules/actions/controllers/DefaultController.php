<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

class DefaultController extends x2base {

	public $modelClass = 'Actions';
	public $showActions = null;
	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules() {
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('invalid','sendReminder'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createSplash','createInline','viewGroup','complete',	//quickCreate
					'completeRedirect','update', 'quickUpdate', 'completeSelected', 'uncompleteSelected', 'saveShowActions', 'updateSelected', 'viewAll','search','completeNew','parseType','getTerms','uncomplete','uncompleteRedirect','delete','shareAction','inlineEmail'),
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
	
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
	
		$action = Actions::model()->findByPk($id);

		if($action != null) {
		
			$users = User::getNames();
			$association = $this->getAssociation($action->associationType,$action->associationId);
			
			// if($association != null)
				// $associationName = $association->name;
			//else
				//$associationName = Yii::t('app','None');
			
			
			if ($action->assignedTo==Yii::app()->user->getName() || $action->visibility==1 || $action->assignedTo=='Anyone') {
				User::addRecentItem('t',$id,Yii::app()->user->getId());	//add action to user's recent item list
				$this->render('view',array(
					'model'=>$this->loadModel($id),
					'associationModel'=>$association,
					'users'=>$users,
				));
			} else
				$this->redirect('index');
		} else
			$this->redirect('index');
	}
	
	public function actionShareAction($id){
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('actions',"Reminder, the following action is due")." ".date("Y-m-d",$model->dueDate).":\n
".Yii::t('actions','Description').": $model->actionDescription
".Yii::t('actions','Type').": $model->type
".Yii::t('actions','Associations').": ".Yii::t('actions',$model->associationName)."
".Yii::t('actions','Link to the action').": ".'http://'.Yii::app()->request->getServerName().$this->createUrl('/actions/'.$model->id);
		$body = trim($body);

		$errors = array();
		$status = array();
		$email = array();
		if(isset($_POST['email'], $_POST['body'])){
		
			$subject = Yii::t('actions',"Reminder, the following action is due")." ".date("Y-m-d",$model->dueDate);
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
		$this->render('shareAction',array(
			'model'=>$model,
			'body'=>$body,
			'email'=>$email,
			'status'=>$status,
			'errors'=>$errors
		));
	}

	public function actionSendReminder() {
		
		$dataProvider=new CActiveDataProvider('Actions', array(
			'criteria'=>array(
				'condition'=>'(dueDate<"'.mktime(23,59,59).'" AND dueDate>"'.mktime(0,0,0).'" AND complete="No")',
		)));

		$emails= User::getEmails();

		$actionArray=$dataProvider->getData();

		foreach($actionArray as $action) {
			if($action->reminder=='Yes') {

				if($action->associationId!=0) {
					$contact=Contacts::model()->findByPk($action->associationId);
					$name=$contact->firstName.' '.$contact->lastName;
				} else
					$name=Yii::t('actions','No one');
				$email=$emails[$action->assignedTo];
				if(isset($action->type))
					$type=$action->type;
				else
					$type=Yii::t('actions','Not specified');

				$subject=Yii::t('actions','Action Reminder:');
				$body=Yii::t('actions',"Reminder, the following action is due today: \n Description: {description}\n Type: {type}.\n Associations: {name}.\nLink to the action: ",
					array('{description}'=>$action->actionDescription,'{type}'=>$type,'{name}'=>$name))
					.'http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl.'/index.php/actions/'.$action->id;
				$headers='From: '.Yii::app()->params['adminEmail'];

				if($action->associationType!='none')
					$body.="\n\n".Yii::t('actions','Link to the {type}',array('{type}'=>ucfirst($action->associationType))).': http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl."/index.php/".$action->associationType."/".$action->associationId;
				$body.="\n\n".Yii::t('actions','Powered by ').'<a href=http://x2engine.com>X2Engine</a>';

				mail($email,$subject,$body,$headers);
			}
		}
	}
	
	public function create($model, $oldAttributes, $api){
		
		if($model->associationId=='')
			$model->associationId=0;
		//if($model->

            $model->createDate = time();	// created now, full datetime
            //$model->associationId=$_POST['Actions']['associationId'];
            $dueDate = $this->parseDateTime($model->dueDate);
            $model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

		//if($type=='none')
		//	$model->associationId=0;
		//$model->associationType=$type;

		$association = $this->getAssociation($model->associationType,$model->associationId);

		if($association != null) {
			$model->associationName = $association->name;
		} else {
			$model->associationName='None';
			//$model->associationId = 0;
		}
		if($model->associationName=='None' && $model->associationType!='none'){
			$model->associationName=ucfirst($model->associationType);
		}
//		$this->render('test', array('model'=>$model));
		if($model->type != 'event' && isset($_POST['submit']) && ($_POST['submit']=='0' || $_POST['submit']=='2')) {	// if user clicked "New Comment" rather than "New Action"
			$model->createDate = time();
			$model->dueDate = time();
			$model->completeDate = time();
			$model->complete='Yes';
			$model->visibility='1';
			$model->assignedTo=Yii::app()->user->getName();
			$model->completedBy=Yii::app()->user->getName();
			$model->type=$_POST['submit']==2?'note':'call';
		} else if($model->type == 'event') {
			if($model->completeDate) {
				$model->completeDate = $this->parseDateTime($model->completeDate);
			}
		}
		if($api==0)
			parent::create($model,$oldAttributes,$api);
		else
			return parent::create($model,$oldAttributes,$api);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	
	public function actionCreate() {

		$model = new Actions;
		$users = User::getNames();

	// Uncomment the following line if AJAX validation is needed
	// $this->performAjaxValidation($model);

		if(isset($_POST['Actions'])) {
//			$this->render('test', array('model'=>$_POST));
			$temp=$model->attributes;
			if(isset($_POST['inCalendar'])) { // create new calendar event
				foreach(array_keys($model->attributes) as $field){
					if(isset($_POST['CalendarEvent'][$field])){
						$model->$field=$_POST['CalendarEvent'][$field];
					}
				}
				$model->actionDescription = $_POST['Actions']['actionDescription'];
//				$this->render('test', array('model'=>$_POST['CalendarEvent']));
			} else {
				foreach(array_keys($model->attributes) as $field){
					if(isset($_POST['Actions'][$field])){
						$model->$field=$_POST['Actions'][$field];
					}
				}
			}
			if(is_numeric($model->assignedTo)) { // assigned to calendar instead of user?
                            $calendar = X2Calendar::model()->findByPk($model->assignedTo);
                            if(isset($calendar)){
				$model->calendarId = $model->assignedTo;
				$model->assignedTo = null;

				
				if($calendar->googleCalendar && $calendar->googleCalendarId) {
					$model->dueDate = $this->parseDateTime($model->dueDate);
					if($model->completeDate)
						$model->completeDate = $this->parseDateTime($model->completeDate);
					$calendar->createGoogleEvent($model); // action/event assigned to Google Calendar, no need to create Action since it's saved to google
					$this->redirect(array('/calendar'));
				}
                            }
			}
			
			$this->create($model,$temp,'0');
                        
		}
		if(isset($_GET['param'])) {
			$pieces=explode(';',$_GET['param']);
			$user=$pieces[0];
			$pieces2=explode(':',$pieces[1]);
			$type=$pieces2[0];
			$id=$pieces2[1];
		} else {	// defaults
			$user=Yii::app()->user->getName();
			$type='none';
			$id=null;
		}
		$model->associationType=$type;
		$model->assignedTo=$user;
		if(!empty($id))
			$model->associationId=$id;

		$this->render('create',array(
			'model'=>$model,
			'users'=>$users
		));
		
	}

/* 	public function actionQuickCreate() {
		$users = User::getNames();
		$actionModel=new Actions;
		$contactModel=new Contacts;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($actionModel);

		if(isset($_POST['Actions']) && isset($_POST['Contacts'])) {
                        $actionTemp=$actionModel->attributes;
			foreach($actionModel->attributes as $field=>$value){
                            if(isset($_POST['Actions'][$field])){
                                $actionModel->$field=$_POST['Actions'][$field];
                            }
                        }
			
                        $contactTemp=$contactModel->attributes;
                        // reset to blank if it's the default value
			foreach($_POST['Contacts'] as $name => &$value) {
				if($value == $contactModel->getAttributeLabel($name))
					$value = '';
			}
			foreach($contactModel->attributes as $field=>$value){
                            if(isset($_POST['Contacts'][$field])){
                                $contactModel->$field=$_POST['Contacts'][$field];
                            }
                        }
			
			$actionModel->createDate=time();
			$contactModel->createDate=time();
			
			$actionModel->visibility = $contactModel->visibility;
			$actionModel->assignedTo = $contactModel->assignedTo;
			$actionModel->priority = $contactModel->priority;
			
			
                        
                        $account = Accounts::model()->findByAttributes(array('name'=>$contactModel->company));
                        if(isset($account))
                                $contact->accountId = $account->id;
                        else
                                $contactModel->accountId = 0;
			
			$dueDate = strtotime($actionModel->dueDate);
			$actionModel->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

			if($_POST['submit']=='comment') {	// if user clicked "New Comment" rather than "New Action"
				$actionModel->createDate = time();
				$actionModel->dueDate = time();
				$actionModel->completeDate = time();
				$actionModel->complete='Yes';
				$actionModel->visibility='1';
				$actionModel->assignedTo=Yii::app()->user->getName();
				$actionModel->completedBy=Yii::app()->user->getName();
				$actionModel->type='note';
			}
                        $changes=$this->calculateChanges($contactTemp,$contactModel->attributes);
			$contactModel=$this->updateChangelog($contactModel,'Create');
			if($contactModel->save()) {

				// $actionModel->dueDate=$actionModel->dueDate;
				$actionModel->associationId = $contactModel->id;
				$actionModel->associationType = 'contacts';
				$actionModel->associationName = $contactModel->firstName.' '.$contactModel->lastName;
                                $changes=$this->calculateChanges($actionTemp,$actionModel->attributes);
				$actionModel=$this->updateChangelog($actionModel,'Create');
				if($actionModel->save())
					$this->redirect(array('contacts/view','id'=>$contactModel->id));
				else
					$contactModel->delete();
			}
		}

		$this->render('quickCreate',array(
			'actionModel'=>$actionModel,
			'contactModel'=>$contactModel,
			'users'=>$users,
		));
	} */
        
        public function update($model, $oldAttributes, $api){
            
            $dueDate = strtotime($model->dueDate);
            $model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM

            $association = $this->getAssociation($model->associationType,$model->associationId);

            if($association != null) {
                    $model->associationName = $association->name;
            } else {
                    $model->associationName = 'None';
                    $model->associationId = 0;
            }
            if($api==0)
                parent::update($model,$oldAttributes,$api);
            else
                return parent::update($model,$oldAttributes,$api);
        }

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model=$this->loadModel($id);
		$users=User::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Actions'])) {
			$temp=$model->attributes;
			foreach($model->attributes as $field=>$value){
                            if(isset($_POST['Actions'][$field])){
                                $model->$field=$_POST['Actions'][$field];
                            }
                        }

			
			$this->update($model,$temp,'0');
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
	
	public function actionQuickUpdate($id) {
		$model=$this->loadModel($id);
		if(isset($_POST['Actions'])) {
			$temp=$model->attributes;
			foreach($model->attributes as $field=>$value){
			    if(isset($_POST['Actions'][$field])){
			        $model->$field=$_POST['Actions'][$field];
			    }
			}
			
            $model->dueDate = $this->parseDateTime($model->dueDate);
			if($model->completeDate)
				$model->completeDate = $this->parseDateTime($model->completeDate);
				
			if(is_numeric($model->assignedTo)) { // assigned to calendar instead of user?
				$model->calendarId = $model->assignedTo;
				$model->assignedTo = null;
			} else {
				$model->calendarId = null;
			}

			$changes = $this->calculateChanges($temp, $model->attributes, $model);
			$model = $this->updateChangelog($model,$changes);
			
			$model->update();
		}
	}
	
	/**
	 * complete a list of selected actions from a gridview
	 */
	public function actionCompleteSelected() {
		if(isset($_POST['Actions'])) {
			$ids = $_POST['Actions'];
			foreach($ids as $id) {
				$action = Actions::model()->findByPk($id);
				if(Yii::app()->user->getName()==$action->assignedTo || $action->assignedTo=='Anyone' || $action->assignedTo=="" || Yii::app()->user->getName()=='admin') { // make sure current user can edit this action
					$action->complete = 'Yes';
					$action->completedBy = Yii::app()->user->getName();
					$action->completeDate = time();
					$action->update();
				}
			}
		}
	}
	
	/**
	 * uncomplete a list of selected actions from a gridview
	 */
	public function actionUncompleteSelected() {
		if(isset($_POST['Actions'])) {
			$ids = $_POST['Actions'];
			foreach($ids as $id) {
				$action = Actions::model()->findByPk($id);
				if(Yii::app()->user->getName()==$action->assignedTo || $action->assignedTo=='Anyone' || $action->assignedTo=="" || Yii::app()->user->getName()=='admin') { // make sure current user can edit this action
					$action->complete = 'No';
					$action->completeDate = null;
					$action->update();
				}
			}
		}
	}
	
	public function actionSaveShowActions() {
		if(isset($_POST['ShowActions'])) {
			$profile = ProfileChild::model()->findByPk(Yii::app()->user->id);
			$profile->showActions = $_POST['ShowActions'];
			$profile->update();
		}
	}
	
	/**
	 * complete/uncomplete a list of selected actions from a gridview
	 */
	public function actionUpdateSelected() {
		if(isset($_POST['C_gvCheckbox'])) {
			if(isset($_POST['complete-selected-button']))
				$complete = 'Yes';
			else if (isset($_POST['uncomplete-selected-button']))
				$complete = 'No';
			
			$actionIds = $_POST['C_gvCheckbox'];
			foreach($actionIds as $actionId) {
			    $action = Actions::model()->findByPk($actionId);
			    if(Yii::app()->user->getName()==$action->assignedTo || // make sure current user can edit this action
			       $action->assignedTo=='Anyone' || 
			       $action->assignedTo=="" || 
			       Yii::app()->user->getName()=='admin') {
			       
			       $action->complete = $complete;
			       $action->completedBy = Yii::app()->user->getName();
			       $action->completeDate = time();
			       $action->update();
			    }
			}
		}
		if(Yii::app()->user->name == 'admin')
			$this->redirect(array('/actions/admin'));
		else
			$this->redirect(array('/actions/index'));
	}
	
	// Postpones due date (and sets action to incomplete)
	public function actionTomorrow($id) {
		$model = $this->loadModel($id);
		$model->complete='No';
		$model->dueDate=time()+86400;	//set to tomorrow
		if($model->save()){
			if($model->associationType!='none')
				$this->redirect(array($model->associationType.'/'.$model->associationId));
			else
				$this->redirect(array('view','id'=>$id));
		}
	}
        
        public function delete($id){
            $model=$this->loadModel($id);
            $this->cleanUpTags($model);
            $model->delete();
        }

	// Deletes a particular model
	public function actionDelete($id) {

		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest){
                        $this->cleanUpTags($model);
			$model->delete();
                }
		else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(isset($_GET['ajax']))
			echo 'Success';
		else
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	public function actionComplete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->user->getName()==$model->assignedTo || $model->assignedTo=='Anyone' || $model->assignedTo=="" || Yii::app()->user->getName()=='admin') {
			
			if(isset($_POST['note']))
				$model->actionDescription = $model->actionDescription."\n\n".$_POST['note'];
				
			$model=$this->updateChangelog($model,'Completed');
			$model->save();
			Actions::completeAction($id);
                        
                        $notif=new Notifications;
                        $notif->record="Actions:$model->id";
                        $profile=CActiveRecord::model('ProfileChild')->findByAttributes(array('username'=>Yii::app()->user->getName()));
                        $notif->text=$profile->fullName." completed an action.";
                        $notif->user='admin';
                        $notif->createDate=time();
                        $notif->viewed=0;
                        $notif->save();

			$createNew = isset($_GET['createNew']) || ((isset($_POST['submit']) && ($_POST['submit']=='completeNew')));
			$redirect = isset($_GET['redirect']) || $createNew;
			
			if($redirect) {
				if($model->associationType!='none' && !$createNew) {	// if the action has an association
					$this->redirect(array($model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				} else {	// no association
					if($createNew)
						$this->redirect(array('/actions/create'));		// go to blank 'create action' page
					else
						$this->redirect(array('/actions/default/view','id'=>$model->id));	// view the action
				}
			} else {
				$this->redirect(array('/actions/default/view','id'=>$model->id)); 
			}
		} else {
			$this->redirect(array('/actions/invalid'));
		}
	}
	
	// Postpones due date (and sets action to incomplete)
	public function actionUncomplete($id) {
		$model = $this->loadModel($id);
		$model->complete = 0;
		$model->completeDate = null;
		//$model->dueDate = date("Y-m-d",time()+(86400));
		if($model->save()){
		if(isset($_GET['redirect'])) {
			if($model->associationType!='none')
				$this->redirect(array($model->associationType.'/'.$model->associationId));
			else
				$this->redirect(array('/actions/default/view','id'=>$id));
		} else {
			$this->redirect(array('/actions/default/view','id'=>$id));
		}
		}else{
			print_r($model->getErrors());
		}
	}
	
	// Lists all actions assigned to this user
	public function actionIndex() {
		$model=new Actions('search');
		$name='Actions';
		parent::index($model,$name);
	}

	// List all public actions
	public function actionViewAll() {
		$model=new Actions('search');
		$name='Actions';
		parent::index($model,$name);
	}
	
	public function actionViewGroup() {
		$model=new Actions('search');
		$name='Actions';
		parent::index($model,$name);
	}

	// Admin view of all actions
	public function actionAdmin() {
		$model=new Actions('search');
		$name='Actions';
		parent::admin($model,$name);
	}
	
	// display error page
	public function actionInvalid() {
		$this->render('invalid');
	}

	
	public function actionParseType() {
		if(isset($_POST['Actions']['associationType'])){
			$type=$_POST['Actions']['associationType'];
			echo $type;
		}else{
			echo 'none';
		}
	}

	public function actionGetTerms(){
		$type=$_GET['type'];
		if($type!='none'){
		if($type=='contacts'){
			$sql = 'SELECT id, CONCAT(firstName," ",lastName) as value FROM x2_contacts WHERE firstName LIKE :qterm OR lastName LIKE :qterm ORDER BY firstName ASC';
		}else{
			$sql = 'SELECT id, name as value FROM x2_'.$type.' WHERE name LIKE :qterm ORDER BY name ASC';
		}
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
		}else{
			echo array('0'=>'None');
		}
	}

	protected function getAssociation($type,$id) {
	
		$classes = array(
			'actions'=>'Actions',
			'contacts'=>'Contacts',
			'accounts'=>'Accounts',
			'sales'=>'Sales',
		);
		
		if(array_key_exists($type,$classes) && $id != 0)
			return CActiveRecord::model($classes[$type])->findByPk($id);
		else
			return null;
	}
	
/* 	public function parseName($arr) {
		$type=$arr[0]; 
		$id=$arr[1];
		if(isset($id) || true) {
			if($type=='project') {
				 $data=CActiveRecord::model('ProjectChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='contact') {
				 $data=CActiveRecord::model('Contacts')->findByPk($id);
				 $name=$data->name;
			} else if($type=='account') {
				 $data=CActiveRecord::model('Accounts')->findByPk($id);
				 $name=$data->name;
			} else if($type=='case') {
				 $data=CActiveRecord::model('CaseChild')->findByPk($id);
				 $name=$data->name;
			} else if($type=='sale') {
				 $data=CActiveRecord::model('Sales')->findByPk($id);
				 $name=$data->name;
			} else {
				$data=null
				$name='None';
			}
		} else {
			 $data=null;
			 $name='None';
		}
		return array($name,$data);
	} */
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Actions::model('Actions')->findByPk((int)$id);
		//$dueDate=$model->dueDate;
		//$model=Actions::changeDates($model);
		// if($model->associationId!=0) {
			// $model->associationName = $this->parseName(array($model->associationType,$model->associationId));
		// } else
			// $model->associationName = 'None';
		
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='actions-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
