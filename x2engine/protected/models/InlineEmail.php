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

Yii::import('application.modules.docs.models.*');
Yii::import('application.modules.actions.models.*');
Yii::import('application.modules.contacts.models.*');
Yii::import('application.modules.quotes.models.*');

/**
 * InlineEmail class. InlineEmail is the data structure for taking in and
 * processing data for outbound email. It is used by the InlineEmailForm
 * component and site/inlineEmail.
 *
 * The following describes the scenarios of this model:
 * - "custom" is used when a modified email has been submitted for processing or
 * 		sending
 * - "template" is used when the form has been submitted to re-create the email
 * 		based on a template.
 * - Blank/empty string is for when there's a new and blank email (i.e. initial
 *		rendering of the inline email widget {@link InlineEmailForm})
 *
 * @property string $actionHeader (read-only) A mock-up of the email's header
 * 	fields to be inserted into the email actions' bodies, for display purposes.
 * @property array $from The sender of the email.
 * @property PHPMailer $mailer PHPMailer instance
 * @property array $insertableAttributes (read-only) Attributes for the inline
 *	email editor that can be inserted into the message.
 * @property array $recipientContacts (read-only) an array of contact records
 * 	identified by recipient email address.
 * @property array $recipients (read-only) an array of all recipients of the email.
 * @property string $signature Signature of the user sending the email, if any
 * @property X2Model $targetModel The model associated with this email, i.e.
 * 	Contacts or Quote
 * @property Docs $templateModel (read-only) template, if any, to use.
 * @property string $trackingImage (read-only) Markup for the tracking image to
 * 	be placed in the email
 * @property string $uniqueId A unique ID used for the tracking record and
 * 	tracking image URL
 * @property Profile $userProfile Profile, i.e. for email sender and signature
 * @package X2CRM.models
 */
class InlineEmail extends CFormModel {

	// Enclosure comments:
	const SIGNATURETAG = 'Signature'; // for signature
	const TRACKTAG = 'OpenedEmail'; // for the tracking image
	const AHTAG = 'ActionHeader'; // for the inline action header

	const UIDREGEX = '/uid.([0-9a-f]{32})/';

	/**
	 * @var string Email address of the addressee
	 */
	public $to;

	/**
	 * @var string CC email address(es), if applicable
	 */
	public $cc;

	/**
	 * @var string BCC email address(es), if applicable
	 */
	public $bcc;

	/**
	 * @var string Email subject
	 */
	public $subject;

	/**
	 * @var string Email body/content
	 */
	public $message;

	/**
	 * @var array Sender address
	 */
	private $_from;

	/**
	 * @var strng Email Send Time
	 */
	public $emailSendTime = '';

	/**
	 * @var int Email Send Time in unix timestamp format
	 */
	public $emailSendTimeParsed = 0;

	/**
	 * @var integer Template ID
	 */
	public $template = 0;

	/**
	 * Stores the name of the model associated with the email i.e. Contacts or Quote.
	 * @var string
	 */
	public $modelName;

	/**
	 * @var integer
	 */
	public $modelId;

	/**
	 * @var array Status codes
	 */
	public $status = array();

	/**
	 * @var array
	 */
	public $mailingList = array();
	public $attachments = array();
	public $emailBody = '';
	public $preview = false;
	public $stageEmail = false;
	private $_recipientContacts;

	/**
	 * Stores value of {@link actionHeader}
	 * @var string
	 */
	private $_actionHeader;

	/**
	 * Stores value of {@link insertableAttributes}
	 * @var array
	 */
	private $_insertableAttributes;

	/**
	 * Stores an instance of PHPMailer
	 * @var PHPMailer
	 */
	private $_mailer;

	/**
	 * Stores value of {@link recipients}
	 * @var array
	 */
	private $_recipients;

	/**
	 * Stores value of {@link signature}
	 * @var string
	 */
	private $_signature;

	/**
	 * Stores value of {@link targetModel}
	 * @var X2Model
	 */
	private $_targetModel;

