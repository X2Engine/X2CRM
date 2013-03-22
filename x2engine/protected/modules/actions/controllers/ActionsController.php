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
 * @package X2CRM.modules.actions.controllers 
 */
class ActionsController extends x2base {

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
				'actions'=>array('invalid','sendReminder','emailOpened'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','createSplash','createInline','viewGroup','complete',	//quickCreate
					'completeRedirect','update', 'quickUpdate', 'completeSelected', 'uncompleteSelected', 'saveShowActions', 'viewAll','search','completeNew','parseType','getTerms','uncomplete','uncompleteRedirect','delete','shareAction','inlineEmail', 'publisherCreate'),
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
		$action = CActiveRecord::model('Actions')->findByPk($id);
		
		if($action === null)
			$this->redirect('index');
		
		$users = User::getNames();
		$association = $this->getAssociation($action->associationType,$action->associationId);
		
		// if($association != null)
			// $associationName = $association->name;
		//else
			//$associationName = Yii::t('app','None');
		
		if($this->checkPermissions($action, 'view')) {
			
			X2Flow::trigger('record_viewed',array('model'=>$action));
			
			User::addRecentItem('t',$id,Yii::app()->user->getId());	//add action to user's recent item list
			$this->render('view',array(
				'model'=>$this->loadModel($id),
				'associationModel'=>$association,
				'users'=>$users,
			));
		} else
			$this->redirect('index');
			
	}

	public function actionViewEmail($id) {
		$action=X2Model::model('Actions')->findByPk($id);
		echo $action->actionDescription;
	}

	public function actionShareAction($id) {
		
		$model=$this->loadModel($id);
		$body="\n\n\n\n".Yii::t('actions',"Reminder, the following action is due")." ".date("Y-m-d",$model->dueDate).":<br />
<br />".Yii::t('actions','Description').": $model->actionDescription
<br />".Yii::t('actions','Type').": $model->type
<br />".Yii::t('actions','Associations').": ".Yii::t('actions',$model->associationName)."
<br />".Yii::t('actions','Link to the action').": ".CHtml::link('Link','http://'.Yii::app()->request->getServerName().$this->createUrl('/actions/'.$model->id));
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
					$contact=X2Model::model('Contacts')->findByPk($action->associationId);
					$name=$contact->firstName.' '.$contact->lastName;
				} else
					$name=Yii::t('actions','No one');
                if(isset($emails[$action->assignedTo])){
                    $email=$emails[$action->assignedTo];
                }else{
                    continue;
                }
				if(isset($action->type))
					$type=$action->type;
				else
					$type=Yii::t('actions','Not specified');

				$subject=Yii::t('actions','Action Reminder:');
				$body=Yii::t('actions',"Reminder, the following action is due today: \n Description: {description}\n Type: {type}.\n Associations: {name}.\nLink to the action: ",
					array('{description}'=>$action->actionDescription,'{type}'=>$type,'{name}'=>$name))
					.'http://'.Yii::app()->request->getServerName().Yii::app()->request->scriptUrl.'/actions/'.$action->id;
				$headers='From: '.Yii::app()->params['adminEmail'];

				if($action->associationType!='none')
					$body.="\n\n".Yii::t('actions','Link to the {type}',array('{type}'=>ucfirst($action->associationType))).': http://'.Yii::app()->request->getServerName().Yii::app()->request->baseUrl."/index.php/".$action->associationType."/".$action->associationId;
				$body.="\n\n".Yii::t('actions','Powered by ').'<a href=http://x2engine.com>X2Engine</a>';

				mail($email,$subject,$body,$headers);
			}
		}
	}
	
	public function create($model, $oldAttributes, $api) {
		
		/* if($model->associationId=='')
			$model->associationId=0;
		//if($model->

        $model->createDate = time();	// created now, full datetime
        //$model->associationId=$_POST['Actions']['associationId'];
        if(!is_numeric($model->dueDate)){
            $dueDate = X2Model::parseDateTime($model->dueDate);
            $model->dueDate = ($dueDate===false)? '' : $dueDate; //date('Y-m-d',$dueDate).' 23:59:59';	// default to being due by 11:59 PM
        } */

		//if($type=='none')
		//	$model->associationId=0;
		//$model->associationType=$type;
        
		/* $association = $this->getAssociation($model->associationType,$model->associationId);

		if($association != null) {
			$model->associationName = $association->name;
			if($association->hasAttribute('lastActivity') && $api==0) {
				$association->lastActivity = time();
				$association->update(array('lastActivity'));
			}
		} else {
			$model->associationName='None';
			//$model->associationId = 0;
		}
		if($model->associationName=='None' && $model->associationType!='none'){
			$model->associationName=ucfirst($model->associationType);
		} */
//		$this->render('test', array('model'=>$model));
		/* if($model->type != 'event' && isset($_POST['submit']) && ($_POST['submit']=='0' || $_POST['submit']=='2') && $model->calendarId == null) {	// if user clicked "New Comment" rather than "New Action"
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
				$model->completeDate = X2Model::parseDateTime($model->completeDate);
			}
		} */
		
		// $model->syncGoogleCalendar();
		// google sync
 		/* if(!is_numeric($model->assignedTo)) { // assigned to user
			$profile = ProfileChild::model()->findByAttributes(array('username'=>$model->assignedTo));
			if(isset($profile))
				$profile->syncActionToGoogleCalendar($model); // sync action to Google Calendar if user has a Google Calendar
		} else { // Assigned to group
			$groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
			foreach($groups as $group) {
				$profile = ProfileChild::model()->findByPk($group['userId']);
				if(isset($profile))
					$profile->syncActionToGoogleCalendar($model);
			}
		} */
		
		if($api==0){
			parent::create($model,$oldAttributes,$api);
        }else
			return parent::create($model,$oldAttributes,$api);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	
	public function actionCreate() {
		
		$model = new Actions;
		$users = User::getNames();
		
		if(isset($_POST['Actions'])) {
			$temp = $model->attributes;
			if(isset($_POST['inCalendar'])) { // create new calendar event
				foreach(array_keys($model->attributes) as $field){
					if(isset($_POST['CalendarEvent'][$field])){
						$model->$field=$_POST['CalendarEvent'][$field];
					}
				}
				$model->actionDescription = $_POST['Actions']['actionDescription'];
			} else {
				foreach(array_keys($model->attributes) as $field){
					if(isset($_POST['Actions'][$field])){
						$model->$field=$_POST['Actions'][$field];
					}
				}
			}
			
			
			// if user clicked "New Comment" rather than "New Action"
			if($model->type != 'event' && isset($_POST['submit']) && ($_POST['submit']=='0' || $_POST['submit']=='2') && $model->calendarId == null) {
				$model->createDate = time();
				$model->dueDate = time();
				$model->completeDate = time();
				$model->complete = 'Yes';
				$model->visibility = 1;
				$model->assignedTo = Yii::app()->user->getName();
				$model->completedBy = Yii::app()->user->getName();
				$model->type = $_POST['submit']==2? 'note' : 'call';
			} elseif($model->type == 'event' && !empty($model->completeDate)) {
				$model->completeDate = X2Model::parseDateTime($model->completeDate);
			}
			
			
			// $this->create($model,$temp,'0');
			if($model->save()) {
				$model->syncGoogleCalendar('create');
			
				if(isset($_GET['inline']) || $model->type == 'note') {
					if($model->associationType == 'product' || $model->associationType == 'products')
						$this->redirect(array('/products/products/view', 'id' => $model->associationId));	//TODO: avoid such hackery
					elseif($model->associationType == 'Campaign')
						$this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
					else
						$this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId));
				} else {
					$this->redirect(array('view', 'id' => $model->id));
				}
			}
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

	public function actionPublisherCreate() {
		
		if(isset($_POST['SelectedTab'],$_POST['Actions'])) {
		
			$model = new Actions;
		
			$temp = $model->attributes;
			foreach(array_keys($model->attributes) as $field){
				if(isset($_POST['Actions'][$field])){
					$model->$field=$_POST['Actions'][$field];
				}
			}
			
			// format dates
			$model->dueDate = X2Model::parseDateTime($model->dueDate);
			
			if($_POST['SelectedTab'] == 'new-event') {
                $event=new Events;
                $event->type='calendar_event';
                $event->visibility=$model->visibility;
                $event->associationType='Actions';
                $event->timestamp=$model->dueDate;
				$model->type = 'event';
				if($model->completeDate){
					$model->completeDate = X2Model::parseDateTime($model->completeDate);
                }else{
                    $model->completeDate=$model->dueDate;
                }
			} else {
				$model->completeDate = null;
			}
			
			
			// format association
			if($model->associationId=='')
				$model->associationId=0;
			    
			$association = $this->getAssociation($model->associationType,$model->associationId);
			
			if($association) {
				$model->associationName = $association->name;
				if($association->hasAttribute('lastActivity')) {
					$association->lastActivity = time();
					$association->update(array('lastActivity'));
				}
			} else
				$model->associationName = 'none';
			
			if($model->associationName == 'None' && $model->associationType != 'none')
				$model->associationName = ucfirst($model->associationType);
	
			if($_POST['SelectedTab'] == 'log-a-call' || $_POST['SelectedTab'] == 'new-comment') {
				$event=new Events;
				$event->associationType='Actions';
				$event->type='record_create';
				$event->user=Yii::app()->user->getName();
				$event->visibility=$model->visibility;
				
				$model->createDate = time();
				$model->dueDate = time();
				$model->completeDate = time();
				$model->complete='Yes';
				$model->visibility='1';
				$model->assignedTo=Yii::app()->user->getName();
				$model->completedBy=Yii::app()->user->getName();
				if($_POST['SelectedTab'] == 'log-a-call')
					$model->type = 'call';
				else
					$model->type = 'note';
			}
			
			// save model
			$name = $this->modelClass;
			$model->createDate=time(); 
			
			if(!empty($model->type))
				$model->disableBehavior('changelog');
			
			if($model->save()) { // action saved to database *
				if(isset($event)){
					$event->associationId=$model->id;
					$event->save();
				}
				
				// now done in X2ChangeLogBehavior
				
				// if(empty($model->type)) {
					/* $event2=new Events;
					$event2->associationType='Actions';
					$event2->associationId=$model->id;
					$event2->visibility=$model->visibility;
					$event2->user=Yii::app()->user->getName();
					$event2->type='record_create';
					$event2->save();
					
					$event=new Events;
					$event->associationType='Actions';
					$event->associationId=$model->id;
					$event->type='action_reminder';
					$event->visibility=$model->visibility;
					$event->user=$model->assignedTo;
					$event->timestamp=$model->dueDate;
					$event->save(); */
				// }
				
				// notify other user (if not assigned to logged in user)
				// $changes = $this->calculateChanges($temp, $model->attributes, $model);
				// $this->updateChangelog($model,$changes);
				
				
				/* if($model->assignedTo != Yii::app()->user->getName()){
					$notif = new Notification;
					$notif->user = $model->assignedTo;
					$notif->createdBy = Yii::app()->user->getName();
					$notif->createDate = time();
					$notif->type = 'create';
					$notif->modelType = $name;
					$notif->modelId = $model->id;
					$notif->save();
				} */
				
				$model->syncGoogleCalendar('create');
				/* // Google Calendar Sync
				if(!is_numeric($model->assignedTo)) { // assigned to user
					$profile = ProfileChild::model()->findByAttributes(array('username'=>$model->assignedTo));
					if(isset($profile))
						$profile->syncActionToGoogleCalendar($model); // sync action to Google Calendar if user has a Google Calendar
				} else { // Assigned to group
					$groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
					foreach($groups as $group) {
						$profile = ProfileChild::model()->findByPk($group['userId']);
						if(isset($profile))
							$profile->syncActionToGoogleCalendar($model);
					}
				} */
			}
			
		}
	}

	public function update($model, $oldAttributes, $api){
		
		// now in Actions::beforeSave()
		/* $model->dueDate = X2Model::parseDateTime($model->dueDate);

		if($model->completeDate)
			$model->completeDate = X2Model::parseDateTime($model->completeDate);

		$association = $this->getAssociation($model->associationType,$model->associationId);

		if($association != null) {
			$model->associationName = $association->name;
		} else {
			$model->associationName = 'None';
			$model->associationId = 0;
		} */
		
		// now in Actions::synchGoogleCalendar()
		/* if( !is_numeric($model->assignedTo)) { // assigned to user
			$profile = ProfileChild::model()->findByAttributes(array('username'=>$model->assignedTo));
			if(isset($profile)) // prevent error for actions assigned to 'Anyone'
				$profile->updateGoogleCalendarEvent($model); // update action in Google Calendar if user has a Google Calendar
		} else { // Assigned to group
			$groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
			foreach($groups as $group) {
				$profile = ProfileChild::model()->findByPk($group['userId']);
				if(isset($profile)) // prevent error for actions assigned to 'Anyone'
					$profile->updateGoogleCalendarEvent($model);
			}
		} */
		
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
	public function actionUpdate() {
        if(isset($_GET['id'])){
            $id=$_GET['id'];
        }else{
            throw new CHttpException('No action ID provided.',400);
        }
		$model=$this->loadModel($id);
		$users=User::getNames();

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Actions'])) {
			$oldAttributes = $model->attributes;
			$model->setX2Fields($_POST['Actions']);
			if($model->dueDate != $oldAttributes['dueDate']) {
				$event = CActiveRecord::model('Events')->findByAttributes(array('type'=>'action_reminder','associationType'=>'Actions','associationId'=>$model->id));
				if(isset($event)) {
					$event->timestamp = $model->dueDate;
					$event->update(array('timestamp'));
				}
			}
			
			// $this->update($model,$oldAttributes,'0');
			if($model->save()) {
				$model->updateGoogleCalendar();
				if(isset($_GET['redirect']) && $model->associationType != 'none') { // if the action has an association
					if($model->associationType == 'product' || $model->associationType == 'products')
						$this->redirect(array('/products/products/view', 'id' => $model->associationId));
					//TODO: avoid such hackery
					elseif($model->associationType == 'Campaign')
						$this->redirect(array('/marketing/marketing/view', 'id' => $model->associationId));
					else
						$this->redirect(array('/' . $model->associationType . '/' . $model->associationType . '/view', 'id' => $model->associationId)); // go back to the association
				} else {	// no association
					$this->redirect(array('/actions/' . $model->id)); // view the action
				}
			}
		}
        if(isset($_GET['param'])) {
			$pieces=explode(';',$_GET['param']);
			$pieces2=explode(':',$pieces[1]);
			$type=$pieces2[0];
			$id=$pieces2[1];
			$model->associationType=$type;
			$model->associationId=$id;
			$model->associationName="";
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
			
            $model->dueDate = X2Model::parseDateTime($model->dueDate);
			if($model->completeDate){
				$model->completeDate = X2Model::parseDateTime($model->completeDate);
            }elseif(empty($model->completeDate)){
                $model->completeDate=$model->dueDate;
            }

			// $changes = $this->calculateChanges($temp, $model->attributes, $model);
			// $model = $this->updateChangelog($model,$changes);
			
 			if(!is_numeric($model->assignedTo)) { // assigned to user
				$profile = ProfileChild::model()->findByAttributes(array('username'=>$model->assignedTo));
				if(isset($profile))
					$profile->updateGoogleCalendarEvent($model); // update action in Google Calendar if user has a Google Calendar
			} else { // Assigned to group
				$groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
				foreach($groups as $group) {
					$profile = ProfileChild::model()->findByPk($group['userId']);
					if(isset($profile))
						$profile->updateGoogleCalendarEvent($model);
				}
			}
			
			$model->update();
		}
	}
	
	/**
	 * Complete a list of selected actions from a gridview
	 */
	public function actionCompleteSelected() {
		$this->updateSelected('complete');
	}
	
	/**
	 * Uncomplete a list of selected actions from a gridview
	 */
	public function actionUncompleteSelected() {
		$this->updateSelected('uncomplete');
	}
	
	/**
	 * Updates several actions at once (complete, uncomplete)
	 * @param string $operation the type of update happening
	 */
	protected function updateSelected($operation) {
		if(isset($_POST['actionIds']) && is_array($_POST['actionIds'])) {
			foreach(CActiveRecord::model('Actions')->findAllByPk($_POST['actionIds']) as $action) {
				if($action === null)
					continue;
				
				$inGroup = false;
				if(ctype_digit($action->assignedTo))	// we have an action assigned to a group? Then check if we are in the group
					$inGroup = Groups::inGroup(Yii::app()->user->id, $action->assignedTo);
				
				if(Yii::app()->user->getName()==$action->assignedTo || $action->assignedTo=='Anyone' || $action->assignedTo=='' || $inGroup || Yii::app()->user->checkAccess('AdminIndex')) { // make sure current user can edit this action
					
					if($operation === 'complete')
						$action->complete();		// $this->completeNotification('admin',$action->id);
					elseif($operation === 'uncomplete')
						$action->uncomplete();
					
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
	
	// Postpones due date (and sets action to incomplete)
	/* public function actionTomorrow($id) {
		$model = $this->loadModel($id);
		$model->complete='No';
		$model->dueDate=time()+86400;	//set to tomorrow
		if($model->save()){
			if($model->associationType!='none')
				$this->redirect(array($model->associationType.'/'.$model->associationId));
			else
				$this->redirect(array('view','id'=>$id));
		}
	} */

	/**
	 * API method to delete an action
	 * @param integer $id The id of the action
	 */
	public function delete($id) {
		$model = $this->loadModel($id);
		$this->cleanUpTags($model);
		$model->delete();
	}

	/**
	 * Deletes an action
	 * @param integer $id The id of the action
	 */
	public function actionDelete($id) {

		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest){
			// $this->cleanUpTags($model);	// now in TagBehavior
			/* $event=new Events;
			$event->type='record_deleted';
			$event->associationType=$this->modelClass;
			$event->associationId=$model->id;
			$event->text=$model->name;
			$event->visibility=$model->visibility;
			$event->user=Yii::app()->user->getName();
			$event->save();
			Events::model()->deleteAllByAttributes(array('associationType'=>'Actions','associationId'=>$id,'type'=>'action_reminder')); */
			
			$model->syncGoogleCalendar('delete');
			
 			/* if(!is_numeric($model->assignedTo)) { // assigned to user
				$profile = ProfileChild::model()->findByAttributes(array('username'=>$model->assignedTo)); 
				if(isset($profile))
					$profile->deleteGoogleCalendarEvent($model); // update action in Google Calendar if user has a Google Calendar
			} else { // Assigned to group
			$groups = Yii::app()->db->createCommand()->select('userId')->from('x2_group_to_user')->where("groupId={$model->assignedTo}")->queryAll();
			foreach($groups as $group) {
				$profile = ProfileChild::model()->findByPk($group['userId']);
				if(isset($profile))
					$profile->deleteGoogleCalendarEvent($model);
			} */
			
			$model->delete();
		} else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		}
		if(isset($_GET['ajax']))
			echo 'Success';
		else
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}
	
	/**
	 * Marks an action as complete and redirects back to the page it was completed on.
	 * @param integer $id The id of the action
	 */
	public function actionComplete($id) {
		$model = $this->loadModel($id);
		
		$inGroup = false;
		if(is_numeric($model->assignedTo)) { // we have an action assigned to a group, check if we are in the group
			$inGroup = Groups::inGroup(Yii::app()->user->id, $model->assignedTo);
		}
		
		if(Yii::app()->user->getName()==$model->assignedTo || $model->assignedTo=='Anyone' || $model->assignedTo=="" || $inGroup || Yii::app()->user->checkAccess('AdminIndex')) {
			
			if(isset($_POST['note']) && !empty($_POST['note']))
				$model->actionDescription = $model->actionDescription."\n\n".$_POST['note'];
				
			// $model = $this->updateChangelog($model,'Completed');
			$model->complete();
			
			// Actions::completeAction($id);
			// $this->completeNotification('admin',$model->id);
			
			$createNew = isset($_GET['createNew']) || ((isset($_POST['submit']) && ($_POST['submit']=='completeNew')));
			$redirect = isset($_GET['redirect']) || $createNew;
			
			if($redirect) {
				if($model->associationType!='none' && !$createNew) {	// if the action has an association
					$this->redirect(array('/'.$model->associationType.'/'.$model->associationType.'/view','id'=>$model->associationId));	// go back to the association
				} else {	// no association
					if($createNew)
						$this->redirect(array('/actions/create'));		// go to blank 'create action' page
					else
						$this->redirect(array('/actions/'.$model->id));	// view the action
				}
			} else {
				$this->redirect(array('/actions/'.$model->id)); 
			}
		} else {
			$this->redirect(array('/actions/invalid'));
		}
	}
	
	/**
	 * Marks an action as incomplete and clears the completedBy field.
	 * @param integer $id The id of the action
	 */
	public function actionUncomplete($id) {
		$model = $this->loadModel($id);
		
		if($model->uncomplete()) {
			if(isset($_GET['redirect'])) {
				if($model->associationType!='none')
					$this->redirect(array('/'.$model->associationType.'/'.$model->associationType.'/'.$model->associationId));
				else
					$this->redirect(array('/actions/'.$id));
			} else {
				$this->redirect(array('/actions/'.$id));
			}
		} else {
			print_r($model->getErrors());
		}
	}
	
	/**
	 * Called when a Contact opens an email sent from Inline Email Form. Inline Email Form
	 * appends an image to the email with src pointing to this function. This function
	 * creates an action associated with the Contact indicating that the email was opened. 
	 *
	 * @param integer $uid The unique id of the recipient
	 * @param string $type 'open', 'click', or 'unsub'
	 *
	 */
	public function actionEmailOpened($uid, $type) {
		if($type == 'open') {
			$track = TrackEmail::model()->findByAttributes(array('uniqueId'=>$uid));
			if($track && $track->opened == null) {
				$action = $track->action;
				if($action) {
					$note = new Actions;
					$note->type = 'emailOpened';
					$now = time();
					$note->createDate = $now;
					$note->lastUpdated = $now;
					$note->completeDate = $now;
					$note->complete = 'Yes';
					$note->updatedBy = 'admin';
					$note->associationType = $action->associationType;
					$note->associationId = $action->associationId;
					$note->associationName = $action->associationName;
					$note->visibility = $action->visibility;
					$note->assignedTo = $action->assignedTo;
					$note->actionDescription = Yii::t('marketing','Contact has opened the email sent on ');
					$note->actionDescription .= X2Model::formatLongDateTime($action->createDate) . "<br>";
					$note->actionDescription .= $action->actionDescription;
					if($note->save()) {
						$event=new Events;
						$event->type='email_opened';
						$contact=X2Model::model('Contacts')->findByPk($action->associationId);
						if(isset($contact)){
							$event->user=$contact->assignedTo;
						}
						$event->associationType='Contacts';
						$event->associationId=$note->associationId;
						if($action->associationType=='services'){
							$case=X2Model::model('Services')->findByPk($action->associationId);
							if(isset($case) && is_numeric($case->contactId)){
								$event->associationId=$case->contactId;
							}
						}
						$event->save();
						$track->opened = $now;
						$track->update();
					}
				}
			}
			
			//return a one pixel transparent png
			header('Content-Type: image/png');
			echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAAXNSR0IArs4c6QAAAAJiS0dEAP+Hj8y/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAC0lEQVQI12NgYAAAAAMAASDVlMcAAAAASUVORK5CYII=');
		}
	}
	
	// Lists all actions assigned to this user
	public function actionIndex() {
		$model=new Actions('search');
		$this->render('index', array('model'=>$model));
	}

	// List all public actions
	public function actionViewAll() {
		$model=new Actions('search');
		$this->render('index', array('model'=>$model));
	}
	
	public function actionViewGroup() {
		$model=new Actions('search');
		$this->render('index', array('model'=>$model));
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
		$type = $_GET['type'];
		if($type!='none' && ctype_alpha($type)) {
			$sql = 'SELECT id, name as value FROM x2_'.$type.' WHERE name LIKE :qterm ORDER BY name ASC';
			$command = Yii::app()->db->createCommand($sql);
			$qterm = $_GET['term'].'%';
			$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
			$result = $command->queryAll();
			echo CJSON::encode($result); exit;
		} else {
			echo array('0'=>'None');
		}
	}

	public function getAssociation($type,$id) {
		return X2Model::getAssociationModel($type,$id);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model = CActiveRecord::model('Actions')->findByPk((int)$id);
		//$dueDate=$model->dueDate;
		//$model=Actions::changeDates($model);
		// if($model->associationId!=0) {
			// $model->associationName = $this->parseName(array($model->associationType,$model->associationId));
		// } else
			// $model->associationName = 'None';
		
		if($model === null)
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
