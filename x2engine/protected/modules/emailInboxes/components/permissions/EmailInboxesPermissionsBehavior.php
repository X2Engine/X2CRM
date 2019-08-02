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




class EmailInboxesPermissionsBehavior extends X2PermissionsBehavior {

    public function isVisibleTo ($user) {
        if (Yii::app()->params->isAdmin) return true;
        $accessLevel = $this->getAccessLevel($user->id);
        if ($accessLevel === self::QUERY_NONE) return false;

        $moduleAdmin = Yii::app()->user->checkAccess('EmailInboxesAdmin');
        return $this->owner->shared && ($moduleAdmin || $this->isAssignedTo ($user->username)) ||
            !$this->owner->shared && $this->owner->assignedTo === $user->username;
    }

    public function getAccessConditions (
        $accessLevel, $tableAlias='t', $paramNamespace='X2PermissionsBehavior',
        $showHidden=false) {

        $prefix = empty($tableAlias) ? '' : "$tableAlias.";
        $username = Yii::app()->getSuModel()->username;
        $moduleAdmin = Yii::app()->user->checkAccess('EmailInboxesAdmin');

        //if (Yii::app()->params->isAdmin) {
            //$ret[] = array('condition' => 'TRUE', 'operator' => 'AND', 'params' => array());
        //} elseif ($accessLevel === self::QUERY_NONE) {
        if ($accessLevel === self::QUERY_NONE) {
            $ret[] = array(
                'condition' => 'FALSE', 'operator' => 'AND', 'params' => array());
        } else {
            list($assignedToCondition, $params) = $this->getAssignedToCondition(
                true, $tableAlias, null, $paramNamespace);
            $ret[] = array(
                'condition' => 
                    $prefix."shared AND (".
                        ($moduleAdmin ? 'TRUE OR ' : '').$assignedToCondition.") OR 
                    NOT shared AND ".$prefix."assignedTo=:".$paramNamespace.'username',
                'operator' => 'OR',
                'params' => array_merge ($params, array (
                    ':'.$paramNamespace.'username' => $username   
                ))
            );
        }
        return $ret;
    }
}

?>