	/**
	 * Stores value of {@link templateModel}
	 */
	private $_templateModel;

	/**
	 * Stores value of {@link trackingImage}
	 * @var string
	 */
	private $_trackingImage;

	/**
	 * Stores value of {@link uniqueId}
	 * @var type
	 */
	private $_uniqueId;

	/**
	 * Stores value of {@link userProfile}
	 * @var Profile
	 */
	private $_userProfile;

	/**
	 * Declares the validation rules. The rules state that username and password
	 * are required, and password needs to be authenticated.
	 * @return array
	 */
	public function rules(){
		return array(
			array('to, subject', 'required','on'=>'custom'),
			array('modelName,modelId', 'required', 'on' => 'template'),
			array('message', 'required', 'on' => 'custom'),
			array('to,cc,bcc', 'parseMailingList'),
			array('emailSendTime', 'date', 'allowEmpty' => true, 'timestampAttribute' => 'emailSendTimeParsed'),
			array('to, cc, bcc, message, template, modelId, modelName, subject', 'safe'),
		);
	}

	/**
	 * Declares attribute labels.
	 * @return array
	 */
	public function attributeLabels(){
		return array(
			'from' => Yii::t('app', 'From:'),
			'to' => Yii::t('app', 'To:'),
			'cc' => Yii::t('app', 'CC:'),
			'bcc' => Yii::t('app', 'BCC:'),
			'subject' => Yii::t('app', 'Subject:'),
			'message' => Yii::t('app', 'Message:'),
			'template' => Yii::t('app', 'Template:'),
			'modelName' => Yii::t('app', 'Model Name'),
			'modelId' => Yii::t('app', 'Model ID'),
		);
	}

	/**
	 * Creates a pattern for finding or inserting content into the email body.
	 *
	 * @param string $name The name of the pattern to use. There should be a
	 * 	constant defined that is the name in upper case followed by "TAG" that
	 * 	specifies the name to use in comments that demarcate the inserted content.
	 * @param string $inside The content to be inserted between comments.
	 * @param bool $re Whether to return the pattern as a regular expression
	 * @param string $reFlags PCRE flags to use in the expression, if $re is enabled.
	 */
	public static function insertedPattern($name, $inside, $re = 0, $reFlags = ''){
		$tn = constant('self::'.strtoupper($name.'tag'));
		$tag = "<!--Begin$tn-->~inside~<!--End$tn--!>";
		if($re)
			$tag = '/'.preg_quote($tag)."/$reFlags";
		return str_replace('~inside~', $inside, $tag);
	}

	/**
	 * Magic getter for {@link actionHeader}
	 *
	 * Composes an informative header for the action record.
	 *
	 * @return type
	 */
	public function getActionHeader(){
		if(!isset($this->_actionHeader)){

			// Add email headers to the top of the action description's body
			// so that the resulting recorded action has all the info of the
			// original email.
			$fromString = $this->from['address'];
			if(!empty($this->from['name']))
				$fromString = '"'.$this->from['name'].'" <'.$fromString.'>';

			$header = CHtml::tag('strong', array(), Yii::t('app','Subject: ')).CHtml::encode($this->subject).'<br />';
			$header .= CHtml::tag('strong', array(), Yii::t('app','From: ')).CHtml::encode($fromString).'<br />';
			// Put in recipient lists, and if any correspond to contacts, make links
			// to them in place of their names.
			foreach(array('to', 'cc', 'bcc') as $recList){
				if(!empty($this->mailingList[$recList])){
					$header .= CHtml::tag('strong', array(), ucfirst($recList).': ');
					foreach($this->mailingList[$recList] as $target){
						if($this->recipientContacts[$target[1]] != null){
							$header .= $this->recipientContacts[$target[1]]->link;
						}else{
							$header .= CHtml::encode("\"{$target[0]}\"");
						}
						$header .= CHtml::encode(" <{$target[1]}>,");
					}
					$header = rtrim($header,', ').'<br />';
				}
			}

			// Include special quote information if it's a quote being issued or emailed to a random contact
			if($this->modelName == 'Quote'){
				$header .= '<hr />';
				$header .= CHtml::tag('strong', array(), Yii::t('quotes', $this->targetModel->type == 'invoice' ? 'Invoice' : 'Quote')).' &#35;'.$this->targetModel->id;
				$header .= ' '.CHtml::encode($this->targetModel->link.' ('.$this->targetModel->status.')').', '.Yii::t('app', 'Created').' '.$this->targetModel->renderAttribute('createDate');
				$header .= ' '.Yii::t('app', 'Updated').' '.$this->targetModel->renderAttribute('lastUpdated').' by '.$this->userProfile->link;
				$header .= ' '.Yii::t('quotes', 'Expires').' '.$this->targetModel->renderAttribute('expirationDate');
			}

			// Attachments info (include links to media items if
			if(!empty($this->attachments)){
				$header .= '<hr />';
				$header .= CHtml::tag('strong', array(), Yii::t('media', 'Attachments:'))."<br />";
				foreach($this->attachments as $attachment){
					$header .= CHtml::tag('span', array('class' => 'email-attachment-text'), $attachment['filename']).'<br />';
				}
			}

			$this->_actionHeader = $header.'<hr />';
		}
		return $this->_actionHeader;
	}

