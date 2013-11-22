<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ******************************************************************************* */

/**
 * Provides an action for sending email from a view page with an inline form.
 *
 * Accepts post requests with form-urlencoded data, responds with JSON.
 *
 * @property InlineEmail $model
 * @package X2CRM.components
 */
class InlineEmailAction extends CAction {

	public $model = null;

	public function getBehaviors(){
		return array(
			'responds' => array(
				'class' => 'application.components.ResponseBehavior',
				'isConsole' => false,
				'exitNonFatal' => false,
				'longErrorTrace' => false,
			),
		);
	}

	public function run(){
		$this->attachBehaviors($this->behaviors);
		// Safety net of handlers - they ensure that errors can be caught and seen easily:
		set_error_handler('ResponseBehavior::respondWithError');
		set_exception_handler('ResponseBehavior::respondWithException');

		$scenario = 'custom';
		if(empty($this->model))
			$model = new InlineEmail();
		else
			$model = $this->model;
        if(isset($_POST['contactFlag'])){
            $model->contactFlag=$_POST['contactFlag'];
        } 
        $makeEvent = isset($_GET['skipEvent']) ? !((bool) $_GET['skipEvent']) : 1;
		// Check to see if the user is requesting a new template
		if(isset($_GET['template'])){
			$scenario = 'template';;
		}
		$model->setScenario($scenario);

		$attachments = array();

		if(isset($_POST['InlineEmail'])){
			// This could indicate either a template change or a form submission.
			$model->attributes = $_POST['InlineEmail'];

			// Prepare attachments that may have been uploaded on-the-fly (?)
			$mediaLibraryUsed = false; // is there an attachment from the media library?
			if(isset($_POST['AttachmentFiles'], $_POST['AttachmentFiles']['id'], $_POST['AttachmentFiles']['temp'])){
				$ids = $_POST['AttachmentFiles']['id'];
				$temps = $_POST['AttachmentFiles']['temp'];
				$attachments = array();
				for($i = 0; $i < count($ids); $i++){
					$temp = json_decode($temps[$i]);
					if($temp){ // attachment is a temp file
						$tempFile = TempFile::model()->findByPk($ids[$i]);
						$attachments[] = array('filename' => $tempFile->name, 'folder' => $tempFile->folder, 'temp' => json_decode($temps[$i]), 'id' => $tempFile->id);
					}else{ // attachment is from media library
						$mediaLibraryUsed = true;
						$media = Media::model()->findByPk($ids[$i]);
						$attachments[] = array('filename' => $media->fileName, 'folder' => $media->uploadedBy, 'temp' => json_decode($temps[$i]), 'id' => $media->id);
					}
				}
			}
			$model->attachments = $attachments;

			// Validate/prepare the body, and send if no problems occur:
			$sendStatus = array_fill_keys(array('code','message'),'');
			$failed = false;
			$message = '';
            $postReplace = isset($_GET['postReplace']) ? $_GET['postReplace'] : 0;
			if(isset($_GET['loadTemplate']))
				$model->template = $_GET['loadTemplate']; // A special override for when it's not possible to include the template in $_POST

			if($model->prepareBody($postReplace)){
				if($scenario != 'template'){
					// Sending the email, not merely requesting a template change
					// 
					// First check that the user has permission to use the
					// specified credentials:
					if($model->credId != Credentials::LEGACY_ID)
						if(!Yii::app()->user->checkAccess('CredentialsSelect',array('model'=>$model->credentials)))
							self::respond(Yii::t('app','Did not send email because you do not have permission to use the specified credentials.'),1);
					$sendStatus = $model->send($makeEvent);
					// $sendStatus = array('code'=>'200','message'=>'sent (testing)');
					$failed = $sendStatus['code'] != '200';
					$message = $sendStatus['message'];
				} else if($model->modelName == 'Quote' && empty($model->template)) {
					// Fill in the gap with the default / "semi-legacy" quotes view
					$model->message = $this->controller->renderPartial('application.modules.quotes.views.quotes.print', array('model' => $model->targetModel,'email' => true), true);
					// Add a linebreak at the beginning for user-entered notes in the email:
					$model->insertInBody('<br />',1);
				}
			}

			// Populate response data:
			$modelHasErrors = $model->hasErrors();
			$failed = $failed || $modelHasErrors;
			$response = array(
				'scenario' => $scenario,
				'sendStatus' => $sendStatus,
				'attributes' => $model->attributes,
				'modelErrors' => $model->errors,
				'modelHasErrors' => $modelHasErrors,
				'modelErrorHtml' => CHtml::errorSummary($model,Yii::t('app', "Please fix the following errors:"), null,array('style'=>'margin-bottom: 5px;')),
			);
			if($scenario == 'template') {
				// There's a chance the inline email form is switching gears into
				// quote mode, in which case we need to include templates and
				// insertable attributes for setting it all up properly:
				$response['insertableAttributes'] = $model->insertableAttributes;
				$templates = array(0=>Yii::t('docs','Custom Message')) + Docs::getEmailTemplates($model->modelName=='Quote'?'quote':'email');
				$response['templateList'] = array();
				foreach($templates as $id=>$templateName) {
					$response['templateList'][] = array('id'=>$id,'name'=>$templateName);
				}
			}
			$this->mergeResponse($response);

			self::respond($message,$failed);
		}else{
			self::respond(Yii::t('app', 'Inline email model missing from the request to the server.'), 1);
		}
	}

}

?>
