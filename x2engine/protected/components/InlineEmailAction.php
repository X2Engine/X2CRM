<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
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
 * Provides an action for sending email from a view page with an inline form.
 * 
 * @package X2CRM.components 
 */
class InlineEmailAction extends CAction {

	public $model;
	
	public function run() {

		$preview = false;
		$stageEmail = false;
		$emailBody = '';
		$signature = '';
		$template = null;
		$attachments = null;
			
		if(!isset($this->model))
			$this->model = new InlineEmail;

		if(isset($_POST['InlineEmail'])) {

			if(isset($_GET['preview']) || (isset($_POST['InlineEmail']['submit']) && $_POST['InlineEmail']['submit'] == Yii::t('app','Preview')))
				$preview = true;
			// if(isset($_GET['emailSendTimeSelector']) && $_GET['emailSendTimeSelector'] === '1')
				// $emailSendTimeSelector = '1';

			$this->model->attributes = $_POST['InlineEmail'];

			// if the user specified a template, look it up and use it for the message
			if($this->model->template != 0) {
				$matches = array();
				if(preg_match('/<!--BeginSig-->(.*)<!--EndSig-->/u',$this->model->message,$matches) && count($matches) > 1)	// extract signature
					$signature = $matches[1];
					
				$this->model->message = preg_replace('/<!--BeginSig-->(.*)<!--EndSig-->/u','',$this->model->message);	// remove signatures
					
				$matches = array();
				if(preg_match('/<!--BeginMsg-->(.*)<!--EndMsg-->/u',$this->model->message,$matches) && count($matches) > 1)
					$this->model->message = $matches[1];
			
				if(empty($signature))
					$signature = Yii::app()->params->profile->getSignature(true);	// load default signature if empty
			
				$template = X2Model::model('Docs')->findByPk($this->model->template);
				if(isset($template)) {
					$this->model->message = str_replace('\\\\', '\\\\\\', $this->model->message);
					$this->model->message = str_replace('$', '\\$', $this->model->message);
					$emailBody = preg_replace('/{content}/u','<!--BeginMsg-->'.$this->model->message.'<!--EndMsg-->',$template->text);
					$emailBody = preg_replace('/{signature}/u','<!--BeginSig-->'.$signature.'<!--EndSig-->',$emailBody);
					
					// check if subject is empty, or is from another template
					if(empty($this->model->subject) || X2Model::model('Docs')->countByAttributes(array('type'=>'email','subject'=>$this->model->subject)))
						$this->model->subject = $template->subject;
					
					// if there is a model name/id available, look it up and use its attributes
					if(isset($this->model->modelName, $this->model->modelId)) {
						$targetModel = X2Model::model($this->model->modelName)->findByPk($this->model->modelId);
						if(isset($targetModel)) {
						
							$matches = array();
							preg_match_all('/{\w+}/',$emailBody,$matches);	// find all the things
							
							if(isset($matches[0])) {					// loop through the things
								foreach($matches[0] as $match) {
									$match = substr($match,1,-1);	// remove { and }
									
									if($targetModel->hasAttribute($match)) {
										$value = $targetModel->renderAttribute($match,false,true);	// get the correctly formatted attribute
										$emailBody = preg_replace('/{'.$match.'}/',$value,$emailBody);
									}
								}
							}
						}
					}
					$this->model->template = 0;				// set to custom so the person can edit the whole message
					$this->model->message = $emailBody;
				}
			} elseif(!empty($this->model->message)) {	// if no template, use the user's custom message, and include a signature
				$emailBody = $this->model->message;
			// } elseif(!empty($this->model->message)) {	// if no template, use the user's custom message, and include a signature
				// $emailBody = $this->model->message.'<br><br>'.Yii::app()->params->profile->getSignature(true);
			}
			
			if($this->model->template == 0)
				$this->model->setScenario('custom');
				
			if($this->model->validate() && !$preview) {
			
				$uniqueId = md5(uniqid(rand(), true));
				$emailBody .= '<img src="' . $this->controller->createAbsoluteUrl('actions/emailOpened', array('uid'=>$uniqueId, 'type'=>'open')) . '"/>';
				
				$mediaLibraryUsed = false; // is there an attachment from the media library?
				if(isset($_POST['AttachmentFiles']) && isset($_POST['AttachmentFiles']['id']) && isset($_POST['AttachmentFiles']['temp']))  {
					$ids = $_POST['AttachmentFiles']['id'];
					$temps = $_POST['AttachmentFiles']['temp'];
					$attachments = array();
					for($i = 0; $i < count($ids); $i++) {
						$temp = json_decode($temps[$i]);
						if($temp) { // attachment is a temp file
							$tempFile = TempFile::model()->findByPk($ids[$i]);
							$attachments[] = array('filename' => $tempFile->name, 'folder' => $tempFile->folder, 'temp' => json_decode($temps[$i]), 'id' => $tempFile->id);
						} else { // attachment is from media library
							$mediaLibraryUsed = true;
							$media = Media::model()->findByPk($ids[$i]);
							$attachments[] = array('filename' => $media->fileName, 'folder' => $media->uploadedBy, 'temp' => json_decode($temps[$i]), 'id' => $media->id);
						}
					}
				}
				
				// if(isset($attachments))
				if(empty($this->model->emailSendTimeParsed))
					$this->model->status = $this->controller->sendUserEmail($this->model->mailingList,$this->model->subject,$emailBody, $attachments);
				else
					$stageEmail = true;
				// else
					// $this->model->status = $this->controller->sendUserEmail($this->model->mailingList,$this->model->subject,$emailBody);
				
				if(in_array('200',$this->model->status) || $stageEmail) {
					
					foreach($this->model->mailingList['to'] as &$target) {
						$model = X2Model::model(ucwords($this->model->modelName))->findByPk($this->model->modelId);
						if(isset($model)) {
                            if($model->hasAttribute('lastActivity')){
                                $model->lastActivity=time();
                                $model->save();
                            }

							$action = new Actions;
							$action->associationType = strtolower($this->model->modelName);
							$action->associationId = $model->id;
							$action->associationName = $model->name;
							if(isset($model->visibility))
								$action->visibility = $model->visibility;
							else
								$action->visibility = 1;
							$action->complete = 'Yes';
							$action->type = 'email';
							$action->completedBy = Yii::app()->user->getName();
							$action->assignedTo = $model->assignedTo;
							$action->createDate = time();

							if($stageEmail) {
								$action->complete = 'No';
								$action->type = 'email_staged';
								$action->dueDate = time();
								$action->completeDate = time();
							} else {
								$action->complete = 'Yes';
								$action->type = 'email';
								$action->dueDate = time();
								$action->completeDate = time();
							}

							if($template == null) {
								$action->actionDescription = '<b>'.$this->model->subject."</b><br><br>".$this->model->message;
								if(isset($attachments)) {
									$action->actionDescription .= "\n\n";
									$action->actionDescription .= '<b>'. Yii::t('media', 'Attachments:') . "</b>\n";
									foreach($attachments as $attachment) {
										$action->actionDescription .= '<span class="email-attachment-text">'. $attachment['filename'] . "</span>\n";
									}
								}
							} else
								$action->actionDescription = CHtml::link($template->name,array('/docs/'.$template->id));
							
							if($action->save()) {
                                $event=new Events;
                                $event->type='email_sent';
                                $event->user=Yii::app()->user->getName();
                                $event->associationType=$this->model->modelName;
                                $event->associationId=$model->id;
                                $event->save();
                                
								$track = new TrackEmail;
								$track->actionId = $action->id;
								$track->uniqueId = $uniqueId;
								$track->save();
							}
							
							// $message="2";
							// $email=$toEmail;
							// $id=$contact['id'];
							// $note.="\n\nSent to Contact";
						}
					}
					
				}
				
			}
		}
		
		$attachments = array();
		if(isset($_POST['AttachmentFiles']) && isset($_POST['AttachmentFiles']['id']) && isset($_POST['AttachmentFiles']['temp']))  {
		    $ids = $_POST['AttachmentFiles']['id'];
		    $temps = $_POST['AttachmentFiles']['temp'];
		    for($i = 0; $i < count($ids); $i++) {
		    	$temp = json_decode($temps[$i]);
		    	if($temp) { // attachment is a temp file
		    		$tempFile = TempFile::model()->findByPk($ids[$i]);
		    		if(isset($tempFile))
		    			$attachments[] = array('filename' => $tempFile->name, 'temp' => json_decode($temps[$i]), 'id' => $tempFile->id);
		    	} else { // attachment is from media library
		    		$mediaLibraryUsed = true;
		    		$media = Media::model()->findByPk($ids[$i]);
		    		if(isset($media))
		    			$attachments[] = array('filename' => $media->fileName, 'temp' => json_decode($temps[$i]), 'id' => $media->id);
		    	}
		    }
		}
		
		echo $this->controller->renderPartial('application.components.views.inlineEmailForm',array(
			'model'=>$this->model,
			'preview'=>$preview? $emailBody : null,
			'attachments'=>$attachments,
		));
		
		// }
	}
}
?>