	public function getFrom() {
		if(!isset($this->_from))
			$this->_from = array(
				'name'=>$this->userProfile->fullName,
				'address'=>$this->userProfile->emailAddress
			);
		return $this->_from;

	}

	public function setFrom($from) {
		$this->_from = $from;
	}

	/**
	 * Magic getter for {@link insertableAttributes}
	 * @return array
	 */
	public function getInsertableAttributes(){
		if(!isset($this->_insertableAttributes)){
			$ia = array();
			if((bool) $this->targetModel){

				$labelFormat = '{attr}';
				$headers = array();
				$models = array();
				switch($this->modelName){
					case 'Quote':
						// There will be many more models whose attributes we want
						// to insert, so prefix each one with the model name to
						// distinguish the current section:
						$labelFormat = '{model}: {attr}';
						$headers = array_merge($headers, array(
							'Accounts' => 'Account Attributes',
							'Quote' => 'Quote Attributes',
							'Contacts' => 'Contact Attributes',
								));
						$models = array_merge($models, array(
							'Accounts' => $this->targetModel->getLinkedModel('accountName'),
							'Contacts' => $this->targetModel->contact,
							'Quote' => $this->targetModel,
								));
						break;
					case 'Contacts':
						$headers = array(
							'Contacts' => 'Contact Attributes',
						);
						$models = array('Contacts' => $this->targetModel);
						break;
					case 'Services':
						$labelFormat = '{model}: {attr}';
						$headers = array(
							'Cases' => 'Case Attributes',
							'Contacts' => 'Contact Attributes',
						);
						$models = array(
							'Cases' => $this->targetModel,
							'Contacts' => Contacts::model()->findByPk($this->targetModel->contactId),
						);
						break;
				}

				$headers = array_map(function($e){return Yii::t('app', $e);}, $headers);

				foreach($headers as $modelName => $title){
					$model = $models[$modelName];
					if($model instanceof CActiveRecord){
						$ia[$title] = array();
						$friendlyName = Yii::t('app', rtrim($modelName, 's'));
						foreach($model->attributeLabels() as $fieldName => $label){
							$attr = trim($this->targetModel->renderAttribute($fieldName, false));
							$fullLabel = strtr($labelFormat, array(
								'{model}' => $friendlyName,
								'{attr}' => $label
									));
							if($attr !== '' && $attr != '&nbsp;')
								$ia[$title][$fullLabel] = $attr;
						}
					}
				}
			}
			$this->_insertableAttributes = $ia;
		}
		return $this->_insertableAttributes;
	}

