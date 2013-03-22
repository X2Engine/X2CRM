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
 * InlineEmail class. InlineEmail is the data structure for keeping inline email
 * data. It is used by the InlineEmailForm component and site/inlineEmail.
 * @package X2CRM.models
 */
class InlineEmail extends CFormModel {

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
	public $status;
	/**
	 * @var array
	 */
	public $mailingList = array();

	/**
	 * Declares the validation rules. The rules state that username and password
	 * are required, and password needs to be authenticated.
	 * @return array
	 */
	public function rules() {
		return array(
			array('to, subject', 'required'),
			array('message', 'required', 'on' => 'custom'),
			array('to', 'parseMailingList'),
			array('cc', 'parseMailingList'),
			array('bcc', 'parseMailingList'),
			array('emailSendTime', 'date','allowEmpty'=>true,'timestampAttribute'=>'emailSendTimeParsed'),
			array('to, cc, bcc, message, template, modelId, modelName', 'safe'),
		);
    }

	/**
	 * Declares attribute labels.
	 * @return array
	 */
	public function attributeLabels() {
		return array(
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
	 * Validation function for lists of email addresses.
	 * 
	 * @param string $attribute
	 * @param array $params
	 */
	public function parseMailingList($attribute, $params) {

		// $to = trim($this->$attribute);
		// if(empty($to))
		// return false;

		$splitString = explode(',', $this->$attribute);

		// require_once('protected/components/phpMailer/class.phpmailer.php');
		$invalid = false;

		foreach ($splitString as &$token) {

			$token = trim($token);
			if (empty($token))
			continue;

			$matches = array();

			$emailValidator = new CEmailValidator;

			// if(PHPMailer::ValidateAddress($token)) {	// if it's just a simple email, we're done!

			if ($emailValidator->validateValue($token)) // if it's just a simple email, we're done!
			$this->mailingList[$attribute][] = array('', $token);
			elseif (strlen($token) < 255 && preg_match('/^"?([^"]*)"?\s*<(.+)>$/i', $token, $matches)) { // otherwise, it must be of the variety <email@example.com> "Bob Slydel"
			if (count($matches) == 3 && $emailValidator->validateValue($matches[2])) {     // (with or without quotes)
				$this->mailingList[$attribute][] = array($matches[1], $matches[2]);
			} else {
				$invalid = true;
				break;
			}
			} else {
			$invalid = true;
			break;
			}
		}

		if ($invalid)
			$this->addError($attribute, Yii::t('app', 'Invalid email address list.'));
	}

	// public function __get($name) {
	// if($name == '_mailingList')
	// return $this->_mailingList;
	// else
	// return null;
	// }
}
