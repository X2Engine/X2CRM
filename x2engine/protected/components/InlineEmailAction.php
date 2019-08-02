<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/**
 * Provides an action for sending email from a view page with an inline form.
 *
 * Accepts post requests with form-urlencoded data, responds with JSON.
 *
 * @property InlineEmail $model
 * @package application.components
 */
class InlineEmailAction extends CAction {

	public $model = null;

	public function getBehaviors(){
		return array(
			'responds' => array(
				'class' => 'application.components.ResponseBehavior',
                'errorCode' => 200
			),
		);
	}

	public function run(){
        if (Yii::app()->user->isGuest) {
            Yii::app()->controller->redirect(Yii::app()->controller->createUrl('/site/login'));
        }

		$this->attachBehaviors($this->behaviors);
		// Safety net of handlers - they ensure that errors can be caught and seen easily:

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
			$scenario = 'template';
		}
		$model->setScenario($scenario);
		$attachments = array();

		if(isset($_POST['InlineEmail'])){
			// This could indicate either a template change or a form submission.
			$model->attributes = $_POST['InlineEmail'];

			// Prepare attachments that may have been uploaded on-the-fly
			if(isset(
                $_POST['AttachmentFiles'],
                $_POST['AttachmentFiles']['id'],
                $_POST['AttachmentFiles']['types'])){

				$ids = $_POST['AttachmentFiles']['id'];
				$types = $_POST['AttachmentFiles']['types'];
				$attachments = array();
				for($i = 0; $i < count($ids); $i++){
					$type = $types[$i];
                    switch ($type) {
                        case 'temp': // attachment is a temp file
                            $file = TempFile::model()->findByPk($ids[$i]);
                            $attachments[] = array(
                                'filename' => $file->name,
                                'folder' => $file->folder,
                                'type' => $type, 
                                'id' => $file->id,
                                'model' => $file,
                            );
                            break;
                        case 'media': // attachment is from media library
                            $file = Media::model()->findByPk($ids[$i]);
                            $attachments[] = array(
                                'filename' => $file->fileName,
                                'folder' => $file->uploadedBy,
                                'type' => $type,
                                'id' => $file->id,
                                'model' => $file,
                            );
                            break;
                         
                        case 'emailInboxes': // imap-fetched attachment from the emailInboxes module
                            list ($uid, $part) = explode (',', $ids[$i]);
                            $message = Yii::app()->controller->getSelectedMailbox ()
                                ->fetchMessage ($uid);
                            list ($mimeType, $filename, $size, $attachment, $encoding) = 
                                $message->downloadAttachment ($part, false, true);
                            $attachments[] = array(
                                'filename' => $filename,
                                'folder' => Yii::app()->user->getName (),
                                'type' => $type,
                                'id' => $uid,
                                'string' => $attachment,
                                'mimeType' => $mimeType,
                                'size' => $size,
                                'encoding' => $encoding,
                            );
                            break;
                         
                        default:
                            throw new CException ('Invalid attachment type: '.$type);
                    }
				}
			}
			$model->attachments = $attachments;

			// Validate/prepare the body, and send if no problems occur:
			$sendStatus = array_fill_keys(array('code','message'),'');
			$failed = false;
			$message = '';
            $postReplace = isset($_GET['postReplace']) ? $_GET['postReplace'] : 0;
			if(isset($_GET['loadTemplate'])) {
                // A special override for when it's not possible to include the template in $_POST
				$model->template = $_GET['loadTemplate']; 
            }

			if($model->prepareBody($postReplace)){
				if($scenario != 'template'){
					// Sending the email, not merely requesting a template change
					// 
					// First check that the user has permission to use the
					// specified credentials:
					if($model->credId != Credentials::LEGACY_ID)
						if(!Yii::app()->user->checkAccess(
                            'CredentialsSelect',array('model'=>$model->credentials))) {
							$this->respond(
                                Yii::t('app','Did not send email because you do not have '.
                                    'permission to use the specified credentials.'),1);
                        }
					$sendStatus = $model->send($makeEvent);
					// $sendStatus = array('code'=>'200','message'=>'sent (testing)');

					$failed = $sendStatus['code'] != '200';
					$message = $sendStatus['message'];
				} else if($model->modelName == 'Quote' && empty($model->template)) {
					// Fill in the gap with the default / "semi-legacy" quotes view
					$model->message = $this->controller->renderPartial(
                        'application.modules.quotes.views.quotes.print',
                        array('model' => $model->targetModel,'email' => true), true);

					// Add a linebreak at the beginning for user-entered notes in the email:
					$model->insertInBody('<br />',1);
				}
			}

			// Populate response data:
			$modelHasErrors = $model->hasErrors();
			$failed = $failed || $modelHasErrors;
            $model->attachments = array (); // prevent response json encoding failures
			$response = array(
				'scenario' => $scenario,
				'sendStatus' => $sendStatus,
				'attributes' => $model->attributes,
				'modelErrors' => $model->errors,
				'modelHasErrors' => $modelHasErrors,
				'modelErrorHtml' => CHtml::errorSummary(
                    $model,Yii::t('app', "Please fix the following errors:"),
                    null,
                    array(
                        'style'=>'margin-bottom: 5px;',
                        'class'=>''
                    )),
			);
			if($scenario == 'template') {
				// There's a chance the inline email form is switching gears into
				// quote mode, in which case we need to include templates and
				// insertable attributes for setting it all up properly:
				$response['insertableAttributes'] = $model->insertableAttributes;
				$templates = array(0=>Yii::t('docs','Custom Message')) + 
                    Docs::getEmailTemplates($model->modelName=='Quote'?'quote':'email',
                    $_POST['associationType']);
				$response['templateList'] = array();
				foreach($templates as $id=>$templateName) {
					$response['templateList'][] = array('id'=>$id,'name'=>$templateName);
				}
			}
			$this->mergeResponse($response);

			$this->respond($message,$failed);
		}else{
			$this->respond(
                Yii::t('app', 'Inline email model missing from the request to the server.'), true);
		}
	}

}

?>