	/**
	 * Magic getter for {@link phpMailer}
	 * @return \PHPMailer
	 */
	public function getMailer(){
		if(!isset($this->_phpMailer)){
			require_once(realpath(Yii::app()->basePath.'/components/phpMailer/class.phpmailer.php'));

			$phpMail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
			$phpMail->CharSet = 'utf-8';

			switch(Yii::app()->params->admin->emailType){
				case 'sendmail':
					$phpMail->IsSendmail();
					break;
				case 'qmail':
					$phpMail->IsQmail();
					break;
				case 'smtp':
					$phpMail->IsSMTP();

					$phpMail->Host = Yii::app()->params->admin->emailHost;
					$phpMail->Port = Yii::app()->params->admin->emailPort;
					$phpMail->SMTPSecure = Yii::app()->params->admin->emailSecurity;
					if(Yii::app()->params->admin->emailUseAuth == 'admin'){
						$phpMail->SMTPAuth = true;
						$phpMail->Username = Yii::app()->params->admin->emailUser;
						$phpMail->Password = Yii::app()->params->admin->emailPass;
					}
					break;
				case 'mail':
				default:
					$phpMail->IsMail();
			}
			$this->_mailer = $phpMail;
		}
		return $this->_mailer;
	}

	public function getRecipientContacts(){
		if(!isset($this->_recipientContacts)){
			$contacts = array();
			foreach($this->recipients as $target){
				$contacts[$target[1]] = Contacts::model()->findByAttributes(array('email' => $target[1]));
			}
			$this->_recipientContacts = $contacts;
		}
		return $this->_recipientContacts;
	}

	/**
	 * Magic getter for {@link recipients}
	 * @return array
	 */
	public function getRecipients(){
		if(empty($this->_recipients)){
			$this->_recipients = array();
			foreach(array('to', 'cc', 'bcc') as $recList){
				if(!empty($this->mailingList[$recList])){
					foreach($this->mailingList[$recList] as $target){
						$this->_recipients[] = $target;
					}
				}
			}
		}
		return $this->_recipients;
	}

	/**
	 * Magic getter for {@link signature}
	 *
	 * Retrieves the email signature from the preexisting body, or from the
	 * user's profile if none can be found.
	 *
	 * @return string
	 */
	public function getSignature(){
		if(!isset($this->_signature)){
			$this->_signature = $this->getUserProfile()->getSignature(true);
		}
		return $this->_signature;
	}

	/**
	 * Magic getter for {@link targetModel}
	 */
	public function getTargetModel(){
		if(!isset($this->_targetModel)){
			$this->_targetModel = false;
			if(isset($this->modelId, $this->modelName)){
				$this->_targetModel = X2Model::model($this->modelName)->findByPk($this->modelId);
			}
//			if(!(bool) $this->_targetModel)
//				throw new Exception('InlineEmail used on a target model name and primary key that matched no existing record.');
		}
		return $this->_targetModel;
	}

	public function setTargetModel(X2Model $model) {
		$this->_targetModel = $model;
	}

	/**
	 * Magic getter for {@link templateModel}
	 * @return type
	 */
	public function getTemplateModel($id=null) {
		$newTemp = !empty($id);
		if($newTemp) {
			$this->template = $id;
			$this->_templateModel = null;
		} else {
			$id = $this->template;
		}
		if(empty($this->_templateModel)) {
			$this->_templateModel = Docs::model()->findByPk($id);
		}
		return $this->_templateModel;
	}

	/**
	 * Magic getter for {@link trackingImage}
	 * @return type
	 */
	public function getTrackingImage(){
		if(!isset($this->_uniqueId, $this->_trackingImage)){
			$this->_trackingImage = null;
			$trackUrl = null;
			if(!Yii::app()->params->noSession){
				$trackUrl = Yii::app()->controller->createAbsoluteUrl('actions/emailOpened', array('uid' => $this->uniqueId, 'type' => 'open'));
			}else{
				$file = realpath(Yii::app()->basePath.'/../webLeadConfig.php');
				if($file){
					include($file);
					$trackUrl = "$url/index.php/actions/emailOpened?uid={$this->uniqueId}&type=open";
				}
			}
			if($trackUrl != null)
				$this->_trackingImage = '<img src="'.$trackUrl.'"/>';
		}
		return $this->_trackingImage;
	}

