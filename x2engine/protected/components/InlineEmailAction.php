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

						if($targetModel !== null) {
							$emailBody = Docs::replaceVariables($emailBody,$targetModel);
							$this->model->subject = Docs::replaceVariables($this->model->subject,$targetModel);
						}
					}
					$this->model->message = $emailBody;
				}
				$this->model->template = 0;				// after applying the template, set it back to custom
				
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
							if($model->hasAttribute('lastActivity')) {
								$model->lastActivity = time();
								$model->update(array('lastActivity'));
							}
							
							$action = new Actions;
							$action->associationType = strtolower($this->model->modelName);
							$action->associationId = $model->id;
							$action->associationName = $model->name;
							$action->visibility = isset($model->visibility)? $model->visibility : 1;
							$action->completedBy = Yii::app()->user->getName();
							$action->assignedTo = $model->assignedTo;
							$action->createDate = time();
							$action->dueDate = time();
							if($stageEmail) {
								$action->complete = 'No';
								$action->type = 'email_staged';
							} else {
								$action->completeDate = time();
								$action->complete = 'Yes';
								$action->type = 'email';
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
