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
 * Base class for behaviors respecting the establishment of access permissions
 *
 * @property boolean|string $assignmentAttr The attribute to use for assignment
 *  and ownership. False signifies that it's to be treated as if owned by the
 *  system/no one in particular.
 * @property boolean|string $visibilityAttr The attribute to use for visibility
 *  settings. False signifies that visibility should be ignored.
 * @package application.components.permissions
 */
abstract class ModelPermissionsBehavior extends CActiveRecordBehavior {

    /**
     * Returns a CDbCriteria containing record-level access conditions.
     * @return CDbCriteria
     */
    abstract function getAccessCriteria();

    /**
     * Returns a number from 0 to 3 representing the current user's access level using the Yii auth manager
     * Assumes authItem naming scheme like "ContactsViewPrivate", etc.
     * This method probably ought to overridden, as there is no reliable way to determine the module a model "belongs" to.
     * @return integer The access level. 0=no access, 1=own records, 2=public records, 3=full access
     */
    abstract function getAccessLevel($uid=null);

    /**
     * Generates SQL condition to filter out records the user doesn't have
     *  permission to see.
     * This method is used by the 'accessControl' filter.
     * @param integer $accessLevel The user's access level. 0=no access, 1=own
     *  records, 2=public records, 3=full access
     * @return String The SQL conditions
     */
    abstract function getAccessConditions($accessLevel);

    /**
     * 
     */
    abstract function getAssignmentAttr();

    /**
     * 
     */
    abstract function getVisibilityAttr();
    
    /**
     * 
     */
    public static function getVisibilityOptions(){
        return array();
    }
    
    /*
     * Returns regex for performing SQL assignedTo field comparisons.
     * @return string This can be inserted (with parameter binding) into SQL queries to
     *  determine if an action is assigned to a given user.
     */
    public static function getUserNameRegex ($username=null) {
        return '(^|, )'.($username===null?Yii::app()->getSuName():$username).'($|, )';
    }
}

?>