	/**
	 * Magic setter for {@link uniqueId}
	 */
	public function getUniqueId(){
		if(empty($this->_uniqueId))
			$this->_uniqueId = md5(uniqid(rand(), true));
		return $this->_uniqueId;
	}

	/**
	 * Magic setter for {@link uniqueId}
	 * @param string $value
	 */
	public function setUniqueId($value){
		$this->_uniqueId = $value;
	}

	/**
	 * Magic getter for {@link userProfile}
	 * @return Profile
	 */
	public function getUserProfile(){
		if(!isset($this->_userProfile)){
			if(empty($this->_userProfile)){
				if(Yii::app()->params->noSession){
					// As a last resort: use admin
					$this->_userProfile = Profile::model()->findByPk(1);
				}else{
					// By default: if no profile was defined, and it's in a web
					// session, use the current user's profile.
					$this->_userProfile = Yii::app()->params->profile;
				}
			}
		}
		return $this->_userProfile;
	}

	/**
	 * Magic setter for {@link userProfile}
	 * @param Profile $profile
	 */
	public function setUserProfile(Profile $profile){
		$this->_userProfile = $profile;
	}

	/**
	 * Validation function for lists of email addresses.
	 *
	 * @param string $attribute
	 * @param array $params
	 */
	public function parseMailingList($attribute, $params = array()){
		$splitString = explode(',', $this->$attribute);
		$invalid = false;

		foreach($splitString as &$token){

			$token = trim($token);
			if(empty($token))
				continue;

			$matches = array();

			$emailValidator = new CEmailValidator;

			if($emailValidator->validateValue($token)) // if it's just a simple email, we're done!
				$this->mailingList[$attribute][] = array('', $token);
			elseif(strlen($token) < 255 && preg_match('/^"?([^"]*)"?\s*<(.+)>$/i', $token, $matches)){ // otherwise, it must be of the variety <email@example.com> "Bob Slydel"
				if(count($matches) == 3 && $emailValidator->validateValue($matches[2])){  // (with or without quotes)
					$this->mailingList[$attribute][] = array($matches[1], $matches[2]);
				}else{
					$invalid = true;
					break;
				}
			}else{
				$invalid = true;
				break;
			}
		}

		if($invalid)
			$this->addError($attribute, Yii::t('app', 'Invalid email address list.'));
	}

	/**
	 * Inserts a signature into the body, if none can be found.
	 * @param array $wrap Wrap the signature in tags (index 0 opens, index 1 closes)
	 */
	public function insertSignature($wrap=array('<br /><br /><span style="font-family:Arial,Helvetica,sans-serif; font-size:0.8em">','</span>')){
		if(preg_match(self::insertedPattern('signature', '(.*)', 1, 'um'), $this->message, $matches)){
			$this->_signature = $matches[1];
		}else{
			$sig = self::insertedPattern('signature', $this->signature);
			if(count($wrap) >= 2) {
				$sig = $wrap[0].$sig.$wrap[1];
			}
			if(strpos($this->message, '{signature}')){
				$this->message = str_replace('{signature}', $sig, $this->message);
			}else if($this->scenario != 'custom'){
				$this->insertInBody($sig);
			}
		}
	}

	/**
	 * Search for an existing tracking image and insert a new one if none are present.
	 *
	 * Parses the tracking image and unique ID out of the body if there are any.
	 *
	 * @param bool $replace Reset the image markup and unique ID, and replace
	 * 	the existing tracking image.
	 */
	public function insertTrackingImage($replace = false){
		$insertNew = true;
		$pattern = self::insertedPattern('track', '(<img.*\/>)', 1, 'u');
		if(preg_match($pattern, $this->message, $matchImg)){
			if($replace){
				// Reset unique ID and insert a new tracking image with a new unique ID
				$this->_trackingImage = null;
				$this->_uniqueId = null;
				$this->message = replace_string($matchImg[0], self::insertedPattern('track', $this->trackingImage), $this->message);
			}else{
				$this->_trackingImage = $matchImg[1];
				if(preg_match(self::UIDREGEX, $this->_trackingImage, $matchId)){
					$this->_uniqueId = $matchId[1];
					$insertNew = false;
				}
			}
		}
		if($insertNew){
			$this->insertInBody(self::insertedPattern('track', $this->trackingImage));
		}
	}

