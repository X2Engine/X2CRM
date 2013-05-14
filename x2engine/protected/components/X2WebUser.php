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
        
		$result = Yii::app()->getAuthManager()->checkAccess($operation,$this->getId(),$params);
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
