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
				'actions'=>array('click'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform the following actions
				'actions'=>array('index','view','create','update','search','delete','launch','toggle','complete','getItems','inlineEmail','mail'),
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
			$model->createdBy = Yii::app()->user->getName();
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
			if (isset($list) && $list->type == "campaign") 
				$list->delete();
			$this->cleanUpTags($model);
			$model->delete();

			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax'])) {
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
			}
		}
		else {
			throw new CHttpException(400,Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Campaign('search');
		$name='Campaign';
		parent::index($model,$name);
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin() {
		$model=new Campaign('search');
		$name='Campaign';
		parent::admin($model, $name);
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id) {
		$model=Campaign::model()->with('list')->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,Yii::t('app', 'The requested page does not exist.'));
		return $model;
	}

	public function actionLaunch($id) {
		$campaign = $this->loadModel($id);

		if(!isset($campaign->list)) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','Contact List cannot be blank.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if (empty($campaign->subject)) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','Subject cannot be blank.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if ($campaign->launchDate != 0 && $campaign->launchDate < time()) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','The campaign has already been launched.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if (CActiveRecord::model($campaign->list->modelName)->count($campaign->list->dbCriteria()) < 1) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','The contact list is empty.'));
			$this->redirect(array('view', 'id'=>$id));
		}
		
		//Duplicate the list for campaign tracking, leave original untouched
		$newList = $campaign->list->staticDuplicate();
		$newList->type = 'campaign';
		$newList->save();

		$campaign->list = $newList;
		$campaign->listId = $newList->id;
		$campaign->launchDate = time();
		$campaign->save();
		
		Yii::app()->user->setFlash('success', Yii::t('marketing','Campaign launched'));
		$this->redirect(array('view', 'id'=>$id));
	}
	
	/**
	 * Deactivate a campaign to halt mailings, or resume paused campaign
	 */
	public function actionToggle($id) {
		$campaign = $this->loadModel($id);
		$campaign->active = $campaign->active ? 0 : 1;
		$campaign->save();
		$message = $campaign->active ? Yii::t('marketing','Campaign resumed') : Yii::t('marketing','Campaign paused');
		Yii::app()->user->setFlash('notice', Yii::t('app', $message));
		$this->redirect(array('view', 'id'=>$id));
	}
	
	/**
	 * Forcibly complete a campaign despite any unsent mail
	 */
	public function actionComplete($id) {
		$campaign = $this->loadModel($id);
		$campaign->active = 0;
		$campaign->complete = 1;
		$campaign->save();
		$message = Yii::t('marketing','Campaign complete.') ;
		Yii::app()->user->setFlash('notice', Yii::t('app', $message));
		$this->redirect(array('view', 'id'=>$id));
	}

	/**
	 * Public action to access processMailing from ajax or otherwise
	 */
	public function actionMail($id=null) {
		$batchSize = Yii::app()->params->admin->emailBatchSize;
		$interval = Yii::app()->params->admin->emailInterval;
		$now = time();
		$wait = $interval * 60;
		$messages = array();
		try {
			//count all list items that were sent within last interval
			$sendCount = X2ListItem::model()->count('sent > :time', array('time'=>($now - $interval * 60)));
		
			//TODO: currently this only takes into account campaign mail sending,
			//other types of mail do not count against the batch limit
			$sendLimit = $batchSize - $sendCount;
			if ($sendLimit < 1) {
			  throw new Exception('The email sending limit has been reached.');
			}

			//get all campaigns that could use mailing
			$campaigns = Campaign::model()->with('list')->findAllByAttributes(
				array('complete'=>0, 'active'=>1, 'type'=>'Email'), 
				'launchdate > 0 AND launchdate < :time',
				array(':time'=>time()));

			if (count($campaigns) == 0) { 
				throw new Exception('There is no campaign email to send.');
			}

			$totalSent = 0;
			foreach($campaigns as $campaign) {
				if ($totalSent >= $sendLimit) break;

				try {
					list($sent, $errors) = $this->campaignMailing($campaign, $sendLimit-$totalSent);
				} catch (Exception $e) {
					if ($campaign->id == $id) $messages[] = $e->getMessage();
					continue;
				}

				$totalSent += $sent;

				//return status messages for the campaign specified in the request
				if ($campaign->id == $id && $sent > 0) {
					//$messages = array_merge($messages, $errors);

					//count the number of contacts we can't send to
					$criteria = $campaign->list->dbCriteria();
					$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
					         ->addCondition('t.email IS NULL OR t.email=""');
					$blankEmail = CActiveRecord::model('Contacts')->count($criteria);

					//count the number of contacts who don't want email 
					$criteria = $campaign->list->dbCriteria();
					$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
					         ->addCondition('t.doNotEmail=1');
					$doNotEmail = CActiveRecord::model('Contacts')->count($criteria);
					
					$errorCount = count($errors); 

					$unsendable = $blankEmail + $doNotEmail + $errorCount;
					
					$messages[] = '';
					if ($totalSent >= $sendLimit)
						$messages[] = Yii::t('marketing','Batch completed, sending again in '). $interval .' '. Yii::t('marketing','minutes').'...';
					if ($errorCount > 0) $messages[] = '&nbsp;'. Yii::t('marketing','Data errors') .': '. $errorCount;
					if ($doNotEmail > 0) $messages[] = '&nbsp;'. Yii::t('marketing','\'Do Not Email\' contacts') .': '. $doNotEmail;
					if ($blankEmail > 0) $messages[] = '&nbsp;'. Yii::t('marketing','Blank email addresses') .': '. $blankEmail;
					if ($unsendable > 0) $messages[] = Yii::t('marketing','Unsendable email') .': '. $unsendable;
					$messages[] = Yii::t('marketing','Successful email sent') .': '. $sent;
					if ($campaign->complete) $messages[] = Yii::t('marketing','Campaign complete.'); 
				}
			}
			//return general messsages if no specific campaign
			if ($id == null) {
				if ($totalSent > 0) {
					$messages[] = Yii::t('marketing','Email sent') .': '. $totalSent;
				} else {
					$messages[] = Yii::t('marketing','No email sent.');
				}
			}
		} catch (Exception $e) {
			$messages[] = $e->getMessage();
		}

		echo CJSON::encode(array('wait'=>$wait, 'messages'=>$messages));
	}

	protected function processMailing($limit, $id=null) {
		//per request batch limits, dont send enough to timeout
		//per log cycle batch, 10 at a time or so to reduce logging sent time queries
		//Timeouts? make sure each mail is logged individually, not waiting for the batch to finish
		//ENSURE no duplicate mail
	}

	protected function campaignMailing($campaign, $limit=null) {
		$totalSent = 0;
		$errors = array();
	
		//get eligible contacts from the campaign
		$criteria = $campaign->list->dbCriteria();
		$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
		         ->addCondition('t.email IS NOT NULL')->addCondition('t.email!=""')
		         ->addCondition('t.doNotEmail=0');
		$contacts = CActiveRecord::model('Contacts')->findAll($criteria);

		//setup campaign email settings
		try {
			$phpMail = $this->getPhpMailer();
			$fromEmail = Yii::app()->params->admin->emailFromAddr;
			$fromName = Yii::app()->params->admin->emailFromName;
			$phpMail->AddReplyTo($fromEmail, $fromName);
			$phpMail->SetFrom($fromEmail, $fromName);
			$phpMail->Subject = $campaign->subject;
		} catch (Exception $e) {
			throw $e;
		}
		
		//prepare the list item update query to be used many times later
		$sql = 'UPDATE x2_list_items SET sent=:sent, uniqueId=:uid WHERE contactId=:cid AND listId=:lid;';
		$itemUpdateCmd = Yii::app()->db->createCommand($sql);

		foreach($contacts as $contact) {
			try {
				//only send up to the specified limit
				if ($limit && $totalSent >= $limit) break;

				$now = time();
				$uniqueId = md5(uniqid(rand(), true));
				$emailBody = $campaign->content;

				//if there is no unsubscribe link placeholder, add default
				if (!preg_match('/\{_unsub\}/', $campaign->content)) {
					$unsubText = "<br/>\n-----------------------<br/>\n"
					            ."To stop receiving these messages, click here: {_unsub}";
					$emailBody .= $unsubText;
				}

				//TODO: email template
				// $templateDoc = CActiveRecord::model('Docs')->findByPk($model->template);
				// if(isset($templateDoc)) {
				//TODO: replace email variables, tracking urls
				//$emailBody = str_replace('\\\\', '\\\\\\', $emailBody);
				//$emailBody = str_replace('$', '\\$', $emailBody);

				//TODO: contact field placeholders
				//$attributeNames = array_keys($contact->getAttributes());
				//$attributes = array_values($contact->getAttributes());
				// $attributeNames[] = 'content';
				// $attributes[] = $model->message;
				//foreach($attributeNames as &$name)
					//$name = '/{'.$name.'}/';
				//unset($name);
				//$emailBody = preg_replace($attributeNames,$attributes,$emailBody);

				$emailBody = x2base::convertUrls($emailBody, false);
				
				//replace existing links with tracking links
				$url = $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'click')); 
				//profane black magic
				$emailBody = preg_replace(
					'/(<a[^>]*href=")([^"]*)("[^>]*>)/e', "(\"\\1" . $url . "&url=\" . urlencode(\"\\2\") . \"\\3\")", $emailBody);
				
				//insert unsubscribe links
				$emailBody = preg_replace(
					'/\{_unsub\}/', 
					'<a href="' . $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'unsub')) . '">'. Yii::t('marketing', 'unsubscribe') .'</a>', 
					$emailBody); 

				$emailBody .= '<img src="' . $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'open')) . '"/>';

				$phpMail->ClearAllRecipients();
				$phpMail->AddAddress($contact->email, $contact->name);
				$phpMail->MsgHTML($emailBody);
				$phpMail->Send();
				$totalSent++;

				//record campaignid, contactid, senttime, uniqueid to save into listitem
				$itemUpdateCmd->bindValues(array(':cid'=>$contact->id, ':lid'=>$campaign->list->id, ':sent'=>$now, ':uid'=>$uniqueId))
					->execute();

				//create action for this email
				$action = new Actions;
				$action->associationType = 'contacts';
				$action->associationId = $contact->id;
				$action->associationName = $contact->name;
				$action->visibility = $contact->visibility;
				$action->complete = 'Yes';
				$action->type = 'email';
				$action->completedBy = Yii::app()->user->getName();
				$action->assignedTo = $contact->assignedTo;
				$action->createDate = $now;
				$action->dueDate = $now;
				$action->completeDate = $now;
				//if($template == null)
				$action->actionDescription = '<b>Campaign: '.$campaign->name."</b>\n\nSubject: ".$campaign->subject."\n\n".$campaign->content;
				//else
					//$action->actionDescription = CHtml::link($template->title,array('/docs/'.$template->id));
				
				$action->save();

			} catch (Exception $e) {
				$errors[] = Yii::t('marketing','Error for contact') .' '. $contact->name .': '. $e->getMessage();
			}
		}

		//check if campaign is complete
		//TODO: consider contacts with unsendable addresses
		$tables = X2ListItem::model()->tableName() . ' as li,' . Contacts::model()->tableName() . ' as c';
		$totalCount = Yii::app()->db->createCommand('SELECT COUNT(*) FROM '. $tables .' WHERE li.contactId = c.id AND c.doNotEmail=0 AND li.listId = :listid')
				->queryScalar(array('listid'=>$campaign->list->id));
		$sentCount = Yii::app()->db->createCommand('SELECT COUNT(*) FROM '. $tables .' WHERE li.contactId = c.id AND c.doNotEmail=0 AND li.listId = :listid AND li.sent > 0')
				->queryScalar(array('listid'=>$campaign->list->id));
		if ($totalCount == $sentCount) {
			$campaign->active = 0;
			$campaign->complete = 1;
			$campaign->save();
		}

		return array($totalSent, $errors);
	}

	public function actionClick($uid, $type, $url=null) {
		$now = time();
		$item = X2ListItem::model()->with('contact')->findByAttributes(array('uniqueId'=>$uid));
		if (!isset($item))
			return;

		$campaign = Campaign::model()->findByAttributes(array('listId'=>$item->listId));
		if (!isset($campaign)) //overkill, but should never happen
			return;

		$action = new Actions;
		$action->associationType = 'contacts';
		$action->associationId = $item->contact->id;
		$action->associationName = $item->contact->name;
		$action->visibility = $item->contact->visibility;
		$action->complete = 'Yes';
		$action->type = 'note';
		$action->completedBy = Yii::app()->user->getName();
		$action->assignedTo = $item->contact->assignedTo;
		$action->createDate = $now;
		$action->dueDate = $now;
		$action->completeDate = $now;
		
		if ($type == 'unsub') {
			$item->contact->doNotEmail = true;
			$item->contact->save();
			if ($item->unsubscribed == 0) $item->unsubscribed = $now;
			if ($item->opened == 0) $item->opened = $now;
			$item->save();

			$action->actionDescription = '<b>Campaign: '.$campaign->name."</b>\n\nContact has unsubscribed.\n'Do Not Email' has been set.";
			$action->save();

			echo 'You have been unsubscribed';
		} else if ($type == 'open') {
			if ($item->opened == 0) $item->opened = $now;
			$item->save();

			$action->actionDescription = '<b>Campaign: '.$campaign->name."</b>\n\nContact has opened the email.";
			$action->save();

			//return a one pixel transparent png
			header('Content-Type: image/png');
			echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAAXNSR0IArs4c6QAAAAJiS0dEAP+Hj8y/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAC0lEQVQI12NgYAAAAAMAASDVlMcAAAAASUVORK5CYII=');
		} else if ($type == 'click') {
			if ($item->clicked == 0) $item->clicked = $now;
			if ($item->opened == 0) $item->opened = $now;
			$item->save();

			$action->actionDescription = '<b>Campaign: '.$campaign->name."</b>\n\nContact has clicked a link:\n". urldecode($url);
			$action->save();

			$this->redirect(urldecode($url));	
		}
	}
}