	/**
	 * Inserts something near the end of the body in the HTML email.
	 *
	 * @param string $content The markup/text to be inserted.
	 * @param bool $beginning True to insert at the beginning, false to insert at the end.
	 * @param bool $return True to modify {@link message}; false to return the modified body instead.
	 */
	public function insertInBody($content, $beginning = 0, $return = 0){
		$insertToken = '{content}';
		$bodTag = $beginning ? '<body>' : '</body>';
		$modTag = $beginning ? $bodTag.$insertToken : $insertToken.$bodTag;
		$modBod = str_replace($bodTag, str_replace($insertToken, $content, $modTag), $this->message);
		if($return)
			return $modBod;
		else
			$this->message = $modBod;
	}

	/**
	 * Generate a blank HTML document.
	 *
	 * @param string $content Optional content to start with.
	 */
	public static function emptyBody($content=null) {
		return "<html><head></head><body>$content</body></html>";
	}

	/**
	 * Prepare the email body for sending or customization by the user.
	 */
	public function prepareBody() {
		if(!$this->validate()){
			return false;
		}
		// Replace the existing body, if any, with a template, i.e. for initial
		// set-up or an automated email.
		if($this->scenario == 'template'){
			// Get the template and associated model

			if(!empty($this->templateModel) && (bool) $this->targetModel){
				// Replace variables in the subject and body of the email
				if(empty($this->subject)){
					$this->subject = Docs::replaceVariables($this->templateModel->subject, $this->targetModel);
				}
				if(!empty($this->targetModel)){
					$this->message = Docs::replaceVariables($this->templateModel->text, $this->targetModel, array(
								'{signature}' => self::insertedPattern('signature', $this->signature)
							));
				} else {
					$this->insertInBody('<span style="color:red">'.Yii::t('app','Error: attempted using a template, but the referenced model was not found.').'</span>');
				}
			}else{
				// No template?
				$this->message = self::emptyBody();
				$this->insertSignature();
			}
		}

		return true;
	}

	/**
	 * Performs a send (or stage, or some other action).
	 *
	 * @return array
	 */
	public function send($createEvent = true){
		// The tracking image is inserted at the very last moment before sending
		// so there is no chance of the user monkeying around in the body and
		// deleting it accidentally.
		$this->insertTrackingImage();
		$this->status = $this->deliver();
		if($this->status['code'] == '200')
			$this->recordEmailSent($createEvent); // Save all the actions and events
		return $this->status;
	}

	/**
	 * Save the tracking record for this email.
	 */
	public function trackEmail($actionId){
		$track = new TrackEmail;
		$track->actionId = $actionId;
		$track->uniqueId = $this->uniqueId;
		$track->save();
	}

