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






Yii::import('application.modules.actions.models.*');

/**
 * Model class for recording time spent on records, i.e. contacts, opportunities, etc.
 *
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ActionTimer extends CActiveRecord {

    public static function model($class = __CLASS__) {
        return parent::model($class);
    }

    public function tableName(){
        return 'x2_action_timers';
    }

    public function rules() {
        return array(
            array('userId,associationId,associationType,type','safe')
        );
    }

    /**
     * Computes the time spent on an action vis a vis its timer records
     * @param type $actionId
     * @return type
     */
    public static function actionTimeSpent($actionId){
        return Yii::app()->db->createCommand()
            ->select('SUM(`endtime`-`timestamp`)')
            ->from(self::model()->tableName())
            ->where('`actionId`=:actionId')
            ->queryScalar(array(':actionId' => $actionId));
    }

    /**
     * Performs a sum over timer records
     * @param type $id
     * @param type $associationType
     * @return type
     */
    public static function getTimeSpent($id = null,$associationType = null,$userId = null) {
        $timeSpent = 0;
        $attributes = array(
            'associationId' => $id,
            'userId' => $userId === null ? Yii::app()->getSuId() : $userId
        );
        if($associationType !== null)
            $attributes['associationType'] = $associationType;
        $cases = self::model()
            ->findAllByAttributes($attributes, 'endtime IS NOT NULL AND actionId IS NULL');
        foreach($cases as $case)
            $timeSpent += $case->endtime - $case->timestamp;
        return $timeSpent;
    }
    
    public static function humanReadableTimeSpent($id = null) {
        $duration = "0s";
        $seconds = self::getTimeSpent($id);
        if ($seconds != 0) {
            $intervals = array(
                'd' => 24 * 60 * 60,
                'h' => 60 * 60,
                'm' => 60,
                's' => 1
                );
            $values = array();
            foreach($intervals as $unit=>$interval) {
                if($quotient = intval($seconds / $interval)) {
                    $readable = $quotient . $unit;
                    array_push($values, $readable);
                    $seconds -= $quotient * $interval;
                }
            }
            $duration = implode(' ', $values);
        }
        return $duration;
    }

    /**
     * Return an initialized active record model, matching any that exist.
     *
     * It is preferable to use this instead of constructing a timer object
     * manually, to avoid violating the unique constraint.
     *
     * @param bool $save Whether to save the new timer upon initialization.
     * @param array $attributes The initial identifying attributes for the timer.
     *   If a preexisting timer record matching them is found, it will be
     *   returned in place of a new model.
     */
    public static function setup($save=false,$attributes = array()) {
        
        if(!isset($attributes['userId']))
            $attributes['userId'] = Yii::app()->getSuId();
        if(!isset($attributes['associationId'])) {
            $attributes['associationId'] = null;
        }
        if(!isset($attributes['type'])) {
            $attributes['type'] = null;
        }
        $uniqueAttributes = array_intersect_key(
            $attributes, array_flip(array('userId','associationId')));

        $criteria = new CDbCriteria(array('condition' => 'endtime IS NULL OR endtime = ""'));
        $existing = self::model()->findByAttributes($uniqueAttributes, $criteria);

        if((bool) $existing) {
            return $existing;
        } else {
            $class = __CLASS__;
            $timer = new $class;
            $timer->attributes = $attributes;
            $timer->timestamp = time();
            if($save)
                $timer->save();
            return $timer;
        }
    }

    /**
     * Ends the "timer" and creates an action record based upon it.
     * 
     * @param array $actionAttr attributes of the action
     * @return Actions
     */
    public function stop() {
        $this->endtime = time();
        $this->update(array('endtime', 'type'));
    }
}

?>
