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
 * Model implementing encrypted, generic credentials storage.
 *
 * @property array $authModelLabels (read-only) labels for embedded models;
 *	classes to display labels.
 * @property array $defaultCredentials (read-only) all credential default
 * 	records indexed by user ID and service type
 * @property array $defaultSubstitutes (read-only) a map of service types to
 * 	valid embedded classes for storing data for that service. For example, the
 * 	Google account model can be used for sending email just as well as the
 * 	generic email account model, so it would need to be included among a list of
 *  credentials to use as the default email account.
 * @property array $defaultSubstitutesInv (read-only) Like {@link defaultSubstitutes}
 *	but "inverted"; displays, for a given model class, the list of service types
 *	for which it can act as a stand-in.
 * @property array $serviceLabels (read-only) An array of UI-friendly names for service
 *	keyworkds, i.e. "Email Account" for "email".
 * @package X2CRM.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Credentials extends CActiveRecord {

	private static $_authModelLabels;

	private static $_authModels;

	private static $_defaultCredentials;

	/**
	 * Labels for each type of service.
	 * @var array
	 */
	private $_serviceLabels = array(
		'email' => 'Email Account',
//		'google' => 'Google Account'
	);

	/**
	 * Model classes to include/list as valid for storing auth data
	 * @var array
	 */
	protected $validModels = array('EmailAccount', 'GMailAccount');

	public function attributeLabels() {
		$attrLabels = array(
			'name' => 'Name',
			'userId' => 'Owner',
			'private' => 'Private',
			'isEncrypted' => 'Encryption Enabled',
			'createDate' => 'Date Created',
			'lastUpdated' => 'Date Last Updated',
			'auth' => 'Authentication Details'
		);
		foreach(array_keys($attrLabels) as $attr) {
			$attrLabels[$attr] = Yii::t('app',$attrLabels[$attr]);
		}
		return $attrLabels;
	}


	public function behaviors(){
		return array(
			array(
				'class' => 'application.components.JSONEmbeddedModelFieldsBehavior',
				'transformAttributes' => array('auth'),
				'templateAttr' => 'modelClass',
				'encryptedFlagAttr' => 'isEncrypted',
			),
		);
	}

	/**
	 * Returns the model with default credentials for a given type.
	 * @param type $userId The ID of the user whose credentials are being looked up
	 * @param type $type The type of service for which credentials are being looked up
	 */
	public function findDefault($userId, $serviceType){
		if(array_key_exists($userId, $this->defaultCredentials)){
			if(array_key_exists($serviceType, $this->defaultCredentials[$userId])){
				return self::model()->findByPk($this->defaultCredentials[$userId][$serviceType]);
			}
		}
		// Fallback: return the first model found associated with user ID that meets the criteria for use ($serviceType)
		$criteria = new CDbCriteria(array('condition' => 'WHERE `userId`=:uid', 'params' => array(':uid' => $userId)));
		if(array_key_exists($serviceType, $this->defaultSubstitutes)){
			if(count($this->defaultSubstitutes[$serviceType])){
				$criteria->addInCondition('model', $this->defaultSubstitutes[$serviceType]);
			}
		}
		return self::model()->find($criteria);
	}

	/**
	 * An array of credential storage model objects, for reference
	 * @return type
	 */
	public function getAuthModels(){
		if(!isset(self::$_authModels)){
			self::$_authModels = array();
			foreach($this->validModels as $class){
				self::$_authModels[$class] = new $class;
			}
		}
		return self::$_authModels;
	}

	/**
	 * Getter for {@link authModelLabels}
	 * @return type
	 */
	public function getAuthModelLabels(){
		if(!isset(self::$_authModelLabels)){
			self::$_authModelLabels = array();
			foreach($this->authModels as $class => $model){
				self::$_authModelLabels[$class] = $model->modelLabel();
			}
		}
		return self::$_authModelLabels;
	}

	/**
	 * Getter for {@link defaultCredentials}
	 * @param type $d
	 * @return type
	 */
	public function getDefaultCredentials($refresh=false){
		if(!isset(self::$_defaultCredentials) || $refresh){
			$allDefaults = Yii::app()->db->createCommand()->select('*')->from('x2_credentials_default')->queryAll();
			self::$_defaultCredentials = array_fill_keys(array_map(function($d){
								return $d['userId'];
							}, $allDefaults), array());
			foreach($allDefaults as $d){
				self::$_defaultCredentials[$d['userId']][$d['serviceType']] = $d['credId'];
			}
		}
		return self::$_defaultCredentials;
	}

	/**
	 * Returns the value for {@link defaultSubstitutes}
	 */
	public function getDefaultSubstitutes(){
		return array(
			'email' => array('EmailAccount', 'GMailAccount'),
//			'google' => array('GMailAccount'),
		);
	}

	/**
	 * Returns the value for {@link defaultSubstitutesInv}
	 */
	public function getDefaultSubstitutesInv() {
		return array(
			'EmailAccount' => array('email'),
			'GMailAccount' => array('email') // ,'google'),
		);
	}

	/**
	 * Returns an appropriate title for create/update pages.
	 * @return type
	 */
	public function getPageTitle() {
		return $this->isNewRecord ? Yii::t('app', "New {service}", array('{service}' => $this->serviceLabel)) : Yii::t('app', 'Editing:')." <em>{$this->name}</em> ({$this->serviceLabel})";
	}

	/**
	 * Obtains the service type label (UI-friendly name for the category of credentials)
	 * @return type
	 */
	public function getServiceLabel() {
		return $this->authModelLabels[$this->modelClass];
	}

	/**
	 * Gets translated labels for each service type ({@link serviceLabels})
	 * @return array
	 */
	public function getServiceLabels(){
		$translated = array();
		foreach($this->_serviceLabels as $type => $label)
			$translated[$type] = Yii::t('app', $label);
		return $translated;
	}

	/**
	 * Gets a UI-friendly list of substitute classes to names for the current
	 * embedded model (i.e. for a selector of services for which the current
	 * credentials should be used as default)
	 * @return array
	 */
	public function getSubstituteLabels() {
		$subInv = $this->defaultSubstitutesInv[$this->modelClass];
		$subLab = array();
		$serviceLabels = $this->getServiceLabels();
		foreach($subInv as $serviceType) {
			$subLab[$serviceType] = $serviceLabels[$serviceType];
		}
		return $subLab;
	}

	/**
	 * Generates a select input for a form that includes a list of credentials
	 * available for the current user.
	 * @param CModel $model Model whose attribute is being used to specify a set of credentials
	 * @param string $name Attribute storing the ID of the credentials record
	 * @param string $type Keyword specifying the "service type" (i.e. "email" encompasess credentials with modelClass "EmailAccount" and "GMailAccount"
	 */
	public static function selectorField($model,$name,$type='email') {
		// First get credentials available to the user:
		$uid = Yii::app()->user->id;
		$criteria = new CDbCriteria();
		$staticModel = self::model();
		$criteria->addInCondition('modelClass',$staticModel->defaultSubstitutes[$type]);
		$criteria->addCondition('userId='.$uid.' OR userId IS NULL');
		$creds = $staticModel->findAll($criteria);
		$credentials = array();
		// Figure out which one is default:
		$defaultCreds = $staticModel->getDefaultCredentials();
		$selectedCredentials = -1;
		if(array_key_exists($uid,$defaultCreds))
			if(array_key_exists($type,$defaultCreds[$uid]))
				$selectedCredentials = $defaultCreds[$uid][$type];
		foreach($creds as $cred) {
			$credentials[$cred->id] = $cred->name;
			if($cred->id == $selectedCredentials) {
				$credentials[$cred->id] .= ' ('.Yii::t('app','Default').')';
			}
			if($type == 'email')
				$credentials[$cred->id] = Formatter::truncateText($credentials[$cred->id].' : "'.$cred->auth->senderName.'" <'.$cred->auth->email.'>',50);
		}
		$credentials[-1] = Yii::t('app','System default (legacy)');

		$options = array();
		foreach($credentials as $credId => $label) {
			if($credId == $selectedCredentials) {
				$options[$credId] = array('selected'=>'selected');
			} else {
				$options[$credId] = array('selected' => false);
			}
		}
		return CHtml::activeDropDownList($model,$name,$credentials,array('options'=>$options));
	}


	/**
	 * Given a user id, returns an array of all service types for which the record is default.
	 * @param integer $uid
	 * @return array
	 */
	public function isDefaultOf($uid){
		$services = array();
		if(array_key_exists($uid,$this->defaultCredentials)) {
			foreach($this->defaultCredentials[$uid] as $service => $id) {
				if($id == $this->id)
					$services[] = $service;
			}
		}
		return $services;
	}


	/**
	 * Set the default account for a given user to use for a given service.
	 * @param type $userId ID of the user whose default is getting set. Null for generic/system account.
	 * @param type $serviceType Service type, i.e. 'email'
	 */
	public function makeDefault($userId, $serviceType){
		Yii::app()->db->createCommand()
				->delete('x2_credentials_default', 'userId=:uid AND serviceType=:st', array(
					':uid' => $userId,
					':st' => $serviceType
				));
		Yii::app()->db->createCommand()
				->insert('x2_credentials_default', array(
					'userId' => $userId,
					'serviceType' => $serviceType,
					'credId' => $this->id
				));
	}

	public static function model($className = __CLASS__){
		return parent::model($className);
	}

	public function rules() {
		return array(
			array('name,private,auth','safe'),
			array('userId','safe','on'=>'admin')
		);
	}

	public function tableName(){
		return 'x2_credentials';
	}

}

?>