	/**
	 * Make records of the email in every shape and form.
	 *
	 * This method is to be called only once the email has been sent.
	 *
	 * The basic principle behind what all is happening here: emails are getting
	 * sent to people. Since the "To:" field in the inline email form is not
	 * read-only, the emails could be sent to completely different people. Thus,
	 * creating action and event records must be based exclusively on who the
	 * email is addressed to and not the model from whose view the inline email
	 * form (if that's how this model is being used) is submitted.
	 */
	public function recordEmailSent($makeEvent = true){

		// The record, with action header:
		$emailRecordBody = $this->insertInBody(self::insertedPattern('ah', $this->actionHeader) , 1, 1);
		$now = time();
		if((bool) $this->targetModel){
			if($this->targetModel->hasAttribute('lastActivity')){
				$this->targetModel->lastActivity = $now;
				$this->targetModel->save();
			}
		}

		foreach($this->recipientContacts as $email => $contact){
			$trackEmail = false; // Only one record need be made: the record for the primary contact
			if(!empty($contact)){
				// Skip updating last activity if the contact is the email's "target model".
				if((bool) $this->targetModel){
					if($this->targetModel->id != $contact->id || $this->targetModel->myModelName != 'Contacts'){
						$contact->lastActivity = $now;
						$contact->update(array('lastActivity'));
					}
				}

				// These attributes will be the same regardless of the type of
				// email being sent:
				$action = new Actions;
				$action->completedBy = $this->userProfile->username;
				$action->createDate = $now;
				$action->dueDate = $now;
				$action->completeDate = $now;
				$action->complete = 'Yes';

				// These attributes are context-sensitive and subject to change:
				$action->associationId = $contact->id;
				$action->associationType = strtolower($contact->myModelName);
				$action->type = 'email';
				$action->visibility = isset($contact->visibility) ? $contact->visibility : 1;
				$action->assignedTo = $this->userProfile->username;

				if($this->modelName == 'Quote'){
					// The email is in this case a quote or invoice. However, if
					// the contact is not the primary contact on the quote, the
					// action should be saved as an ordinary email action. That
					// is because there would be no way to navigate back to the
					// email were the email saved as an "email issued" record.
					// This is because, in such records, only the quote and the
					// primary contact of the quote can actually be linked to,
					// due to the limitation of how many associations can be
					// natively stored in the model, whereas in ordinary email
					// records, the contact is linked to directly. Thus, in both
					// cases, a user can locate the original record without
					// having to search for it.
					//
					// It all boils down to this: are we sending the email to the
					// primary contact on the quote? If so, we're "issuing" the
					// quote or invoice. If not, we're just sending an email.
					if(!empty($this->targetModel->contact)){
						if($contact->id == $this->targetModel->contact->id){
							$trackEmail = true;
							$action->associationType = strtolower($this->targetModel->myModelName);
							$action->associationId = $this->targetModel->id;
							$action->type .= '_'.($this->targetModel->type == 'invoice' ? 'invoice' : 'quote');
							$action->visibility = isset($this->targetModel->visibility) ? $this->targetModel->visibility : 1;
							$action->assignedTo = $this->targetModel->assignedTo;
						}
					}
				}else if($this->modelName == 'Contacts' && $this->modelId == $contact->id){
					$trackEmail = true;
				}

				// Set the action's text to the modified email body
				$action->actionDescription = $emailRecordBody;
				// We don't really care about changelog events for emails; they're
				// set in stone anyways.
				$action->disableBehavior('changelog');

				if($action->save()){
					if($trackEmail)
						$this->trackEmail($action->id);
					if($makeEvent){
						$event = new Events;
						$event->type = 'email_sent';
						$event->subtype = 'email';
						$event->associationType = $contact->myModelName;
						$event->associationId = $contact->id;
						$event->user = $this->userProfile->username;
						if($this->modelName == 'Quote'){
							if($this->targetModel->contact->id == $contact->id){
								// Special "quote issued" or "invoice issued" event,
								// but only when the recipient is the primary contact
								// of the quote:
								$event->subtype = 'quote';
								if($this->targetModel->type == 'invoice')
									$event->subtype = 'invoice';
								$event->associationType = $this->modelName;
								$event->associationId = $this->modelId;
							}
						}
						$event->save();
					}

				}
			} // Stuff that happens if a contact record exists with a matching email address
		} // Loop over contacts
	}

