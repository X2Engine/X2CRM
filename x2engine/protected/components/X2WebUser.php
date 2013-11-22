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
 ********************************************************************************/

class X2WebUser extends CWebUser {

	private $_keyPrefix;
	// private $_access=array();
	private $_access = null;

	public function checkAccess($operation,$params=array(),$allowCaching=true) {

		// return true;
		if($allowCaching && $params===array()) {

			if($this->_access===null)
				$this->_access = Yii::app()->authCache->loadAuthCache($this->getId());

			if(isset($this->_access[$operation]))
				return $this->_access[$operation];

			// if(isset($this->_access[$operation]))
				// return $this->_access[$operation];
			// if(($result = Yii::app()->authCache->checkResult($this->getId(),$operation)) !== null)
				// return $result;
		}
		// $GLOBALS['access'][] = $operation;

		$result = Yii::app()->getAuthManager()->checkAccess($operation,$this->getName(),$params);
		foreach(Yii::app()->params['roles'] as $roleId) {
			if($result = ($result || Yii::app()->getAuthManager()->checkAccess($operation,$roleId,$params)))
				break;
		}

		// $test = X2Model::model('Contacts')->findAllByAttributes(array('company'=>2));
		// $GLOBALS['accessCount'] = isset($GLOBALS['accessCount'])? $GLOBALS['accessCount']+1 : 1;

		// $roles=RoleToUser::model()->findAllByAttributes(array('userId'=>$this->getId()));
		// foreach($roles as $role){
			// $roleRecord=Roles::model()->findByPk($role->roleId);
			// if(isset($roleRecord))
				// $result=$result || Yii::app()->getAuthManager()->checkAccess($operation,$roleRecord->id,$params);
		// }

		if($allowCaching && $params===array()) {
			$this->_access[$operation] = $result;
			Yii::app()->authCache->addResult($this->getId(),$operation,$result);
		}
		return $result;
	}

	/**
	 * Runs the user_login automation trigger
	 *
	 * @param $fromCookie whether the login was automatic (cookie-based)
	 */
	protected function afterLogin($fromCookie) {
		if(!$fromCookie) {
			X2Flow::trigger('UserLoginTrigger',array(
				'user'=>$this->getName()
			));
		}
	}

	/**
	 * Runs the user_logout automation trigger
	 *
	 * @return boolean whether or not to logout
	 */
	protected function beforeLogout() {
		X2Flow::trigger('UserLogoutTrigger',array(
			'user'=>$this->getName()
		));
		return parent::beforeLogout();
	}
}
