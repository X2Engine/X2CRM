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




Yii::import('application.components.util.AuxLib');

/**
 * CModelBehavior class for permissions lookups on classes.
 *
 * X2PermissionsBehavior is a CModelBehavior which allows consistent lookup of
 * access levels and whether or not a user is allowed to view or edit a record.
 *
 * @property string $assignmentAttr Name of the attribute to use for permissions
 * @property string $visibilityAttr Name of the attribute to use for visibility setting
 * @package application.components.permissions
 * @author Jake Houser <jake@x2engine.com>, Demitri Morgan <demitri@x2engine.com>
 * TODO: replace hard-coded references to ", " delimeter with Fields::MULTI_ASSIGNMENT_DELIM
 */
class X2PermissionsBehavior extends ModelPermissionsBehavior {

    /**
     * The access level for administrators.
     *
     * All records, public and private, will be included in indexes and searches.
     */
    const QUERY_ALL = 3;

    /**
     * The access level for users granted general access.
     *
     * All records marked public or viewable to groupmates (providing the user
     * in question shares a group in common with the assignee(s)) will be
     * included in indexes and searches.
     */
    const QUERY_PUBLIC = 2;

    /**
     * The access level for users granted "private" access.
     *
     * Only records assigned to the user in question, or assigned to the user's
     * groups, will be included.
     */
    const QUERY_SELF = 1;

    /**
     * The access level for users granted no access.
     *
     * No records will be retrieved.
     */
    const QUERY_NONE = 0;

    /**
     * This visibility value implies "private"; ordinarily visible only to
     * assignee(s)/owner(s) of the record
     */
    const VISIBILITY_PRIVATE = 0;

    /**
     * This visibility setting implies the record is public/shared, and anyone
     * can view.
     */
    const VISIBILITY_PUBLIC = 1;

    /**
     * This visibility setting implies that the record is visible to the owners
     * and other members of groups to which the owners belong ("groupmates").
     */
    const VISIBILITY_GROUPS = 2;

    /**
     * Used to prefix sql parameters to prevent parameter name conflicts
     */
    const SQL_PARAMS_PREFIX = 'X2PermissionsBehavior';

    private $_assignmentAttr;
    private $_visibilityAttr;

    /**
     * "Caches" whether the assignment applies to a given user, for each user.
     *
     * Keyed with usernames; values are boolean for whether the assignment applies.
     * @var type
     */
    private $_isAssignedTo = array();

    /**
     * Similar to {@link _isAssignedTo} but for visibility, which also utilizes
     * the visibility setting on the model.
     * @var type
     */
    private $_isVisibleTo = array();

    public function clearCache () {
        $this->_isVisibleTo = $this->_isAssignedTo = array ();
    }

    /**
     * Returns a CDbCriteria containing record-level access conditions.
     * @return CDbCriteria
     */
    public function getAccessCriteria(
        $tableAlias = 't', $paramsNamespace = 'X2PermissionsBehavior', $showHidden = false) {

        $criteria = new CDbCriteria;
        $criteria->alias = $tableAlias;
        $accessLevel = $this->getAccessLevel();

        $conditions = $this->getAccessConditions(
                $accessLevel, $tableAlias, $paramsNamespace, $showHidden);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            if (!empty($arr['params']))
                $criteria->params = array_merge($criteria->params, $arr['params']);
        }
        
