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

			if($model->prepareBody()){
				if($scenario != 'template'){
					// Sending the email, not merely requesting a template change
					$sendStatus = $model->send();
					// $sendStatus = array('code'=>'200','message'=>'sent (testing)');
					$failed = $sendStatus['code'] != '200';
					$message = $sendStatus['message'];
				} else if($model->modelName == 'Quote' && empty($model->template)) {
					// Fill in the gap with the default / "semi-legacy" quotes view
					$model->message = $this->controller->renderPartial('application.modules.quotes.views.quotes.print', array('model' => $model->targetModel,'email' => true), true);
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
