<?php

/**
 * This is the model class for table "x2_admin".
 *
 * The followings are the available columns in table 'x2_admin':
 * @property integer $id
 * @property integer $accounts
 * @property integer $sales
 * @property integer $timeout
 * @property string $webLeadEmail
 * @property string $currency
 * @property string $menuOrder
 * @property string $menuVisibility
 * @property string $menuNicknames
 * @property integer $chatPollTime
 * @property integer $ignoreUpdates
 * @property integer $rrId
 * @property string $leadDistribution
 * @property integer $onlineOnly
 * @property string emailFromName
 * @property string emailFromAddr
 * @property string emailUseSignature
 * @property string emailSignature
 * @property string emailType
 * @property string emailHost
 * @property integer emailPort
 * @property string emailUseAuth
 * @property string emailUser
 * @property string emailPass
 * @property string emailSecurity
 * @property integer installDate
 * @property integer updateDate
 * @property integer updateInterval
 */
class Admin extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @return Admin the static model class
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return 'x2_admin';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('emailFromName, emailFromAddr', 'required'),
			array('accounts, sales, timeout, chatPollTime, ignoreUpdates, rrId, onlineOnly, emailPort, installDate, updateDate, updateInterval', 'numerical', 'integerOnly'=>true),
			array('chatPollTime', 'numerical', 'max'=>10000, 'min'=>100),
			array('currency', 'length', 'max'=>3),
			array('emailUseAuth, emailUseSignature', 'length', 'max'=>10),
			array('emailType, emailSecurity', 'length', 'max'=>20),
			array('webLeadEmail, menuOrder, menuNicknames, leadDistribution, emailFromName, emailFromAddr, emailHost, emailUser, emailPass', 'length', 'max'=>255),
			// array('emailSignature', 'length', 'max'=>512),
			array('menuVisibility', 'length', 'max'=>100),
			array('emailSignature', 'length', 'max'=>512),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			// array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
		);
	}
	
	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels() {
		return array(
			'id' => Yii::t('admin','ID'),
			'accounts' => Yii::t('admin','Accounts'),
			'sales' => Yii::t('admin','Sales'),
			'timeout' => Yii::t('admin','Session Timeout'),
			'webLeadEmail' => Yii::t('admin','Web Lead Email'),
			'currency' => Yii::t('admin','Currency'),
			'menuOrder' => Yii::t('admin','Menu Order'),
			'menuVisibility' => Yii::t('admin','Menu Visibility'),
			'menuNicknames' => Yii::t('admin','Menu Nicknames'),
			'chatPollTime' => Yii::t('admin','Chat Poll Time'),
			'ignoreUpdates' => Yii::t('admin','Ignore Updates'),
			'rrId' => Yii::t('admin','Rr'),
			'leadDistribution' => Yii::t('admin','Lead Distribution'),
			'onlineOnly' => Yii::t('admin','Online Only'),
			'emailFromName' => Yii::t('admin','Sender Name'),
			'emailFromAddr' => Yii::t('admin','Sender Email Address'),
			'emailUseSignature' => Yii::t('admin','Email Signatures'),
			'emailSignature' => Yii::t('admin','Default Signature'),
			'emailType' => Yii::t('admin','Method'),
			'emailHost' => Yii::t('admin','Host'),
			'emailPort' => Yii::t('admin','Port'),
			'emailUseAuth' => Yii::t('admin','Authentication'),
			'emailUser' => Yii::t('admin','Username'),
			'emailPass' => Yii::t('admin','Password'),
			'emailSecurity' => Yii::t('admin','Security'),
			'installDate' => Yii::t('admin','Installed'),
			'updateDate' => Yii::t('admin','Last Update'),
			'updateInterval' => Yii::t('admin','Update Interval'),
		);
	}

	public static function getMenuItems($returnSelected = false) {
		// get admin model
		$admin=Admin::model()->findByPk(1);

		$nicknames = explode(":",$admin->menuNicknames);
		$menuOrder = explode(":",$admin->menuOrder);
		$menuVis = explode(":",$admin->menuVisibility);
		
		$menuItems = array();		// assoc. array with correct order, containing realName => nickName
		$selectedItems = array();
		
		for($i=0;$i<count($menuOrder);$i++) {				// load items from menuOrder into $menuItems keys
			$menuItems[$menuOrder[$i]] = Yii::t('app',$nicknames[$i]);	// set values to their (translated) nicknames
			
			if($menuVis[$i] == 1 && $returnSelected)
				$selectedItems[] = $menuOrder[$i];			// but only include them if they are visible (or we need the full list)
		}
		return $returnSelected? array($menuItems,$selectedItems) : $menuItems;
	}
}