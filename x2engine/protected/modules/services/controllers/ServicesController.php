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
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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

/**
 * @package X2CRM.modules.services.controllers 
 */
class ServicesController extends x2base { 

	public $modelClass = 'Services';
	public $serviceCaseStatuses = null;

	public function accessRules() {
		return array(
                        array('allow',
                            'actions'=>array('getItems','webForm'),
                            'users'=>array('*'), 
                        ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','view','create','update','search','saveChanges','delete','inlineEmail','createWebForm', 'statusFilter'),
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
	
	public function behaviors() {
		return array(
			'ServiceRoutingBehavior'=>array(
				'class'=>'ServiceRoutingBehavior'
			)
		);
	}

	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model=$this->loadModel($id);	 
		
		
		$type='services';
		parent::view($model, $type);
	}
	

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function create($model,$oldAttributes, $api){
//		$model->annualRevenue = $this->parseCurrency($model->annualRevenue,false);
		$model->createDate=time();
		$model->lastUpdated=time();
		$model->updatedBy = Yii::app()->user->name;
		if($api==0) {
		     parent::create($model,$oldAttributes,'1');
		     if( !$model->isNewRecord ) {
		     	$model->name = $model->id;
		     	$model->update();
				if($model->escalatedTo != '') {
                    $event=new Events;
                    $event->type='case_escalated';
                    $event->level=2;
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
	}

	public function actionCreate() {
		$model=new Services;
		$users=User::getNames();
		unset($users['admin']);
		unset($users['']);
		foreach(Groups::model()->findAll() as $group) {
			$users[$group->id]=$group->name;
		}

		if(isset($_POST['Services'])) {
			$temp=$model->attributes;
			foreach($_POST['Services'] as $name => &$value) {
			if($value == $model->getAttributeLabel($name))
				$value = '';
			}
			$model->setX2Fields($_POST['Services']);
			if($model->contactId != '' && !is_numeric($model->contactId)) // make sure an existing contact is associated with this case, otherwise don't create it
				$model->addError('contactId', Yii::t('services', 'Contact does not exist'));
			if(isset($_POST['x2ajax'])) {
				if($this->create($model,$temp, '1')) { // success creating case?
		     		$model->name = $model->id;
		     		$model->update();
					if(isset($_POST['ModelName']) && isset($_POST['ModelId'])) {
						Relationships::create($_POST['ModelName'], $_POST['ModelId'], 'Services', $model->id);
					}

					echo json_encode(
						array(
							'status'=>'success',
							'name'=>$model->name,
							'id'=>$model->id,
						)
					);
					Yii::app()->end();
				} else {
					$x2ajaxCreateError = true;
				}
			} else {
				$this->create($model,$temp, '0');
	//			var_dump($model->errors);
			}
		}
		
		// set default options for dropdowns
		if(!isset($model->status) || $model->status == '') {
			$model->status = "New";
		}
		
		if(!isset($model->impact) || $model->impact == '') {
			$model->impact = "3 - Moderate";
		}
				
		if(isset($_POST['x2ajax'])) {
			Yii::app()->clientScript->scriptMap['*.js'] = false;
			Yii::app()->clientScript->scriptMap['*.css'] = false;
			if(isset($x2ajaxCreateError) && $x2ajaxCreateError == true) {
				$page = $this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'services'), true, true);
				echo json_encode(
					array(
						'status'=>'userError',
						'page'=>$page,
					)
				);
			} else {
				$this->renderPartial('application.components.views._form', array('model'=>$model, 'users'=>$users,'modelName'=>'services'), false, true);
			}
		} else {
			$this->render('create',array(
				'model'=>$model,
				'users'=>$users,
			));
		}

	}
        
	public function update($model, $oldAttributes,$api){
		
		$ret = parent::update($model,$oldAttributes,'1');
		
		if($model->escalatedTo != '' && $model->escalatedTo != $oldAttributes['escalatedTo']) {
            $event=new Events;
            $event->type='case_escalated';
            $event->level=2;
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
	}

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
		foreach(Groups::model()->findAll() as $group){
			$users[$group->id]=$group->name;
		}

		if(isset($_POST['Services'])) {
			$temp=$model->attributes;
			foreach($_POST['Services'] as $name => &$value) {
			if($value == $model->getAttributeLabel($name))
			  $value = null;
			}
			$model->setX2Fields($_POST['Services']);
			if($model->contactId != '' && !is_numeric($model->contactId)) // make sure an existing contact is associated with this case, otherwise don't create it
				$model->addError('contactId', Yii::t('services', 'Contact does not exist'));
			$this->update($model,$temp,'0');
		}

		$this->render('update',array(
			'model'=>$model,
			'users'=>$users,
		));
	}
        public function delete($id){
            
            $model=$this->loadModel($id);
            $dataProvider=new CActiveDataProvider('Actions', array(
                'criteria'=>array(
                    'condition'=>'associationId='.$id.' AND associationType=\'services\'',
            )));

            $actions=$dataProvider->getData();
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
	public function actionDelete($id) {
		$model=$this->loadModel($id);
		if(Yii::app()->request->isPostRequest) {
            $event=new Events;
            $event->type='record_deleted';
            $event->level=2;
            $event->associationType=$this->modelClass;
            $event->associationId=$model->id;
            $event->text=$model->name;
            $event->user=Yii::app()->user->getName();
            $event->save();
            Actions::model()->deleteAll('associationId='.$id.' AND associationType=\'services\'');
			$this->cleanUpTags($model);
			$model->delete();
		} else
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		
		$model=new Services('search');
		$this->render('index', array('model'=>$model));
	}
	
	public function actionGetItems(){
		$sql = 'SELECT id as value FROM x2_services WHERE id LIKE :qterm ORDER BY id ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = $_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
	
	
	/**
	 * Create a web lead form with a custom style
	 */
	public function actionCreateWebForm() {
		if(file_exists(__DIR__ . '/pro/actionCreateWebForm.php')) {
			include(__DIR__ . '/pro/actionCreateWebForm.php');
			return;
		}
	
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (empty($_POST['name'])) {
				echo json_encode(array('errors'=>array('name'=>Yii::t('marketing','Name cannot be blank.'))));
				return;
			}

			$type = 'serviceCase';
			$model = WebForm::model()->findByAttributes(array('name'=>$_POST['name'], 'type'=>$type));
			if (!isset($model)) {
				$model = new WebForm;
				$model->name = $_POST['name'];
				$model->type = $type;
				$model->modelName = 'Services';
				$model->visibility = 1;
				$model->assignedTo = Yii::app()->user->getName();
				$model->createdBy = Yii::app()->user->getName();
				$model->createDate = time();
			}

			//grab web lead configuration and stash in 'params'
			$whitelist = array('fg', 'bgc', 'font', 'bs', 'bc', 'tags');
			$config = array_filter(array_intersect_key($_POST, array_flip($whitelist)));
			//restrict param values, alphanumeric, # for color vals, comma for tag list
			$config = preg_replace('/[^a-zA-Z0-9#,]/', '', $config);
			if (!empty($config)) $model->params = $config;
			else $model->params = null;

			$model->updatedBy = Yii::app()->user->getName();
			$model->lastUpdated = time();

			if ($model->save()) {
				echo json_encode($model->attributes);
			} else {
				echo json_encode(array('errors'=>$model->getErrors()));
			}
		} else {
			if(Yii::app()->user->getName()!='admin') {
				$condition = ' AND visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
				/* x2temp */
				$groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
				if(!empty($groupLinks))
					$condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';

				$condition .= ' OR (visibility=2 AND assignedTo IN 
					(SELECT username FROM x2_group_to_user WHERE groupId IN
					(SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
			} else {
				$condition='';
			}
			//this get request is for weblead type only, marketing/weblist/view supplies the form that posts for weblist type
			$forms = WebForm::model()->findAll('type="serviceCase"'.$condition);
			$this->render('createWebForm', array('forms'=>$forms));
		}
	}
	
	
	public function actionWebForm() {
		if(file_exists(__DIR__ . '/pro/actionWebForm.php')) {
			include(__DIR__ . '/pro/actionWebForm.php');
			return;
		}
	
		if (isset($_POST['Services'])) {			
			$firstName = $_POST['Services']['firstName'];
			$lastName = $_POST['Services']['lastName'];
			$fullName = $firstName . ' ' . $lastName;
			$email = $_POST['Services']['email'];
			$phone = $_POST['Services']['phone'];
			$description = $_POST['Services']['description'];
			
			$model = new Services;
			$oldAttributes = $model->getAttributes();
			
			$contact = Contacts::model()->findByAttributes(array('email'=>$email));
			if($contact) {
				$model->contactId = $contact->id;
			} else {
				$model->contactId = "Unregistered";
			}
			
			$model->subject = Yii::t('services', 'Web Form Case entered by {name}', array(
				'{name}'=>$fullName,
			));
			
			$model->description = $description;

			$model->origin = 'Web';
			$model->impact = Yii::t('services', '3 - Moderate');
			$model->status = Yii::t('services', 'New');
			$model->mainIssue = Yii::t('services', 'General Request');
			$model->subIssue = Yii::t('services', 'Other');
			$model->assignedTo = $this->getNextAssignee();
			$now = time();
			$model->createDate = $now;
			$model->lastUpdated = $now;
			$model->updatedBy = 'admin';
			if($this->create($model, $oldAttributes, 1)) {
				$model->name = $model->id;
				$model->update();
			}
					
			
			// add tags
			if(!empty($_POST['tags'])) {
				$taglist = explode(',', $_POST['tags']);
				if($taglist !== false) {
					foreach($taglist as &$tag) {
						if($tag === '')
							continue;
                        if(substr($tag,0,1)!='#')
                            $tag="#".$tag;
						$tagModel = new Tags;
						$tagModel->taggedBy = 'API';
						$tagModel->timestamp = time();
						$tagModel->type = 'Services';
						$tagModel->itemId = $model->id;
						$tagModel->tag = $tag;
						$tagModel->itemName = $model->name;
						$tagModel->save();
					}
				}
			}
		
			//use the submitted info to create an action
			$action = new Actions;
			$action->actionDescription = Yii::t('contacts','Web Form') ."\n\n".
				Yii::t('contacts','Name') .': '. $fullName ."\n".
				Yii::t('contacts','Email') .": ". $email ."\n".
				Yii::t('contacts','Phone') .": ". $phone ."\n".
				Yii::t('services','Description') .": ". $description;
			
			// create action
			$action->type = 'note';
			$action->assignedTo = $model->assignedTo;
			$action->visibility = '1';
			$action->associationType = 'services';
			$action->associationId = $model->id;
			$action->associationName = $model->name;
			$action->createDate = $now;
			$action->lastUpdated = $now;
			$action->completeDate = $now;
			$action->complete= 'Yes';
			$action->updatedBy = 'admin';
			$action->save();
			
			//send email
			$emailBody = Yii::t('services', 'Hello'). ' ' . $fullName . ",<br><br>";
			$emailBody .= Yii::t('services', 'Thank you for contacting our Technical Support team. This is to verify we have received your request for Case# {casenumber}.  One of  our Technical Analysts will contact you shortly.', array(
				'{casenumber}'=>$model->id,
			));
			
			$emailBody = Yii::app()->params->admin->serviceCaseEmailMessage;
			$emailBody = preg_replace('/{first}/u', $firstName, $emailBody);
			$emailBody = preg_replace('/{last}/u', $lastName, $emailBody);
			$emailBody = preg_replace('/{phone}/u', $phone, $emailBody);
			$emailBody = preg_replace('/{email}/u', $email, $emailBody);
			$emailBody = preg_replace('/{description}/u', $description, $emailBody);
			$emailBody = preg_replace('/{case}/u', $model->id, $emailBody);
			$emailBody = preg_replace('/\n|\r\n/', "<br>", $emailBody);
			
			$uniqueId = md5(uniqid(rand(), true));
			$emailBody .= '<img src="' . $this->createAbsoluteUrl('actions/emailOpened', array('uid'=>$uniqueId, 'type'=>'open')) . '"/>';
			
			$emailSubject = Yii::app()->params->admin->serviceCaseEmailSubject;
			$emailSubject = preg_replace('/{first}/u', $firstName, $emailSubject);
			$emailSubject = preg_replace('/{last}/u', $lastName, $emailSubject);
			$emailSubject = preg_replace('/{phone}/u', $phone, $emailSubject);
			$emailSubject = preg_replace('/{email}/u', $email, $emailSubject);
			$emailSubject = preg_replace('/{description}/u', $description, $emailSubject);
			$emailSubject = preg_replace('/{case}/u', $model->id, $emailSubject);
			
			$from = array('name' => Yii::app()->params->admin->serviceCaseFromEmailName, 'address'=> Yii::app()->params->admin->serviceCaseFromEmailAddress);
			$status = $this->sendUserEmail(array($fullName, $email), $emailSubject, $emailBody, null, $from);
			
			if($status[0] == 200) {
				//email action
				$action = new Actions;
				$action->associationType = 'services';
				$action->associationId = $model->id;
				$action->associationName = $model->name;
				$action->visibility = 1;
				$action->complete = 'Yes';
				$action->type = 'email';
				$action->completedBy = 'admin';
				$action->assignedTo = $model->assignedTo;
				$action->createDate = time();
				$action->dueDate = time();
				$action->completeDate = time();
				$action->actionDescription = '<b>'.$model->subject."</b>\n\n".$emailBody;
				if($action->save()) {
				    $track = new TrackEmail;
				    $track->actionId = $action->id;
				    $track->uniqueId = $uniqueId;
				    $track->save();
				}
			}
			
			$this->renderPartial('webFormSubmit', array('caseNumber'=>$model->id));
			
		} else {
			//sanitize get params
			$whitelist = array('fg', 'bgc', 'font', 'bs', 'bc', 'tags');
			$_GET = array_intersect_key($_GET, array_flip($whitelist));
			//restrict param values, alphanumeric, # for color vals, comma for tag list
			$_GET = preg_replace('/[^a-zA-Z0-9#,]/', '', $_GET);
	
			$this->renderPartial('webForm', array('type'=>'webForm'));
		}
	}
	
	/**
	 *  Show or hide a certain status in the gridview
	 *
	 *  Called through ajax with a status and if that status should be shown or hidden.
	 *  Saves the result in the user's profile.
	 *
	 */
	public function actionStatusFilter() {
	//	var_dump($_POST);
		$checked = CJSON::decode($_POST['checked']);
		$status = $_POST['status'];
		
		var_dump($checked);
		var_dump($status);
		
		$hideStatuses = CJSON::decode(Yii::app()->params->profile->hideCasesWithStatus); // get a list of statuses the user wants to hide
		if(!$hideStatuses) {
			$hideStatuses = array();
		}
		
		var_dump($checked);
		var_dump(in_array($status, $hideStatuses));
		if($checked && ($key = array_search($status, $hideStatuses)) !== false) { // if we want to show the status, and it's not being shown
			unset($hideStatuses[$key]); // show status
		} else if(!$checked && !in_array($status, $hideStatuses)) { // if we want to hide the status, and it's not being hidden
			$hideStatuses[] = $status;
		}
		
		Yii::app()->params->profile->hideCasesWithStatus = CJSON::encode($hideStatuses);
		Yii::app()->params->profile->update();
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Services::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
}
