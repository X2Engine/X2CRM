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
 * Mocks behavior of X2AuthManager for the purposes of testing. Allows permissions to be quickly
 * set in memory without having to prepare complicated database state.
 */

class X2AuthManagerMock extends X2AuthManager {

    private $_access = array ();

    public function clearCache () {
        $this->_access = array ();
    }

    public function checkAccess($itemName, $userId, $params = array()) {
        if (!isset($params['userId']))
            $params['userId'] = $userId;
        $cacheParams = $this->getCacheParams ($params);

        // this if block is required by X2AuthManager::checkAssignment
        if (!isset($this->_usernames[$userId])) {
            if ($userId == Yii::app()->getSuId())
                $user = Yii::app()->getSuModel();
            else
                $user = User::model()->findByPk($userId);
            if ($user instanceof User)
                $this->_usernames[$userId] = $user->username;
            else
                $this->_usernames[$userId] = 'Guest';
        }

        if (isset ($this->_access[$userId][$itemName][CJSON::encode ($cacheParams)])) {
            $permission = $this->_access[$userId][$itemName][CJSON::encode ($cacheParams)];
        } else {
            $permission = $this->checkAccessRecursive ($itemName, $userId, $params, array ());
        }

        return $permission;
    }

    public function setAccess ($itemName, $userId, $params = array (), $access) {
        $params = $this->getCacheParams ($params);
        $this->_access[$userId][$itemName][CJSON::encode ($params)] = $access;
    }

    /**
     * Prepended cache checking condition 
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
	protected function checkAccessRecursive($itemName,$userId,$params,$assignments)
	{
        /* x2modstart */ 
        $cacheParams = $this->getCacheParams ($params);
        if (isset ($this->_access[$userId][$itemName][CJSON::encode ($cacheParams)])) {
            return $this->_access[$userId][$itemName][CJSON::encode ($cacheParams)];
        }
        /* x2modend */  

		if(($item=$this->getAuthItem($itemName))===null)
			return false;
		Yii::trace('Checking permission "'.$item->getName().'"','system.web.auth.CDbAuthManager');
		if(!isset($params['userId']))
		    $params['userId'] = $userId;
		if($this->executeBizRule($item->getBizRule(),$params,$item->getData()))
		{
			if(in_array($itemName,$this->defaultRoles))
				return true;
			if(isset($assignments[$itemName]))
			{
				$assignment=$assignments[$itemName];
				if($this->executeBizRule($assignment->getBizRule(),$params,$assignment->getData()))
					return true;
			}
			$parents=$this->db->createCommand()
				->select('parent')
				->from($this->itemChildTable)
				->where('child=:name', array(':name'=>$itemName))
				->queryColumn();
			foreach($parents as $parent)
			{
				if($this->checkAccessRecursive($parent,$userId,$params,$assignments))
					return true;
			}
		}
		return false;
	}
}

?>
