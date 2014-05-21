<?php

/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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
 * RBAC auth manager for X2Engine
 *
 * @package application.components
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class X2AuthManager extends CDbAuthManager {


    public $caching = true;

    /**
     * Stores auth data in the scope of the current request
     *
     * @var type
     */
    private $_access;

    /**
     * Internal "cache" of user names
     * @var type
     */
    private $_usernames = array();

    /**
     * Access check function.
     *
     * Checks access and attempts to speed up all future access checks using
     * caching and storage of the variable within {@link _access}.
     * 
     * Note, only if parameters are empty will permissions caching or storage
     * in {@link _access} be effective, because parameters (i.e. the assignment
     * of a record based on the value of its assignedTo field) are expected to
     * vary. For example, in record-specific permission items checked for
     * multiple records. That is why $params be empty for any shortcuts to be
     * taken.
     *
     * @param string $itemName Name of the auth item for which access is being checked
     * @param integer $userId ID of the user for which to check access
     * @param array $params Parameters to pass to business rules
     * @return boolean
     */
    public function checkAccess($itemName, $userId, $params = array()) {
        if(!isset($this->_access))
            $this->_access = array();

        if(isset($this->_access[$userId][$itemName])) {
            // Shortcut 1: return data stored in the component's property
            return $this->_access[$userId][$itemName];
        } else if($this->caching && empty($params)) {
            // Shortcut 2: load the auth cache data and return if a result was found
            if(!isset($this->_access[$userId]))
                $this->_access[$userId] = Yii::app()->authCache->loadAuthCache($userId);
            if(isset($this->_access[$userId][$itemName]))
                return $this->_access[$userId][$itemName];
        } else {
            // Merely prepare _access[$userId]
            if(!isset($this->_access[$userId]))
                $this->_access[$userId] = array();
        }

        // Get assignments via roles.
        //
        // In X2Engine's system, x2_auth_assignment doesn't refer to users, but
        // to roles. Hence, the ID of each role is sent to 
        // parent::getAuthAssignments rather than a user ID, which would be
        // meaningless in light of how x2_auth_assignment stores roles.
        $roles = Roles::getUserRoles($userId);
        $assignments = array();
        foreach($roles as $roleId) {
            $assignments = array_merge($assignments,
                    parent::getAuthAssignments($roleId));
        }

        // Prepare the username for the session-agnostic permissions check:
        if(!isset($this->_usernames[$userId])) {
            if($userId == Yii::app()->getSuId())
                $user = Yii::app()->getSuModel();
            else
                $user = User::model()->findByPk($userId);
            if($user instanceof User)
                $this->_usernames[$userId] = $user->username;
            else
                $this->_usernames[$userId] = 'Guest';
        }
        if(!isset($params['userId']))
            $params['userId'] = $userId;

        // Get whether the user has access:
        $hasAccess = parent::checkAccessRecursive($itemName, $userId, $params, $assignments);

        if(empty($params)) {
            // Store locally.
			$this->_access[$userId][$itemName] = $hasAccess;
            // Cache
            if($this->caching)
                Yii::app()->authCache->addResult($userId,$itemName,$hasAccess);
		}

        return $hasAccess;
    }

    /**
     * Assignment check function for business rules
     *
     * @param array $params
     * @return boolean
     */
    public function checkAssignment($params){
        return isset($params['X2Model'])
                && $params['X2Model'] instanceof X2Model
                && $params['X2Model']->isAssignedTo($this->_usernames[$params['userId']]);
    }

    /**
     * Visibility check function for business rules
     * 
     * @param array $params
     * @return boolean
     */
    public function checkVisibility($params) {
        return isset($params['X2Model'])
                && $params['X2Model'] instanceof X2Model
                && $params['X2Model']->isVisibleTo($this->_usernames[$params['userId']]);
    }

}

?>