	/**
	 * Perform the email delivery with PHPMailer.
	 *
	 * Any special authentication and security should take place in here.
	 *
	 * @throws Exception
	 * @return array
	 */
	public function deliver(){

		$addresses = $this->mailingList;
		$subject = $this->subject;
		$message = $this->message;
		$attachments = $this->attachments;
		$from = $this->from;

		$phpMail = $this->mailer;

		try{
			if($from == null){ // if no from address (or not formatted properly)
				if(empty($this->userProfile->emailAddress))
					throw new Exception('Your profile doesn\'t have a valid email address.');

				$phpMail->AddReplyTo($this->userProfile->emailAddress, $this->userProfile->fullName);
				$phpMail->SetFrom($this->userProfile->emailAddress, $this->userProfile->fullName);
			} else{
				$phpMail->AddReplyTo($from['address'], $from['name']);
				$phpMail->SetFrom($from['address'], $from['name']);
			}

			$this->addEmailAddresses($phpMail, $addresses);

			$phpMail->Subject = $subject;
			// $phpMail->AltBody = $message;
			$phpMail->MsgHTML($message);
			// $phpMail->Body = $message;
			// add attachments, if any
			if($attachments){
				foreach($attachments as $attachment){
					if($attachment['temp']){ // stored as a temp file?
						$file = 'uploads/media/temp/'.$attachment['folder'].'/'.$attachment['filename'];
						if(file_exists($file)) // check file exists
							if(filesize($file) <= (10 * 1024 * 1024)) // 10mb file size limit
								$phpMail->AddAttachment($file);
							else
								throw new Exception("Attachment '{$attachment['filename']}' exceeds size limit of 10mb.");
					} else{ // stored in media library
						$file = 'uploads/media/'.$attachment['folder'].'/'.$attachment['filename'];
						if(file_exists($file)) // check file exists
							if(filesize($file) <= (10 * 1024 * 1024)) // 10mb file size limit
								$phpMail->AddAttachment($file);
							else
								throw new Exception("Attachment '{$attachment['filename']}' exceeds size limit of 10mb.");
					}
				}
			}

			$phpMail->Send();

			// delete temp attachment files, if they exist
			if($attachments){
				foreach($attachments as $attachment){
					if($attachment['temp']){
						$file = 'uploads/media/temp/'.$attachment['folder'].'/'.$attachment['filename'];
						$folder = 'uploads/media/temp/'.$attachment['folder'];
						if(file_exists($file))
							unlink($file); // delete temp file
						if(file_exists($folder))
							rmdir($folder); // delete temp folder
						TempFile::model()->deleteByPk($attachment['id']);
					}
				}
			}

			$this->status['code'] = '200';
			$this->status['message'] = Yii::t('app', 'Email Sent!');
		}catch(phpmailerException $e){
			$this->status['code'] = '500';
			$this->status['message'] = $e->getMessage()." ".$e->getFile()." L".$e->getLine(); //Pretty error messages from PHPMailer
		}catch(Exception $e){
			$this->status['code'] = '500';
			$this->status['message'] = $e->getMessage()." ".$e->getFile()." L".$e->getLine(); //Boring error messages from anything else!
		}
		return $this->status;
	}

	/**
	 * Adds email addresses to a PHPMail object
	 * @param type $phpMail
	 * @param type $addresses
	 */
	public function addEmailAddresses(&$phpMail, $addresses){

		if(isset($addresses['to'])){
			foreach($addresses['to'] as $target){
				if(count($target) == 2)
					$phpMail->AddAddress($target[1], $target[0]);
			}
		} else{
			if(count($addresses) == 2 && !is_array($addresses[0])) // this is just an array of [name, address],
				$phpMail->AddAddress($addresses[1], $addresses[0]); // not an array of arrays
			else{
				foreach($addresses as $target){	 //this is an array of [name, address] subarrays
					if(count($target) == 2)
						$phpMail->AddAddress($target[1], $target[0]);
				}
			}
		}
		if(isset($addresses['cc'])){
			foreach($addresses['cc'] as $target){
				if(count($target) == 2)
					$phpMail->AddCC($target[1], $target[0]);
			}
		}
		if(isset($addresses['bcc'])){
			foreach($addresses['bcc'] as $target){
				if(count($target) == 2)
					$phpMail->AddBCC($target[1], $target[0]);
			}
		}
	}

}
