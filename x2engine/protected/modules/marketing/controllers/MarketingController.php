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
 * Controller to handle creating and mailing campaigns.
 *
 * @package X2CRM.modules.marketing.controllers
 */
class MarketingController extends x2base {
	public $modelClass = 'Campaign';
	
	public function accessRules() {
		return array(
			array('allow',  // allow all users
				'actions'=>array('click'),
				'users'=>array('*'),
			),
			array('allow', // allow authenticated user to perform the following actions
				'actions'=>array('index','view','create','createFromTag','update','search','delete','launch','toggle','complete','getItems',
					'inlineEmail','mail','webleadForm'),
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

	/**
	 * Returns a JSON array of the names of all campaigns filtered by a search term.
	 * 
	 * @return string A JSON array of strings
	 */
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
	 *
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id) {
		$model = $this->loadModel($id);

		if(!isset($model)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		if(isset($model->list)) {
			//set this as the list we are viewing, for use by vcr controls
			Yii::app()->user->setState('contacts-list', $model->list->id);
		}
		$this->view($model,'marketing',array('contactList'=>$model->list));
	}
	
	/**
	 * Displays the content field (email template) for a particular model.
	 *
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionViewContent($id) {
		$model = $this->loadModel($id);
		
		if(!isset($model)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		if($model->template != 0) {
			$template = X2Model::model('Docs')->findByPk($model->template);
			if(isset($template))
				$model->content = $template->text;
		}
		echo $model->content;
	}
    
    public function loadModel($id) {
		return Campaign::load($id);
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate() {
		$model = new Campaign;
		$model->type = 'Email'; //default choice for now

		if(isset($_POST['Campaign'])) {
			$oldAttributes = $model->attributes;
			$model->setX2Fields($_POST['Campaign']);
			if($model->template != 0)
				$model->content = '';
			$model->createdBy = Yii::app()->user->getName();
			if($model->save()) {
				if(isset($_POST['AttachmentFiles'])) {
					if(isset($_POST['AttachmentFiles']['id'])) {
						foreach($_POST['AttachmentFiles']['id'] as $mediaId) {
							$attachment = new CampaignAttachment;
							$attachment->campaign = $model->id;
							$attachment->media = $mediaId;
							$attachment->save();
						}
					}
				}
				$this->redirect(array('view','id'=>$model->id));
			}
		} elseif(isset($_GET['Campaign'])) {
			//preload the create form with query params
			$model->setAttributes($_GET['Campaign']);
			$model->setX2Fields($_GET['Campaign']);
		}

		$this->render('create', array('model'=>$model));
	}

	/**
	 * Create a campaign for all contacts with a certain tag.
	 * 
	 * This action will create and save the campaign and redirect the user to 
	 * edit screen to fill in the email message, etc.  It is intended to provide
	 * a fast workflow from tags to campaigns.
	 *
	 * @param string $tag
	 */
	public function actionCreateFromTag($tag) {
		//enusre tag sanity
		if(empty($tag) || strlen(trim($tag)) == 0) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','Invalid tag value'));
			$this->redirect(Yii::app()->request->getUrlReferrer());
		}

		//ensure sacred hash
		if(substr($tag, 0, 1) != '#') {
			$tag = '#' . $tag;
		}
	
		//only works for contacts
		$modelType = 'Contacts';
		$now = time();

		//get all contact ids from tags
		$ids = Yii::app()->db->createCommand()
			->select('itemId')
			->from('x2_tags')
			->where('type=:type AND tag=:tag')
            ->group('itemId')
			->order('itemId ASC')
			->bindValues(array(':type'=>$modelType, ':tag'=>$tag))
			->queryColumn();

		//create static list
		$list = new X2List;
		$list->name = Yii::t('marketing', 'Contacts for tag') .' '. $tag;
		$list->modelName = $modelType;
		$list->type = 'campaign';
		$list->count = count($ids);
		$list->visibility = 1;
		$list->assignedTo = Yii::app()->user->getName();
		$list->createDate = $now; 
		$list->lastUpdated = $now;

		//create campaign
		$campaign = new Campaign;
		$campaign->name = Yii::t('marketing', 'Mailing for tag') .' '. $tag;
		$campaign->type = 'Email';
		$campaign->visibility = 1;
		$campaign->assignedTo = Yii::app()->user->getName();
		$campaign->createdBy = Yii::app()->user->getName();
		$campaign->updatedBy = Yii::app()->user->getName();
		$campaign->createDate = $now;
		$campaign->lastUpdated = $now;

		$transaction = Yii::app()->db->beginTransaction();
		try {
			if(!$list->save()) throw new Exception(array_shift(array_shift($list->getErrors())));
			$campaign->listId = $list->id;
			if(!$campaign->save()) throw new Exception(array_shift(array_shift($campaign->getErrors())));

			foreach($ids as $id) {
				$listItem = new X2ListItem;	
				$listItem->listId = $list->id;
				$listItem->contactId = $id;
				if(!$listItem->save()) throw new Exception(array_shift(array_shift($listItem->getErrors())));
			}

			$transaction->commit();
			$this->redirect($this->createUrl('update', array('id'=>$campaign->id)));
		} catch (Exception $e) {
			$transaction->rollBack();
			Yii::app()->user->setFlash('error', Yii::t('marketing','Could not create mailing') .': '. $e->getMessage());
			$this->redirect(Yii::app()->request->getUrlReferrer());
		}
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 *
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id) {
		$model = $this->loadModel($id);
		
		if(!isset($model)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		if(isset($_POST['Campaign'])) {
			$oldAttributes = $model->attributes;
			$model->setX2Fields($_POST['Campaign']);
			if($model->template != 0)
				$model->content = '';

			if($model->save()) {
				CampaignAttachment::model()->deleteAllByAttributes(array('campaign'=>$model->id));
				if(isset($_POST['AttachmentFiles'])) {
					if(isset($_POST['AttachmentFiles']['id'])) {
						foreach($_POST['AttachmentFiles']['id'] as $mediaId) {
							$attachment = new CampaignAttachment;
							$attachment->campaign = $model->id;
							$attachment->media = $mediaId;
							$attachment->save();
						}
					}
				}
				$this->redirect(array('view','id'=>$model->id));
			}
		}
		// load the template into the content field
		if($model->template != 0) {
			$template = X2Model::model('Docs')->findByPk($model->template);
			if(isset($template))
				$model->content = $template->text;
		}

		$this->render('update', array('model'=>$model));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 *
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id) {
		if(Yii::app()->request->isPostRequest) {
			$model = $this->loadModel($id);
			
			if(!isset($model)) {
				Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
				$this->redirect(array('index'));
			}
			// now in X2ChangeLogBehavior
			// $event=new Events;
			// $event->type='record_deleted';
			// $event->associationType=$this->modelClass;
			// $event->associationId=$model->id;
			// $event->text=$model->name;
			// $event->user=Yii::app()->user->getName();
			// $event->save();
			$list = $model->list;
			if(isset($list) && $list->type == "campaign")
				$list->delete();
			// $this->cleanUpTags($model);	// now in TagBehavior
			$model->delete();
			
			// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
			if(!isset($_GET['ajax']))
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		} else {
			Yii::app()->user->setFlash('error', Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
			$this->redirect(array('index'));
		}
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex() {
		$model=new Campaign('search');
		$this->render('index', array('model'=>$model));
	}
    
    public function actionAdmin(){
        $this->redirect('index');
    }


	/**
	 * Launches the specified campaign, activating it for mailing
	 *
	 * When a campaign is created, it is specified with an existing contact list.
	 * When the campaign is lauched, this list is replaced with a duplicate to prevent
	 * the original from being modified, and to allow campaign specific information to
	 * be saved in the list.  This includes the email send time, and the times when a
	 * contact has opened the mail or unsubscribed from the list.
	 * 
	 * @param integer $id ID of the campaign to launch
	 */
	public function actionLaunch($id) {
		$campaign = $this->loadModel($id);
		// check if there's a template, and load that into the content field
		if($campaign->template != 0) {
			$template = X2Model::model('Docs')->findByPk($campaign->template);
			if(isset($template))
				$campaign->content = $template->text;
		}
		
		if(!isset($campaign)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		if(!isset($campaign->list)) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','Contact List cannot be blank.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if(empty($campaign->subject)) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','Subject cannot be blank.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if($campaign->launchDate != 0 && $campaign->launchDate < time()) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','The campaign has already been launched.'));
			$this->redirect(array('view', 'id'=>$id));
		}

		if(($campaign->list->type == 'dynamic' && X2Model::model($campaign->list->modelName)->count($campaign->list->queryCriteria()) < 1)
			|| ($campaign->list->type != 'dynamic' && count($campaign->list->listItems) < 1)) {
			Yii::app()->user->setFlash('error', Yii::t('marketing','The contact list is empty.'));
			$this->redirect(array('view', 'id'=>$id));
		}
		
		//Duplicate the list for campaign tracking, leave original untouched
		//only if the list is not already a campaign list
		if($campaign->list->type != "campaign") {
			$newList = $campaign->list->staticDuplicate();
			if(!isset($newList)) {
				Yii::app()->user->setFlash('error', Yii::t('marketing','The contact list is empty.'));
				$this->redirect(array('view', 'id'=>$id));
			}
			$newList->type = 'campaign';
			$newList->save();
			$campaign->list = $newList;
			$campaign->listId = $newList->id;
		}

		$campaign->launchDate = time();
		$campaign->save();
		
		Yii::app()->user->setFlash('success', Yii::t('marketing','Campaign launched'));
		$this->redirect(array('view', 'id'=>$id));
	}
	
	/**
	 * Deactivate a campaign to halt mailings, or resume paused campaign
	 *
	 * @param integer $id The ID of the campaign to toggle
	 */
	public function actionToggle($id) {
		$campaign = $this->loadModel($id);

		if(!isset($campaign)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		$campaign->active = $campaign->active ? 0 : 1;
		$campaign->save();
		$message = $campaign->active ? Yii::t('marketing','Campaign resumed') : Yii::t('marketing','Campaign paused');
		Yii::app()->user->setFlash('notice', Yii::t('app', $message));
		$this->redirect(array('view', 'id'=>$id));
	}
	
	/**
	 * Forcibly complete a campaign despite any unsent mail
	 *
	 * @param integer $id The ID of the campaign to complete
	 */
	public function actionComplete($id) {
		$campaign = $this->loadModel($id);

		if(!isset($campaign)) {
			Yii::app()->user->setFlash('error', Yii::t('app', 'The requested page does not exist.'));
			$this->redirect(array('index'));
		}
	
		$campaign->active = 0;
		$campaign->complete = 1;
		$campaign->save();
		$message = Yii::t('marketing','Campaign complete.') ;
		Yii::app()->user->setFlash('notice', Yii::t('app', $message));
		$this->redirect(array('view', 'id'=>$id));
	}

	/**
	 * Send mail for any active campaigns
	 *
	 * This call is usually made from the context of one specific campaign, though
	 * it will always try to send mail for all active campaigns. If one campaign is
	 * specified, usually from an ajax call, the action will return a JSON object with
	 * property 'wait' indicated how many seconds to wait before making the call again,
	 * and property 'messages' containing an array of messages relevant to the specified
	 * campaign.
	 *
	 * @param integer $id The ID of the campaign to return status messages for
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
			if($sendLimit < 1) {
			  throw new Exception(Yii::t('marketing','The email sending limit has been reached.'));
			}

			//get all campaigns that could use mailing
			$campaigns = Campaign::model()->with('list')->findAllByAttributes(
				array('complete'=>0, 'active'=>1, 'type'=>'Email'), 
				'launchdate > 0 AND launchdate < :time',
				array(':time'=>time()));

			if(count($campaigns) == 0) { 
				throw new Exception(Yii::t('marketing','There is no campaign email to send.'));
			}

			$totalSent = 0;
			foreach($campaigns as $campaign) {
				if($totalSent >= $sendLimit) break;

				try {
					list($sent, $errors) = $this->campaignMailing($campaign, $sendLimit-$totalSent);
				} catch (Exception $e) {
					if($campaign->id == $id) $messages[] = $e->getMessage();
					continue;
				}

				$totalSent += $sent;

				//return status messages for the campaign specified in the request
				if($campaign->id == $id) {
					//count the number of contacts we can't send to
					$sql = 'SELECT COUNT(*) FROM x2_list_items as t LEFT JOIN x2_contacts as c ON t.contactId=c.id WHERE t.listId=:listId '
						.'AND t.sent=0 AND t.unsubscribed=0 AND (c.email IS NULL OR c.email="") AND (t.emailAddress IS NULL OR t.emailAddress="");';
					$blankEmail = Yii::app()->db->createCommand($sql)->queryScalar(array('listId'=>$campaign->list->id));

					/* PRE WEBLIST
					$criteria = $campaign->list->queryCriteria();
					$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
					         ->addCondition('t.email IS NULL OR t.email=""');
					$blankEmail = X2Model::model('Contacts')->count($criteria);*/

					//count the number of contacts who don't want email 
					$sql = 'SELECT COUNT(*) FROM x2_list_items as t LEFT JOIN x2_contacts as c ON t.contactId=c.id WHERE t.listId=:listId '
						.'AND t.sent=0 AND t.unsubscribed=0 AND c.doNotEmail=1;';
					$doNotEmail = Yii::app()->db->createCommand($sql)->queryScalar(array('listId'=>$campaign->list->id));

					/* PRE WEBLIST
					$criteria = $campaign->list->queryCriteria();
					$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
					         ->addCondition('t.doNotEmail=1');
					$doNotEmail = X2Model::model('Contacts')->count($criteria);*/

					$errorCount = count($errors); 

					$unsendable = $blankEmail + $doNotEmail + $errorCount;
					
					if($campaign->complete) $messages[] = Yii::t('marketing','Campaign complete.'); 
					$messages[] = Yii::t('marketing','Successful email sent') .': '. $sent;
					if($unsendable > 0) $messages[] = Yii::t('marketing','Unsendable email') .': '. $unsendable;
					if($blankEmail > 0) $messages[] = '&nbsp;'. Yii::t('marketing','Blank email addresses') .': '. $blankEmail;
					if($doNotEmail > 0) $messages[] = '&nbsp;'. Yii::t('marketing','\'Do Not Email\' contacts') .': '. $doNotEmail;
					if($errorCount > 0) $messages[] = '&nbsp;'. Yii::t('marketing','Data errors') .': '. $errorCount;
					if($totalSent >= $sendLimit)
						$messages[] = Yii::t('marketing','Batch completed, sending again in '). $interval .' '. Yii::t('marketing','minutes').'...';
					$messages[] = '';

					if(count($errors) > 1) {
						$messages = array_merge($messages, array_unique($errors));
						$messages[] = '';
					}
				}
			}
			//return general messsages if no specific campaign
			if($id == null) {
				if($totalSent > 0) {
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

	/**
	 * Send mail for one campaign
	 *
	 * @param Campaign $campaign The campaign to send
	 * @param integer $limit The maximum number of emails to send
	 * 
	 * @return Array [0]=> The number of emails sent, [1]=> array of applicable error messages
	 */
	protected function campaignMailing($campaign, $limit=null) {
		//TODO: handle large mail batches that would trigger a timeout

		$totalSent = 0;
		$errors = array();
	
		//get eligible contacts from the campaign
		$sql = 'SELECT *, t.id as li_id, c.id as c_id FROM x2_list_items as t LEFT JOIN x2_contacts as c ON t.contactId=c.id WHERE t.listId=:listId '
			.'AND t.sent=0 AND t.unsubscribed=0 AND (c.doNotEmail IS NULL OR c.doNotEmail=0) AND ((c.email IS NOT NULL AND c.email!="") OR (t.emailAddress IS NOT NULL AND t.emailAddress!=""));';
		$recipients = Yii::app()->db->createCommand($sql)->queryAll(true, array('listId'=>$campaign->list->id));
		
		//setup campaign email settings
		try {
			$phpMail = $this->getPhpMailer();
			
			// lookup campaign owner's email address
			$profile = X2Model::model('Profile')->findByAttributes(array('username'=>$campaign->createdBy));
			if($profile !== null) {
				$fromEmail = $profile->emailAddress;
				$fromName = $profile->fullName;
			} else {	//use site defaults otherwise
				$fromEmail = Yii::app()->params->admin->emailFromAddr;
				$fromName = Yii::app()->params->admin->emailFromName;
			}
			$phpMail->AddReplyTo($fromEmail, $fromName);
			$phpMail->SetFrom($fromEmail, $fromName);
			// $phpMail->Subject = $campaign->subject;
	/*		$attachments = $campaign->attachments;
			foreach($attachments as $attachment) {
				$media = $attachment->mediaFile;
				if($media) {
					if($file = $media->getPath()) {
						if(file_exists($file)) // check file exists
							$phpMail->AddAttachment($file);
					}
				}
			} */
		} catch (Exception $e) {
			throw $e;
		}
		
		//prepare the list item update query to be used many times later
		$sql = 'UPDATE x2_list_items SET sent=:sent, uniqueId=:uid WHERE id=:id;';
		$itemUpdateCmd = Yii::app()->db->createCommand($sql);

		//keep track of sends to prevent duplicate emails
		$sentAddresses = array();

		foreach($recipients as $recipient) {
			if(isset($limit) && $totalSent >= $limit)	//only send up to the specified limit
				break;
			
			try {
				
				$contact = new Contacts();
				$contact->setAttributes($recipient);

				
				//get the correct email address to send to
				//'email' is from contact record, 'emailAddress' is from list item
				$email = !empty($recipient['email']) ? $recipient['email'] : $recipient['emailAddress'];

				//if this address has already been sent, skip it and continue
				if(in_array($email, $sentAddresses)) {
					throw new Exception(Yii::t('marketing','Duplicate Email Address'));
				}

				$now = time();
				$uniqueId = md5(uniqid(rand(), true));
				//add some newlines to prevent hitting 998 line length limit in phpmailer/rfc2821
				$emailBody = preg_replace('/<br>/',"<br>\n",$campaign->content);
				
				// add links to attachments
				try {
					$attachments = $campaign->attachments;
					if(sizeof($attachments) > 0) {
						$emailBody .= "<br>\n<br>\n";
						$emailBody .= '<b>' . Yii::t('media', 'Attachments:') . "</b><br>\n";
					}
					foreach($attachments as $attachment) {
						$media = $attachment->mediaFile;
						if($media) {
							if($file = $media->getPath()) {
								if(file_exists($file)) { // check file exists
									if($url = $media->getFullUrl()) {
										$emailBody .= CHtml::link($media->fileName, $url) . "<br>\n";
									}
								}
							}
						}
					}
				} catch (Exception $e) {
					throw $e;
				}
								

				//if there is no unsubscribe link placeholder, add default
				if(!preg_match('/\{_unsub\}/', $campaign->content)) {
					$unsubText = "<br/>\n-----------------------<br/>\n"
					            .Yii::t('marketing','To stop receiving these messages, click here') .": {_unsub}";
					$emailBody .= $unsubText;
				}

				// $emailBody = x2base::convertUrls($emailBody, false);
				
				/* disable this for now
				//replace existing links with tracking links
				$url = $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'click')); 
				//profane black magic
				$emailBody = preg_replace(
					'/(<a[^>]*href=")([^"]*)("[^>]*>)/e', "(\"\\1" . $url . "&url=\" . urlencode(\"\\2\") . \"\\3\")", $emailBody);
				/* disable end */
				
				//insert unsubscribe links
				$emailBody = preg_replace(
					'/\{_unsub\}/', 
					'<a href="' . $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'unsub', 'email'=>$email)) . '">'. Yii::t('marketing', 'unsubscribe') .'</a>', 
					$emailBody); 
				
				//replace any {attribute} tags with the contact attribute value
				$emailBody = Docs::replaceVariables($emailBody,$contact,array('trackingKey'=>$uniqueId));	// use the campaign key, not the general key
				
				//add a link to transparent img to track when email was viewed
				$emailBody .= '<img src="' . $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'open')) . '"/>';

				$phpMail->Subject = Docs::replaceVariables($campaign->subject,$contact);
				
				$phpMail->ClearAllRecipients();
				$phpMail->AddAddress($email, $recipient['firstName'] .' '. $recipient['lastName']);
				$phpMail->MsgHTML($emailBody);
				$phpMail->Send();
				$totalSent++;
				$sentAddresses[] = $email;

				//record campaignid, contactid, senttime, uniqueid to save into listitem
				$itemUpdateCmd->bindValues(array(':id'=>$recipient['li_id'], ':sent'=>$now, ':uid'=>$uniqueId))
					->execute();

				//create action for this email if tied to a contact
				if(!empty($recipient['c_id'])) {
					$action = new Actions;
					$action->associationType = 'contacts';
					$action->associationId = $recipient['c_id'];
					$action->associationName = $recipient['firstName'] .' '. $recipient['lastName'];
					$action->visibility = $recipient['visibility'];
					$action->type = 'email';
					$action->assignedTo = $recipient['assignedTo'];
					$action->createDate = $now;
					$action->completeDate = $now;
					$action->complete = 'Yes';
					$action->actionDescription = '<b>'. Yii::t('marketing','Campaign') .': '. $campaign->name."</b>\n\n"
						.Yii::t('marketing','Subject'). ": ".$campaign->subject."\n\n".$campaign->content;
					$action->save();
				}
			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}
		}

		//check if campaign is complete
		//TODO: consider contacts with unsendable addresses
		$totalCount = Yii::app()->db->createCommand('SELECT COUNT(*) FROM x2_list_items as t LEFT JOIN x2_contacts as c ON t.contactId=c.id'
			.' WHERE t.listId=:listId AND (c.doNotEmail IS NULL OR c.doNotEmail=0);')->queryScalar(array('listId'=>$campaign->list->id));
		$sentCount = Yii::app()->db->createCommand('SELECT COUNT(*) FROM x2_list_items as t LEFT JOIN x2_contacts as c ON t.contactId=c.id'
			.' WHERE t.listId=:listId AND (c.doNotEmail IS NULL OR c.doNotEmail=0) AND t.sent>0;')->queryScalar(array('listId'=>$campaign->list->id));
		if($totalCount == $sentCount) {
			$campaign->active = 0;
			$campaign->complete = 1;
			$campaign->save();
		}

		return array($totalSent, $errors);
	}

	/* PRE WEBLIST
	protected function campaignMailing($campaign, $limit=null) {
		//per request batch limits, dont send enough to timeout
		//per log cycle batch, 10 at a time or so to reduce logging sent time queries
		//Timeouts? make sure each mail is logged individually, not waiting for the batch to finish
		//ENSURE no duplicate mail

		$totalSent = 0;
		$errors = array();
	
		//get eligible contacts from the campaign
		$criteria = $campaign->list->queryCriteria();
		$criteria->addCondition('x2_list_items.sent=0')->addCondition('x2_list_items.unsubscribed=0')
		         ->addCondition('t.email IS NOT NULL')->addCondition('t.email!=""')
		         ->addCondition('t.doNotEmail=0');
		$contacts = X2Model::model('Contacts')->findAll($criteria);

		//setup campaign email settings
		try {
			$phpMail = $this->getPhpMailer();
			try {
				//lookup current user's email address
				$fromEmail = Yii::app()->params->profile->emailAddress;
				$fromName = Yii::app()->params->profile->fullName;
			} catch (Exception $e) {
				//use site defaults otherwise
				$fromEmail = Yii::app()->params->admin->emailFromAddr;
				$fromName = Yii::app()->params->admin->emailFromName;
			}
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
				if($limit && $totalSent >= $limit) break;

				$now = time();
				$uniqueId = md5(uniqid(rand(), true));
				//add some newlines to prevent hitting 998 line length limit in phpmailer/rfc2821
				$emailBody = preg_replace('/<br>/',"<br>\n",$campaign->content);

				//if there is no unsubscribe link placeholder, add default
				if(!preg_match('/\{_unsub\}/', $campaign->content)) {
					$unsubText = "<br/>\n-----------------------<br/>\n"
					            ."To stop receiving these messages, click here: {_unsub}";
					$emailBody .= $unsubText;
				}

				$emailBody = x2base::convertUrls($emailBody, false);
				
				// disable this for now
				////replace existing links with tracking links
				//$url = $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'click')); 
				////profane black magic
				//$emailBody = preg_replace(
				//	'/(<a[^>]*href=")([^"]*)("[^>]*>)/e', "(\"\\1" . $url . "&url=\" . urlencode(\"\\2\") . \"\\3\")", $emailBody);
				// disable end 
				
				//insert unsubscribe links
				$emailBody = preg_replace(
					'/\{_unsub\}/', 
					'<a href="' . $this->createAbsoluteUrl('click', array('uid'=>$uniqueId, 'type'=>'unsub', 'email'=>$contact->email)) . '">'. Yii::t('marketing', 'unsubscribe') .'</a>', 
					$emailBody); 
			
				//replace any {attribute} tags with the contact attribute value
				$attrMatches = array();
				preg_match_all('/{\w+}/', $emailBody,$attrMatches);
				
				if(isset($attrMatches[0])) {
					foreach($attrMatches[0] as $match) {
						$match = substr($match,1,-1);	// remove { and }
						
						if($contact->hasAttribute($match)) {
							$value = $contact->renderAttribute($match, false, true);	// get the correctly formatted attribute
							$emailBody = preg_replace('/{'.$match.'}/', $value, $emailBody);
						}
					}
				}

				//add a link to transparent img to track when email was viewed
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
					//$action->actionDescription = CHtml::link($template->name,array('/docs/'.$template->id));
				
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
		if($totalCount == $sentCount) {
			$campaign->active = 0;
			$campaign->complete = 1;
			$campaign->save();
		}

		return array($totalSent, $errors);
	}*/

	/**
	 * Track when an email is viewed, a link is clicked, or the recipient unsubscribes
	 *
	 * Campaign emails include an img tag to a blank image to track when the message was opened,
	 * an unsubscribe link, and converted links to track when a recipient clicks a link.
	 * All those links are handled by this action.
	 *
	 * @param integer $uid The unique id of the recipient
	 * @param string $type 'open', 'click', or 'unsub'
	 * @param string $url For click types, this is the urlencoded URL to redirect to
	 * @param string $email For unsub types, this is the urlencoded email address
	 *  of the person unsubscribing
	 */
	public function actionClick($uid,$type,$url=null,$email=null) {
		$now = time();
		$item = CActiveRecord::model('X2ListItem')->with('contact','list')->findByAttributes(array('uniqueId'=>$uid));
		// if($item !== null)
			// $campaign = CActiveRecord::model('Campaign')->findByAttributes(array('listId'=>$item->listId));

		//it should never happen that we have a list item without a campaign, 
		//but it WILL happen on x2software or any old db where x2_list_items does not cascade on delete
		//we can't track anything if the listitem was deleted, but at least prevent breaking links
		if($item === null || $item->list->campaign === null) {
			if($type == 'click') {
				$this->redirect(urldecode($url));
			} elseif($type == 'open') {
				//return a one pixel transparent gif
				header('Content-Type: image/gif');
				echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
			} elseif($type == 'unsub' && !empty($email)) {
				Contacts::model()->updateAll(array('doNotEmail'=>true),'email=:email',array(':email'=>$email));
				X2ListItem::model()->updateAll(array('unsubscribed'=>time()), 'emailAddress=:email AND unsubscribed=0', array('email'=>$email));
				$message = Yii::t('marketing','You have been unsubscribed');
				echo '<html><head><title>'. $message .'</title></head><body>'. $message .'</body></html>';
			} 
			return;
		}
		
		$contact = $item->contact;
		$list = $item->list;
		
		$event = new Events;
		$notif = new Notification;
		
		$action = new Actions;
		$action->completeDate = $now;
		$action->complete = 'Yes';
		$action->updatedBy = 'API';
		
		if($contact !== null) {
			if($email === null)
				$email = $contact->email;
		
			$action->associationType = 'contacts';
			$action->associationId = $contact->id;
			$action->associationName = $contact->name;
			$action->visibility = $contact->visibility;
			$action->assignedTo = $contact->assignedTo;
			
			$event->associationId = $action->associationId;
			$event->associationType = 'Contacts';
			
			if($action->assignedTo !== '' && $action->assignedTo !== 'Anyone') {
				$notif->user = $contact->assignedTo;
				$notif->modelType = 'Contacts';
				$notif->modelId = $contact->id;
				$notif->createDate = $now;
				$notif->value = $item->list->campaign->getLink();
			}
		} elseif($list !== null) {
			$action = new Actions;
			$action->type = 'note';
			$action->createDate = $now;
			$action->lastUpdated = $now;
			$action->completeDate = $now;
			$action->complete = 'Yes';
			$action->updatedBy = 'admin';
			
			$action->associationType = 'X2List';
			$action->associationId = $list->id;
			$action->associationName = $list->name;
			$action->visibility = $list->visibility;
			$action->assignedTo = $list->assignedTo;
		}
		
		if($type == 'unsub') {
			$item->unsubscribe();
			
			// find any weblists associated with the email address and create unsubscribe actions for each of them
			$sql = 'SELECT t.* FROM x2_lists as t JOIN x2_list_items as li ON t.id=li.listId WHERE li.emailAddress=:email AND t.type="weblist";'; 
			$weblists = Yii::app()->db->createCommand($sql)->queryAll(true,array('email'=>$email));
			foreach($weblists as $weblist) {
				$weblistAction->id = 0;
				$weblistAction->isNewRecord = true;
				$weblistAction->type = 'email_unsubscribed';
				$weblistAction->associationType = 'X2List';
				$weblistAction->associationId = $weblist['id'];
				$weblistAction->associationName = $weblist['name'];
				$weblistAction->visibility = $weblist['visibility'];
				$weblistAction->assignedTo = $weblist['assignedTo'];
				$weblistAction->actionDescription = Yii::t('marketing','Campaign').': '.$item->list->campaign->name."\n\n".$email." ".Yii::t('marketing','has unsubscribed').".";
				$weblistAction->save();
			}
			
			$action->type = 'email_unsubscribed';
			$notif->type = 'email_unsubscribed';
			
			if($contact === null)
				$action->actionDescription = Yii::t('marketing','Campaign') .': '. $item->list->campaign->name ."\n\n".$item->emailAddress.' '.Yii::t('marketing','has unsubscribed').".";
			else
				$action->actionDescription = Yii::t('marketing','Campaign') .': '. $item->list->campaign->name ."\n\n".Yii::t('marketing','Contact has unsubscribed').".\n".Yii::t('marketing','\'Do Not Email\' has been set').".";
			
			$message = Yii::t('marketing','You have been unsubscribed');
			echo '<html><head><title>'. $message .'</title></head><body>'. $message .'</body></html>';
			
		} elseif($type == 'open') {
			$item->markOpened();
			$action->disableBehavior('changelog');
			$action->type = 'email_opened';
			$event->type = 'email_opened';
			$notif->type = 'email_opened';
            $event->save();
			
			if($contact === null)
				$action->actionDescription = Yii::t('marketing','Campaign').': '.$item->list->campaign->name."\n\n".$item->emailAddress.' '.Yii::t('marketing','has opened the email').".";
			else
				$action->actionDescription = Yii::t('marketing','Campaign').': '.$item->list->campaign->name."\n\n".Yii::t('marketing','Contact has opened the email').".";
			
			//return a one pixel transparent gif
			header('Content-Type: image/gif');
			echo base64_decode('R0lGODlhAQABAIABAP///wAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');

		} elseif($type == 'click') {
			$item->markClicked($url);
			
			$action->type = 'email_clicked';
			$notif->type = 'email_clicked';
			
			if($contact === null)
				$action->actionDescription = Yii::t('marketing','Campaign').': '.$item->list->campaign->name."\n\n".Yii::t('marketing','Contact has clicked a link').":\n".urldecode($url);
			else
				$action->actionDescription = Yii::t('marketing','Campaign').': '.$item->list->campaign->name."\n\n".$item->emailAddress.' '.Yii::t('marketing','has clicked a link').":\n".urldecode($url);
			
			$this->redirect(urldecode($url));	
		}
		
		$action->save();
                    		// if any of these hasn't been fully configured
		$notif->save();		// it will simply not validate and not be saved
	}

	/**
	 * Create a web lead form with a custom style
	 */
	public function actionWebleadForm() {
		if(file_exists(__DIR__ . '/pro/actionWebleadForm.php')) {
			include(__DIR__ . '/pro/actionWebleadForm.php');
			return;
		}
		
		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			if (empty($_POST['name'])) {
				echo json_encode(array('errors'=>array('name'=>Yii::t('marketing','Name cannot be blank.'))));
				return;
			}

			$type = !empty($_POST['type']) ? $_POST['type'] : 'weblead';
			$model = WebForm::model()->findByAttributes(array('name'=>$_POST['name'], 'type'=>$type));
			if (!isset($model)) {
				$model = new WebForm;
				$model->name = $_POST['name'];
				$model->type = $type;
				$model->modelName = 'Contacts';
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
            if(Yii::app()->user->getName()!='admin'){
            $condition = 'AND visibility="1" OR assignedTo="Anyone"  OR assignedTo="'.Yii::app()->user->getName().'"';
            /* x2temp */
            $groupLinks = Yii::app()->db->createCommand()->select('groupId')->from('x2_group_to_user')->where('userId='.Yii::app()->user->getId())->queryColumn();
            if(!empty($groupLinks))
                $condition .= ' OR assignedTo IN ('.implode(',',$groupLinks).')';

            $condition .= 'OR (visibility=2 AND assignedTo IN 
                (SELECT username FROM x2_group_to_user WHERE groupId IN
                    (SELECT groupId FROM x2_group_to_user WHERE userId='.Yii::app()->user->getId().')))';
            }else{
                $condition="";
            }
			//this get request is for weblead type only, marketing/weblist/view supplies the form that posts for weblist type
			$forms = WebForm::model()->findAll('type="weblead" '.$condition);
			$this->render('webleadForm', array('forms'=>$forms));
		}
	}

	/**
	 * Get the web tracker code to insert into your website
	 */
	public function actionWebTracker() {
		$admin = Yii::app()->params->admin;
		if(isset($_POST['Admin']['enableWebTracker'],$_POST['Admin']['webTrackerCooldown'])) {
			$admin->enableWebTracker = $_POST['Admin']['enableWebTracker'];
			$admin->webTrackerCooldown = $_POST['Admin']['webTrackerCooldown'];
			$admin->save();
		}
		$this->render('webTracker',array('admin'=>$admin));
	}
}
