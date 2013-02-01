<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
 * ****************************************************************************** */

class X2WebUser extends CWebUser{
	
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
}
