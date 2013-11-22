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

/**
 * CModelBehavior class for permissions lookups on classes.
 *
 * X2PermissionsBehavior is a CModelBehavior which allows consistent lookup of
 * access levels and whether or not a user is allowed to view or edit a record.
 *
 * @package X2CRM.components.permissions
 */
class X2PermissionsBehavior extends ModelPermissionsBehavior {

    /**
     * Returns a CDbCriteria containing record-level access conditions.
     * @return CDbCriteria
     */
    public function getAccessCriteria(){
        $criteria = new CDbCriteria;

        $accessLevel = $this->getAccessLevel();

        if($this->owner->hasAttribute('visibility')){
            $visFlag = true;
        }else{
            $visFlag = false;
        }
        $conditions=$this->getAccessConditions($accessLevel, $visFlag);
        foreach($conditions as $arr){
            $criteria->addCondition($arr['condition'],$arr['operator']);
        }

        return $criteria;
    }

    /**
     * Returns a number from 0 to 3 representing the current user's access level using the Yii auth manager
     * Assumes authItem naming scheme like "ContactsViewPrivate", etc.
     * This method probably ought to overridden, as there is no reliable way to determine the module a model "belongs" to.
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
     * Generates SQL condition to filter out records the user doesn't have permission to see.
     * This method is used by the 'accessControl' filter.
     * @param Integer $accessLevel The user's access level. 0=no access, 1=own records, 2=public records, 3=full access
     * @param Boolean $useVisibility Whether to consider the model's visibility setting
     * @param String $user The username to use in these checks (defaults to current user)
     * @return String The SQL conditions
     */
    public function getAccessConditions($accessLevel, $useVisibility = true, $user = null){
        if($user === null){
            if(Yii::app()->isInSession)
                $user = Yii::app()->user->getName();
            else
                $user = Yii::app()->getSuModel()->username;
        }

        if($accessLevel === 2 && $useVisibility === false) // level 2 access only works if we consider visibility,
            $accessLevel = 3;  // so upgrade to full access
        $ret=array();
        switch($accessLevel){
            case 3:  // user can view everything
                $ret[] = array('condition'=>'TRUE', 'operator'=>'AND');
                break;
            case 1:  // user can view records they (or one of their groups) own
                $ret[] = array('condition'=>'t.assignedTo="'.$user.'"', 'operator'=>'OR');
                $ret[] = array('condition'=>'t.assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE username="'.$user.'")', 'operator'=>'OR');
                break;
            case 2:  // user can view any public (shared) record
                $ret[] = array('condition'=>'t.visibility=1', 'operator'=>'OR');
                $ret[] = array('condition'=>'t.assignedTo="'.$user.'"', 'operator'=>'OR');
                $ret[] = array('condition'=>'t.assignedTo IN (SELECT groupId FROM x2_group_to_user WHERE username="'.$user.'")', 'operator'=>'OR');
                $ret[] = array('condition'=>'(t.visibility=2 AND t.assignedTo IN (SELECT DISTINCT b.username FROM x2_group_to_user a INNER JOIN x2_group_to_user b ON a.groupId=b.groupId WHERE a.username="'.$user.'"))', 'operator'=>'OR');
                break;
            default:
            case 0:  // can't view anything
                $ret[] = array('condition'=>'FALSE', 'operator'=>'AND');
        }
        return $ret;
    }

}

?>