        return $criteria;
    }

    /**
     * @return array access condition and parameters 
     */
    public function getAccessSQLCondition($tableAlias = 't') {
        $criteria = $this->getAccessCriteria($tableAlias);
        return array('(' . $criteria->condition . ')', $criteria->params);
    }

    /**
     * Returns a number from 0 to 3 representing the current user's access level using the Yii 
     * auth manager.
     * Assumes authItem naming scheme like "ContactsViewPrivate", etc.
     * This method probably ought to overridden, as there is no reliable way to determine the 
     * module a model "belongs" to.
     * @return integer The access level. 0=no access, 1=own records, 2=public records, 3=full access
     */
    public function getAccessLevel($uid=null) {
        $module = ucfirst($this->owner->module);

        if ($uid) {
        } elseif (Yii::app()->isInSession) { // Web request
            $uid = Yii::app()->user->id;
        } else { // User session not available; doing an operation through API or console
            $uid = Yii::app()->getSuID();
        }
        $accessLevel = self::QUERY_NONE;

        if (Yii::app()->params->isAdmin ||
                Yii::app()->authManager->checkAccess($module . 'Admin', $uid)) {

            if ($accessLevel < self::QUERY_ALL)
                $accessLevel = self::QUERY_ALL;
        }elseif (Yii::app()->authManager->checkAccess($module . 'ReadOnlyAccess', $uid)) {
            if ($accessLevel < self::QUERY_PUBLIC)
                $accessLevel = self::QUERY_PUBLIC;
        }elseif (Yii::app()->authManager->checkAccess($module . 'PrivateReadOnlyAccess', $uid)) {
            if ($accessLevel < self::QUERY_SELF)
                $accessLevel = self::QUERY_SELF;
        }

        // level 2 access only works if we consider visibility,
        $visibilityAttr = $this->getVisibilityAttr();
        if ($accessLevel === self::QUERY_PUBLIC && $visibilityAttr === false)
            $accessLevel = self::QUERY_ALL;  // so upgrade to full access

        return $accessLevel;
    }

    /**
     * Resolves/returns the assignment attribute to use in permission checks
     * @return type
     */
    public function getAssignmentAttr() {
        if (!isset($this->_assignmentAttr)) {
            $this->_assignmentAttr = false;
            if ($this->owner->hasAttribute('assignedTo')) {
                return $this->_assignmentAttr = 'assignedTo';
            } elseif ($this->owner->hasAttribute('createdBy')) {
                return $this->_assignmentAttr = 'createdBy';
            } elseif ($this->owner instanceof X2Model) {
                $fields = $this->owner->getFields();
                foreach ($fields as $field) {
                    // Use the first assignment field available:
                    if ($field->type == 'assignment') {
                        $assignAttr = $field->fieldName;
                        return $this->_assignmentAttr = $field->fieldName;
                    }
                }
            }
        }
        return $this->_assignmentAttr;
    }

    /**
     * Resolves/returns the visibility attribute to use in permission checks
     */
    public function getVisibilityAttr() {
        if (!isset($this->_visibilityAttr)) {
            $this->_visibilityAttr = false;
            if ($this->owner->hasAttribute('visibility')) {
                return $this->_visibilityAttr = 'visibility';
            } elseif ($this->owner instanceof X2Model) {
                $fields = $this->owner->getFields();
                foreach ($fields as $field) {
                    // Use the first assignment field available:
                    if ($field->type == 'visibility') {
                        $assignAttr = $field->fieldName;
                        return $this->_visibilityAttr = $field->fieldName;
                    }
                }
            }
        }
        return $this->_visibilityAttr;
    }

    /**
     * Returns visibility dropdown menu options.
     * @return type
     */
    public static function getVisibilityOptions() {
        return array(
            self::VISIBILITY_PUBLIC => Yii::t('app', 'Public'),
            self::VISIBILITY_PRIVATE => Yii::t('app', 'Private'),
            self::VISIBILITY_GROUPS => Yii::t('app', 'User\'s Groups')
        );
    }

    /**
     * Generates SQL condition to filter out records the user doesn't have
     * permission to see.
     *
     * This method is used by the 'accessControl' filter.
     *
     * @param Integer $accessLevel The user's access level. 0=no access, 1=own
     *  records, 2=public records, 3=full access
     * @return String The SQL conditions
     */
    public function getAccessConditions(
        $accessLevel, $tableAlias = 't', $paramsNamespace = 'X2PermissionsBehavior',
        $showHidden = false) {

        $assignmentAttr = $this->getAssignmentAttr();
        $visibilityAttr = $this->getVisibilityAttr();

        $ret = array();
        $prefix = empty($tableAlias) ? '' : "$tableAlias.";

        switch ($accessLevel) {
            case self::QUERY_ALL:
                // User can view everything
                if (!$assignmentAttr || !$visibilityAttr || $showHidden) {
                    $ret[] = array('condition' => 'TRUE', 'operator' => 'AND', 'params' => array());
                } else {
                    $ret[] = array(
                        'condition' =>
                        "NOT (" . $prefix . "$visibilityAttr=" . self::VISIBILITY_PRIVATE . " AND "
                        . $prefix . "$assignmentAttr='Anyone')",
                        'operator' => 'OR',
                        'params' => array());
                }
                break;
            case self::QUERY_PUBLIC:
                // User can view any public (shared) record
                if ($visibilityAttr != false) {
                    $ret[] = array(
                        'condition' => $prefix . "$visibilityAttr=" . self::VISIBILITY_PUBLIC,
                        'operator' => 'OR',
                        'params' => array()
                    );
                }
                // Made visible among the user(s)' groupmates via "User's Groups"
                // visibility setting:
                $groupmatesRegex = self::getGroupmatesRegex();
                if (!empty($groupmatesRegex)) {
                    $ret[] = array(
                        'condition' =>
                        "(" . $prefix . "$visibilityAttr=" . self::VISIBILITY_GROUPS . ' ' .
                        "AND " . $prefix . "$assignmentAttr 
                                REGEXP BINARY :" . $paramsNamespace . "groupmatesRegex)",
                        'operator' => 'OR',
                        'params' => array(
                            ':' . $paramsNamespace . 'groupmatesRegex' => $groupmatesRegex
                        ),
                    );
                }
            // Continue to case for group visibility / assignment
            case self::QUERY_SELF:
                // User can view records they (or one of their groups) own or
                // have permission to view
                if ($assignmentAttr) {
                    list($assignedToCondition, $params) = $this->getAssignedToCondition(false, $tableAlias, null, $paramsNamespace);
                    $ret[] = array(
                        'condition' => $assignedToCondition,
                        'operator' => 'OR',
                        'params' => $params
                    );
                }
                // Visible to user groups:
                $groupRegex = self::getGroupIdRegex();
                if ($assignmentAttr && !empty($groupRegex)) {
                    $ret[] = array(
                        'condition' => "(" . $prefix . "$assignmentAttr REGEXP BINARY 
                            :" . $paramsNamespace . "visibilityGroupIdRegex)",
                        'operator' => 'OR',
                        'params' => array(
                            ':' . $paramsNamespace . 'visibilityGroupIdRegex' => $groupRegex
                        )
                    );
                }
                if ($assignmentAttr && $visibilityAttr) {
                    $ret[] = array(
                        'condition' =>
                        "NOT (" . $prefix . "$visibilityAttr=" . self::VISIBILITY_PRIVATE . " AND "
                        . $prefix . "$assignmentAttr='Anyone')",
                        'operator' => 'AND',
                        'params' => array());
                }
                break;
            case self::QUERY_NONE:  // can't view anything
            default:
                $ret[] = array('condition' => 'FALSE', 'operator' => 'AND', 'params' => array());
        }
        return $ret;
    }

    /**
     * Checks assignment list, including membership to groups in assignment list
     *
     * @param string $username The username of the user for which to check assignment
     * @param bool $excludeAnyone If true, isAssignedTo will not return true if
     *  the record is assigned to anyone or no one.
     * @return bool true of action is assigned to specified user, false otherwise
     */
    public function isAssignedTo($username, $excludeAnyone = false) {
        if (isset($this->_isAssignedTo[$username][$excludeAnyone]))
            return $this->_isAssignedTo[$username][$excludeAnyone];
        if (!$this->assignmentAttr) // No way to determine assignment
            return true;

        // User model corresponding to the specified username
        $user = $username === Yii::app()->getSuName() ? 
            Yii::app()->getSuModel() : 
            User::model()->findByAttributes(array('username' => $username));

        $isAssignedTo = false;
        $assignees = explode(', ', $this->owner->getAttribute($this->assignmentAttr));
        $groupIds = array_filter($assignees, 'ctype_digit');
        $usernames = array_diff($assignees, $groupIds);

        // Check for individual assignment (or "anyone" if applicable):
        foreach ($usernames as $assignee) {
            if ($assignee === 'Anyone' || (sizeof($assignees) === 1 && $assignee === '')) {
                if (!$excludeAnyone) {
                    $isAssignedTo = true;
                    break;
                } else {
                    continue;
                }
            } else if ($assignee === $username) {
                $isAssignedTo = true;
                break;
            }
        }

        // Check for group assignment:
        if (!$isAssignedTo && !empty($groupIds) && $user instanceof User) {
            $userGroupsAssigned = array_intersect($groupIds, Groups::getUserGroups($user->id));
            if (!empty($userGroupsAssigned)) {
                $isAssignedTo = true;
            }
        }
        $this->_isAssignedTo[$username][$excludeAnyone] = $isAssignedTo;
        return $isAssignedTo;
    }

    public function getHiddenCondition ($tableAlias = 't') {
        $assignmentAttr = $this->getAssignmentAttr();
        $visibilityAttr = $this->getVisibilityAttr();
        if ($assignmentAttr && $visibilityAttr) {
            return "(NOT ($tableAlias.$assignmentAttr='Anyone' AND 
                $tableAlias.$visibilityAttr = ".self::VISIBILITY_PRIVATE."))";
        } else {
            return 'TRUE';
        }
    }

    private function isHidden () {
        $assignmentAttr = $this->getAssignmentAttr();
        $visibilityAttr = $this->getVisibilityAttr();
        if ($assignmentAttr && $visibilityAttr) {
            if ($this->owner->$assignmentAttr === 'Anyone' && 
                $this->owner->$visibilityAttr == self::VISIBILITY_PRIVATE) {

                return true;
            }
        }
        return false;
    }

    /**
     * Uses the visibility attribute and the assignment of the model to determine
     * if a given named user has permission to view it.
     * 
     * @param User $user The user for which visibility is to be checked
     * @return type
     */
    public function isVisibleTo($user) {
        if ($user) {
            $username = $user->username;
            $uid = $user->id;
        } else {
            $username = 'Guest';
            $uid = null;
        }
        if (!isset($this->_isVisibleTo[$username])) {
            $accessLevel = $this->getAccessLevel($uid);

            $hasViewPermission = false;

            if (!$this->isHidden ()) {
                switch ($accessLevel) {
                    case self::QUERY_ALL:
                        $hasViewPermission = true;
                        break;
                    case self::QUERY_PUBLIC:
                        if ($this->owner->getAttribute($this->visibilityAttr) ==
                            self::VISIBILITY_PUBLIC) {

                            $hasViewPermission = true;
                            break;
                        }
                        // Visible if marked with visibility "Users' Groups"
                        // and the current user has groups in common with
                        // assignees of the model:
                        if ($this->owner->getAttribute($this->visibilityAttr) == 
                                self::VISIBILITY_GROUPS && 
                            (bool) $this->assignmentAttr && 
                            (bool) ($groupmatesRegex = self::getGroupmatesRegex()) && 
                            preg_match(
                                '/' . $groupmatesRegex . '/', 
                                $this->owner->getAttribute($this->assignmentAttr))) {

                            $hasViewPermission = true;
                            break;
                        }
                    case self::QUERY_SELF:
                        // Visible if assigned to current user
                        if ($this->isAssignedTo($username, true)) {
                            $hasViewPermission = true;
                            break;
                        }
                    case self::QUERY_NONE:
                        break;
                }
            }
            $this->_isVisibleTo[$username] = $hasViewPermission;
        }
        return $this->_isVisibleTo[$username];
    }

    /**
     * Returns SQL condition which can be used to determine if an action is assigned to the
     *  current user.
     * @param bool $includeAnyone If true, SQL condition will evaluate to true for actions assigned
     *  to anyone or no one.
     * @return array array (<SQL condition string>, <array of parameters>)
     */
    public function getAssignedToCondition(
        $includeAnyone = true, $alias = null, $username = null,
        $paramsNamespace = 'X2PermissionsBehavior') {

        $username = $username === null ? Yii::app()->getSuName() : $username;
        $prefix = empty($alias) ? '' : "$alias.";
        $groupIdsRegex = self::getGroupIdRegex($username);
        $condition = "(" . ($includeAnyone ?
                        ($prefix . $this->assignmentAttr . "='Anyone' OR assignedTo='' OR ") : '') .
                $prefix . $this->assignmentAttr .
                " REGEXP BINARY :" . $paramsNamespace . "userNameRegex";
        $params = array(
            ':' . $paramsNamespace . 'userNameRegex' => self::getUserNameRegex($username),
        );
        if ($groupIdsRegex !== '') {
            $condition .= " OR $prefix" . $this->assignmentAttr .
                    " REGEXP BINARY :" . $paramsNamespace . "groupIdsRegex";
            $params[':' . $paramsNamespace . 'groupIdsRegex'] = $groupIdsRegex;
        }
        $condition .= ')';
        return array($condition, $params);
    }

    /**
     * Generates a display-friendly list of assignees
     * 
     * @param mixed $value If specified, use as the assignment instead of the
     *  current model's assignment field.
     */
    public function getAssigneeNames($value = false) {
        $assignment = !$value ? $this->owner->getAttribute($this->getAssignmentAttr()) : $value;
        $assignees = !is_array($assignment) ? explode(', ', $assignment) : $assignment;

        $groupIds = array_filter($assignees, 'ctype_digit');
        $userNames = array_diff($assignees, $groupIds);
        $userNameParam = AuxLib::bindArray($userNames);
        $userFullNames = !empty($userNames) ? array_map(function($u) {
                    return Formatter::fullName($u['firstName'], $u['lastName']);
                }, Yii::app()->db->createCommand()->select('firstName,lastName')
                                ->from(User::model()->tableName())
                                ->where('username IN ' . AuxLib::arrToStrList(
                                                array_keys($userNameParam)), $userNameParam)
                                ->queryAll()) : array();
        $groupIdParam = AuxLib::bindArray($groupIds);
        $groupNames = !empty($groupIds) ? Yii::app()->db->createCommand()
                        ->select('name')->from(Groups::model()->tableName())
                        ->where('id IN ' . AuxLib::arrToStrList(array_keys($groupIdParam)), $groupIdParam)
                        ->queryColumn() : array();
        return array_merge($userFullNames, $groupNames);
    }

    /**
     * Determines all users to whom a record is assigned.
     * 
     * @param bool $getUsernamesFromGroups If true, usernames of all users in groups whose ids
     *  are in the assignedTo string will also be returned
     * @return array assignees of this action
     */
    public function getAssignees($getUsernamesFromGroups = false) {
        $assignment = $this->owner->getAttribute($this->getAssignmentAttr());
        $assignees = !is_array($assignment) ? explode(', ', $assignment) : $assignment;

        $assigneesNames = array();

        if ($getUsernamesFromGroups) {
            // Obtain usernames from the groups assignment table
            $groupIds = array_filter($assignees, 'ctype_digit');
            if (!empty($groupIds)) {

                $groupIdParam = AuxLib::bindArray($groupIds);
                $groupUsers = Yii::app()->db->createCommand()
                        ->select('username')
                        ->from('x2_group_to_user')
                        ->where('groupId IN ' .
                                AuxLib::arrToStrList(array_keys($groupIdParam)), $groupIdParam)
                        ->queryColumn();
                foreach ($groupUsers as $username)
                    $assigneesNames[] = $username;
            }
        }
        foreach ($assignees as $assignee) {
            if ($assignee === 'Anyone') {
                continue;
            } else if (!ctype_digit($assignee)) {
                // Not a group ID but a username
                if (CActiveRecord::model('Profile')->exists('username=:u', array(
                            ':u' => $assignee))) {
                    $assigneesNames[] = $assignee;
                }
            }
        }

        return array_unique($assigneesNames);
    }

    /**
     * Returns regex for performing SQL assignedTo field comparisons.
     * @return string This can be inserted (with parameter binding) into SQL queries to
     *  determine if an action is assigned to a given group.
     */
    public static function getGroupIdRegex($username = null) {
        if ($username !== null) {
            $user = User::model()->findByAttributes(array('username' => $username));
            if (!$user)
                throw new CException('invalid username: ' . $username);
            $userId = $user->id;
        } else {
            $userId = Yii::app()->getSuId();
        }
        $groupIds = Groups::getUserGroups($userId);
        $groupIdRegex = '';
        $i = 0;
        foreach ($groupIds as $id) {
            if ($i++ > 0)
                $groupIdRegex .= '|';
            $groupIdRegex .= '((^|, )' . $id . '($|,))';
        }
        return $groupIdRegex;
    }

    /**
     * Regular expression for matching against a list of users
     *
     * @param array $userNames
     */
    public static function getUsernameListRegex($usernames) {
        return '(^|, )(' . implode('|', $usernames) . ')($|, )';
    }

    public static function getGroupmatesRegex() {
        $groupmates = Groups::getGroupmates(Yii::app()->getSuId());
        return empty($groupmates) ? null : self::getUsernameListRegex($groupmates);
    }

}

?>
