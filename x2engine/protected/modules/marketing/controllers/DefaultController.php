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
	public $modelClass = 'Campaign';
	
	public function accessRules() {
		return array(
			array('allow',  // allow all users
				'actions'=>array('track'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform the following actions
				'actions'=>array('index','view','create','update','search','delete','launch','getItems','inlineEmail'),
				'users'=>array('@'),
			),
			array('allow', // allow admin user to perform 'admin' action
				'actions'=>array('admin'),
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
		
	public function actionGetItems() {
		$sql = 'SELECT id, name as value FROM x2_campaigns WHERE name LIKE :qterm ORDER BY name ASC';
		$command = Yii::app()->db->createCommand($sql);
		$qterm = '%'.$_GET['term'].'%';
		$command->bindParam(":qterm", $qterm, PDO::PARAM_STR);
		$result = $command->queryAll();
		echo CJSON::encode($result); exit;
	}
	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {

		$model=$this->loadModel($id);
		if(!isset($model))
			return;
			
		$contactList = null;
		if(!empty($model->listId))
			$contactList = CActiveRecord::model('X2List')->findByPk($model->listId);

		$this->render('view',array(
			'model'=>$model,
			'contactList'=>$contactList,
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model=new Campaign;
		if(isset($_POST['Campaign'])) {
			$oldAttributes = $model->attributes;
			$model->setX2Fields($_POST['Campaign']);
			parent::create($model, $oldAttributes,0);
		}
		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		
		if(isset($_POST['Campaign'])) {
			$oldAttributes = $model->attributes;
			$model->setX2Fields($_POST['Campaign']);
			parent::update($model,$oldAttributes,0);
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			// we only allow deletion via POST request
			$model=$this->loadModel($id);
			$list = X2List::model()->findByPk($model->listId);
			if ($list->type == "campaign") 
				$list->delete();
			$this->cleanUpTags($model);
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
		}
		else {
			throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Campaign('search');
		//hack until mass assignment problem solved
		$model->active = null;
		$model->complete = null;
		$model->launched = null;
		$name='Campaign';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new Campaign('search');
		//hack until mass assignment problem solved
		$model->active = null;
		$model->complete = null;
		$model->launched = null;
		$name='Campaign';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Campaign::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	/*protected function performAjaxValidation($model) {
		if(isset($_POST['ajax']) && $_POST['ajax']==='marketing-form') {
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}*/
	
	public function actionLaunch($id) {
		$messages = '';
		$status ='';
		$errors = array();
		
		$campaign = $this->loadModel($id);
		if (!isset($campaign)) {
			throw new CHttpException(404,'The requested page does not exist.');
		}

		if(!ctype_digit($campaign->listId)) {
			$errors[] = Yii::t('app','This campaign has no target contact list.');
			$this->render('view', array('model'=>$campaign, 'errors'=>$errors));
		}

		$list = X2List::model()->findByPk($campaign->listId);

		//already launched
		if ($campaign->launched) {
			$errors[] = Yii::t('app','This campaign has already been launched.');
			$this->render('view',array(
				'model'=>$campaign,
				'errors'=>$errors,
				'contactList'=>$list,
			));
			return;
		}

		if (CActiveRecord::model($list->modelName)->count($list->dbCriteria()) < 1) {
			$errors[] = Yii::t('app','The contacts list is empty.');
			$this->render('view', array('model'=>$campaign, 'errors'=>$errors));
			return;
		}
		
		if(empty($campaign->subject)) {
			$errors[] = Yii::t('app','The subject is empty.');
			$this->render('view', array('model'=>$campaign, 'errors'=>$errors));
			return;
		}

		//Campaign is launching, a point of no return
		//After launching, the campaign becomes read only

		//Duplicate the list for campaign tracking, leave original untouched
		$campaignList = $list->staticDuplicate();
		$campaignList->type = 'campaign';
		//give each item a uniqueId for tracking
		$campaignList->save();

		$campaign->listId = $campaignList->id;
		//TODO: modify to support future launching
		$campaign->launched = 1;
		$campaign->launchDate = time();
		$campaign->save();
		
		$this->render('view',array(
			'model'=>$campaign,
			'errors'=>$errors,
			'contactList'=>$campaignList,
		));
	}

	public function processMailing() {
		$messages = '';
		$status ='';
		$errors = array();

		$phpMail = $this->getPhpMailer();
		
		$user = CActiveRecord::model('User')->findByPk(Yii::app()->user->getId());
		
		try {
			if(empty(Yii::app()->params->profile->emailAddress))
				throw new Exception('<b>'.Yii::t('app','Your profile doesn\'t have a valid email address.').'</b>');
			
			$phpMail->AddReplyTo(Yii::app()->params->admin->emailFromAddr,$user->name);
			$phpMail->SetFrom(Yii::app()->params->admin->emailFromAddr,$user->name);
			$phpMail->Subject = $campaign->subject;
			
			if($testEmail)
				$phpMail->Subject = Yii::t('marketing','Test Email: ').$phpMail->Subject;
		
		} catch (phpmailerException $e) {
			$errors[] = $e->errorMessage();
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}
		
		foreach($contacts as &$contact) {
			$phpMail->ClearAllRecipients();
		
			$emailBody = $campaign->content;
			// $templateDoc = CActiveRecord::model('Docs')->findByPk($model->template);
			// if(isset($templateDoc)) {
			$emailBody = str_replace('\\\\', '\\\\\\', $emailBody);
			$emailBody = str_replace('$', '\\$', $emailBody);


			$attributeNames = array_keys($contact->getAttributes());
			$attributes = array_values($contact->getAttributes());
			// $attributeNames[] = 'content';
			// $attributes[] = $model->message;
			foreach($attributeNames as &$name)
				$name = '/{'.$name.'}/';
			unset($name);

			$emailBody = preg_replace($attributeNames,$attributes,$emailBody);

			try {
				$phpMail->AddAddress(Yii::app()->params->profile->emailAddress,$user->name);

				// $phpMail->AltBody = $message;
				$phpMail->MsgHTML($emailBody);
				// $phpMail->Body = $message;
				//$phpMail->Send();

				$messages .= 'Dispatched spam to '.$contact->name.'<br>';
				$status = Yii::t('app','Email Sent!');

			} catch (phpmailerException $e) {
				$errors[] = $e->errorMessage(); //Pretty error messages from PHPMailer
			} catch (Exception $e) {
				$errors[] = $e->getMessage(); //Boring error messages from anything else!
			}
		}
		$messages .= $status;
	}
	
	public function actionTrack() {
		if(isset($_GET['c']) && ctype_digit($_GET['c'])) {
		
		}
	}
}
