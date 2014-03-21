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
 * CModelBehavior class for permissions lookups on classes.
 *
 * X2PermissionsBehavior is a CModelBehavior which allows consistent lookup of
 * access levels and whether or not a user is allowed to view or edit a record.
 *
 * @package application.components.permissions
 */
class X2PermissionsBehavior extends ModelPermissionsBehavior {

    /**
     * Returns a CDbCriteria containing record-level access conditions.
     * @return CDbCriteria
     */
    public function getAccessCriteria(){
        $criteria = new CDbCriteria;

        $accessLevel = $this->getAccessLevel();

        // Determine visibility attribute:
        $visAttr = false;
        if($this->owner->hasAttribute('visibility')){
            $visAttr = 'visibility';
        }else if($this->owner instanceof X2Model){
            // Check for the existence of a visibility-type field
            $fields = $this->owner->getFields();
            foreach($fields as $field){
                // Use the first visibility type field in the model (any
                // additional fields would be superfluous)
                if($field->type == 'visibility'){
                    $visAttr = $field->fieldName;
                    break;
                }
            }
        }

        // Determine assignment attribute:
        $assignAttr = false;
        if($this->owner->hasAttribute('assignedTo')) {
            $assignAttr = 'assignedTo';
        } elseif ($this->owner->hasAttribute('createdBy')) {
            $assignAttr = 'createdBy';
        } elseif ($this->owner instanceof X2Model) {
            $fields = $this->owner->getFields();
            foreach($fields as $field) {
                // Use the first assignment field available:
                if($field->type == 'assignment') {
                    $assignAttr = $field->fieldName;
                    break;
                }
            }
        }

        $conditions=$this->getAccessConditions($accessLevel, $visAttr,null,$assignAttr);
        foreach($conditions as $arr){
            $criteria->addCondition($arr['condition'],$arr['operator']);
        }

        return $criteria;
    }

    /**
     * Returns a number from 0 to 3 representing the current user's access level using the Yii 
     * auth manager.
     * Assumes authItem naming scheme like "ContactsViewPrivate", etc.
     * This method probably ought to overridden, as there is no reliable way to determine the 
     * module a model "belongs" to.
     * @return integer The access level. 0=no access, 1=own records, 2=public records, 3=full access
     */
    public function getAccessLevel(){
        $module = ucfirst($this->owner->module);

        if(Yii::app()->isInSession){ // Web request
            $uid = Yii::app()->user->id;
        }else{ // User session not available; doing an operation through API or console
            $uid = Yii::app()->getSuID();
        }
        $accessLevel = 0;
        if(Yii::app()->authManager->checkAccess($module.'Admin', $uid)){
            if($accessLevel < 3)
                $accessLevel = 3;
        }elseif(Yii::app()->authManager->checkAccess($module.'ReadOnlyAccess', $uid)){
            if($accessLevel < 2)
                $accessLevel = 2;
        }elseif(Yii::app()->authManager->checkAccess($module.'PrivateReadOnlyAccess', $uid)){
            if($accessLevel < 1)
                $accessLevel = 1;
        }
        $roles = X2Model::model('RoleToUser')->findAllByAttributes(array('userId' => $uid));
        foreach($roles as $role){
            if(Yii::app()->authManager->checkAccess($module.'Admin', $role->roleId)){
                if($accessLevel < 3)
                    $accessLevel = 3;
            }elseif(Yii::app()->authManager->checkAccess($module.'ReadOnlyAccess', $role->roleId)){
                if($accessLevel < 2)
                    $accessLevel = 2;
            }elseif(Yii::app()->authManager->checkAccess($module.'PrivateReadOnlyAccess', $role->roleId)){
                if($accessLevel < 1)
                    $accessLevel = 1;
            }
        }
        /* temp */
        // Remove this code after Custom Module refactor project.
//        $item = Yii::app()->authManager->getAuthItem($module.'ReadOnlyAccess');
//        if(is_null($item)){
//            if($accessLevel < 2){
//                $accessLevel = 2;
//            }
//        }
        /* end temp */
        return $accessLevel;
    }

    /**
     * Generates SQL condition to filter out records the user doesn't have
     * permission to see.
     *
     * This method is used by the 'accessControl' filter.
     *
     * @param Integer $accessLevel The user's access level. 0=no access, 1=own
     *  records, 2=public records, 3=full access
     * @param boolean|string $visibilityAttr The attribute to use for visibility
     *  settings. False signifies that visibility should be ignored.
     * @param String $user The username to use in these checks (defaults to the
     *  current user)
     * @param boolean|string $assignmentAttr The attribute to use for assignment
     *  and ownership. False signifies that it's to be treated as if owned by
     *  the system/no one in particular.
     * @return String The SQL conditions
     */
    public function getAccessConditions($accessLevel, $visibilityAttr = 'visibility', $user = null, $assignmentAttr='assignedTo'){
        if($user === null){
            if(Yii::app()->isInSession)
                $user = Yii::app()->user->getName();
            else
                $user = Yii::app()->getSuModel()->username;
        }

        if($accessLevel === 2 && $visibilityAttr === false) // level 2 access only works if we consider visibility,
            $accessLevel = 3;  // so upgrade to full access
        $ret = array();
        switch($accessLevel){
            case 3:  // user can view everything
                $ret[] = array('condition'=>'TRUE', 'operator'=>'AND');
                break;
            case 1:  // user can view records they (or one of their groups) own
                if($assignmentAttr != false){
                    $ret[] = array('condition' => 't.'.$assignmentAttr.'="'.$user.'"', 'operator' => 'OR');
                    $ret[] = array('condition' => 't.'.$assignmentAttr.' IN (SELECT groupId FROM x2_group_to_user WHERE username="'.$user.'")', 'operator' => 'OR');
                }
                break;
            case 2:  // user can view any public (shared) record
                if($visibilityAttr != false){
                    $ret[] = array('condition' => "t.$visibilityAttr=1", 'operator' => 'OR');
                }
                if($assignmentAttr != false){
                    $ret[] = array('condition' => 't.'.$assignmentAttr.'="'.$user.'"', 'operator' => 'OR');
                    $ret[] = array('condition' => 't.'.$assignmentAttr.' IN (SELECT groupId FROM x2_group_to_user WHERE username="'.$user.'")', 'operator' => 'OR');
                    $ret[] = array(
                        'condition' => "(t.$visibilityAttr=2 AND t.$assignmentAttr IN (".
                        'SELECT DISTINCT b.username '.
                        'FROM x2_group_to_user a INNER JOIN x2_group_to_user b '.
                        'ON a.groupId=b.groupId '.
                        'WHERE a.username="'.$user.'"))', 'operator' => 'OR');
                }
                break;
            default:
            case 0:  // can't view anything
                $ret[] = array('condition'=>'FALSE', 'operator'=>'AND');
        }
        return $ret;
    }

}

?>
