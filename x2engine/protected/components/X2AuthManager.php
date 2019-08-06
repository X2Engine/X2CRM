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
 * RBAC auth manager for X2Engine
 * 
 *
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
    private $_assignments = array();
    protected $_usernames = array();

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
        if (!isset($params['userId']))
            $params['userId'] = $userId;
        if (!isset($this->_access))
            $this->_access = array();

        if (isset($this->_access[$userId][$itemName]) && 
            !empty($this->_access[$userId][$itemName])) {

            $checkParams = $this->getCacheParams($params);
            if ($checkParams !== false) {
                $checkParams = json_encode ($checkParams);

                // Shortcut 1: return data stored in the component's property
                if (isset ($this->_access[$userId][$itemName][$checkParams])) {
                    return $this->_access[$userId][$itemName][$checkParams];
                }
            }
        } else if ($this->caching) {

            // Shortcut 2: load the auth cache data and return if a result was found
            if (!isset($this->_access[$userId])) {
                $this->_access[$userId] = Yii::app()->authCache->loadAuthCache($userId);
            }
            if (isset($this->_access[$userId][$itemName]) && 
                !empty($this->_access[$userId][$itemName])) {

                $checkParams = $this->getCacheParams($params);

                if ($checkParams !== false) {
                    $checkParams = json_encode ($checkParams);

                    if (isset ($this->_access[$userId][$itemName][$checkParams])) {
                        return $this->_access[$userId][$itemName][$checkParams];
                    }
                }
            }
        }

        if (!isset($this->_access[$userId]))
            $this->_access[$userId] = array();
        if (!isset($this->_access[$userId][$itemName]))
            $this->_access[$userId][$itemName] = array();

        // Get assignments via roles.
        //
        // In X2Engine's system, x2_auth_assignment doesn't refer to users, but
        // to roles. Hence, the ID of each role is sent to 
        // parent::getAuthAssignments rather than a user ID, which would be
        // meaningless in light of how x2_auth_assignment stores roles.
        if (isset($this->_assignments[$userId])) {
            $assignments = $this->_assignments[$userId];
        } else {
            $roles = Roles::getUserRoles($userId);
            $assignments = array();
            foreach ($roles as $roleId) {
                $assignments = array_merge($assignments, parent::getAuthAssignments($roleId));
            }
            $this->_assignments[$userId] = $assignments;
        }

        // Prepare the username for the session-agnostic permissions check:
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

        
        // Get whether the user has access:
        $hasAccess = parent::checkAccessRecursive($itemName, $userId, $params, $assignments);

        // Store locally.
        $cacheParams = $this->getCacheParams($params);
        if ($cacheParams !== false) {
            $this->_access[$userId][$itemName][json_encode ($cacheParams)] = $hasAccess;

            // Cache
            if ($this->caching) {
                Yii::app()->authCache->addResult($userId, $itemName, $hasAccess, $cacheParams);
            }
        }

        return $hasAccess;
    }

    protected function getCacheParams(array $params) {
        $ret = false;
        unset($params['userId']);
        if ($params == array ()) {
            return array ();
        } elseif (isset($params['X2Model']) && count ($params) === 1 && 
            $params['X2Model']->asa('permissions') != null) {

            $ret = array ();
            $ret['modelType'] = get_class($params['X2Model']);
            $assignmentAttr = $params['X2Model']->getAssignmentAttr();
            if($assignmentAttr){
                $ret[$assignmentAttr] = $params['X2Model']->$assignmentAttr;
            }
        } else {
            $simpleParamFlag = true;
            foreach ($params as $param) {
                if (!is_scalar ($param)) {
                    $simpleParamFlag = false;
                    break;
                }
            }
            if ($simpleParamFlag) {
                $ret = $params;
            }
        }
        return $ret;
    }

    /**
     * Checks for admin access on a specific named module.
     *
     * Originally written as a kludge to bypass checking for overall admin access when
     * performing a generic admin action that is specific to a module. Specifically, it
     * was written for exporting models as a fix for 4.1.6, wherein otherwise a user would
     * need full admin rights and not just contact module admin rights to export contacts.
     *
     * Note, since this starts its own chain of recursive access checking, extreme caution
     * should be used when using this method inside of a business rule, because infinite 
     * loops could potentially occur.
     *
     * @param array $params An associative array that is presumed to contain a "userId"
     *  element that refers to the user ID (as if $params is as within a business rule),
     *  and also expects a model (or module) parameter.
     */
    public function checkAdminOn($params) {
        if (!isset($params['userId']))
            return false;

        // Look in the $_GET superglobal for 'model' if the 'model' parameter is not available
        $modelName = isset($params['model']) ? ($params['model'] instanceof X2Model ? get_class($params['model']) : $params['model']) : (isset($_GET['model']) ? $_GET['model'] : null);

        // Determine the module on which admin access will be checked, based on a model class:
        if (empty($params['module']) && !empty($modelName)) {
            if (($staticModel = X2Model::model($modelName)) instanceof X2Model) {
                if (($lb = $staticModel->asa('LinkableBehavior')) instanceof LinkableBehavior) {
                    $module = !empty($lb->module) ? $lb->module : null;
                }
            }
        }
        if (!isset($module)) // Check if module parameter is specified and use it if so:
            $module = isset($params['module']) ? $params['module'] : null;

        if (!empty($module)) {
            // Perform a check for the existence of the item name (because, per the original 
            // design of X2Engine's permissions, for backwards compatibility: if no auth 
            // item exists, permission will be granted by default).
            $itemName = ucfirst($module) . 'AdminAccess';
            if (!(bool) $this->getAuthItem($itemName))
                return false;
        } else {
            // Use the generic administrator auth item if there is no module specified:
            $itemName = 'administrator';
        }
        //AuxLib::debugLogR(compact('params','itemName','userId','module','modelName'));
        return $this->checkAccess($itemName, $params['userId'], $params);
    }

    /**
     * Assignment check function for business rules. Note that this method does not check for 
     * assignment to "Anyone". At the time of this writing, checkAssignment is used exclusively
     * for checking permissions related to private access.
     * @param array $params
     * @return boolean
     */
    public function checkAssignment($params) {
        return isset($params['X2Model']) && $params['X2Model']->asa('permissions') && $params['X2Model']->isAssignedTo($this->_usernames[$params['userId']], true);
    }

    /**
     * Visibility check function for business rules
     * 
     * @param array $params
     * @return boolean
     */
    public function checkVisibility($params) {
        return isset($params['X2Model']) && $params['X2Model']->asa('permissions') && $params['X2Model']->isVisibleTo($this->_usernames[$params['userId']]);
    }

}

?>
