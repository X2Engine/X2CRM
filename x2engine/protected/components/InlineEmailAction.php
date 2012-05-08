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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

class InlineEmailAction extends CAction {

	public $model;
	
	public function run() {

		$preview = false;
		$emailBody = '';
		$signature = '';
		$template = null;
	
		if(!isset($this->model))
			$this->model = new InlineEmail;

		if(isset($_POST['InlineEmail'])) {

			if(isset($_GET['preview']) || (isset($_POST['InlineEmail']['submit']) && $_POST['InlineEmail']['submit'] == Yii::t('app','Preview')))
				$preview = true;

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
			
				$template = CActiveRecord::model('Docs')->findByPk($this->model->template);
				if(isset($template)) {
					$this->model->message = str_replace('\\\\', '\\\\\\', $this->model->message);
					$this->model->message = str_replace('$', '\\$', $this->model->message);
					$emailBody = preg_replace('/{content}/u','<!--BeginMsg-->'.$this->model->message.'<!--EndMsg-->',$template->text);
					$emailBody = preg_replace('/{signature}/u','<!--BeginSig-->'.$signature.'<!--EndSig-->',$emailBody);
					
					// check if subject is empty, or is from another template
					if(empty($this->model->subject) || CActiveRecord::model('Docs')->countByAttributes(array('type'=>'email','title'=>$this->model->subject)))
						$this->model->subject = $template->title;
					
					if(isset($this->model->modelName, $this->model->modelId)) {		// if there is a model name/id available, look it up and use its attributes
						$targetModel = CActiveRecord::model($this->model->modelName)->findByPk($this->model->modelId);
						if(isset($targetModel)) {
							$attributes = $targetModel->getAttributes();
							$attributeNames = array_keys($attributes);
							$attributeNames[] = 'content';
							$attributes = array_values($attributes);
							$attributes[] = $this->model->message;
							foreach($attributeNames as &$name)
								$name = '/{'.$name.'}/';
							unset($name);

							$emailBody = preg_replace($attributeNames,$attributes,$emailBody);
						}
					}
					// $this->model->template = 0;
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
				
				$this->model->status = $this->controller->sendUserEmail($this->model->mailingList,$this->model->subject,$emailBody);
				
				if(in_array('200',$this->model->status)) {
					
					foreach($this->model->mailingList['to'] as &$target) {
						$contact = Contacts::model()->findByAttributes(array('email'=>$target[1]));
						if(isset($contact)) {

							$action = new Actions;
							$action->associationType = 'contacts';
							$action->associationId = $contact->id;
							$action->associationName = $contact->name;
							$action->visibility = $contact->visibility;
							$action->complete = 'Yes';
							$action->type = 'email';
							$action->completedBy = Yii::app()->user->getName();
							$action->assignedTo = $contact->assignedTo;
							$action->createDate = time();
							$action->dueDate = time();
							$action->completeDate = time();
							if($template == null)
								$action->actionDescription = '<b>'.$this->model->subject."</b>\n\n".$this->model->message;
							else
								$action->actionDescription = CHtml::link($template->title,array('/docs/'.$template->id));
							
							if($action->save()){
                                                            
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
		echo $this->controller->renderPartial('application.components.views.inlineEmailForm',array(
			'model'=>$this->model,
			'preview'=>$preview? $emailBody : null,
		));
		// }
	}
}
?>