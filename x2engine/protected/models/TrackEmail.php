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
 * This is the model class for table "x2_track_emails". Inline Email Form 
 * uses this model to add a unique id to the action that was generated when
 * the email was sent. This uniqueId is used in /actions/emailOpened($uid)
 * to keep track of which emails have been opened by the Contact. 
 *
 * @package application.models
 * @property integer $id
 * @property integer $actionId
 * @property string $uniqueId
 */
class TrackEmail extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Tags the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_track_emails';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'action' => array(self::BELONGS_TO, 'Actions', 'actionId'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        return new CActiveDataProvider(get_class($this),
                array(
            'criteria' => $criteria,
        ));
    }

    public function recordEmailOpen() {
        if ($this->opened === null) {
            $openedAction = $this->createEmailOpenedAction();
            $event = $this->createEmailOpenedEvent();
            if ($openedAction->save() && $event->save()) {
                $this->opened = time();
                $this->save();
                $model = X2Model::getAssociationModel($openedAction->associationType,
                                $openedAction->associationId);
                X2Flow::trigger('EmailOpenTrigger',
                        array(
                    'model' => $model,
                ));
            }
        }
    }

    private function createEmailOpenedAction() {
        $now = time();
        $action = new Actions;
        $action->type = 'emailOpened';
        $action->associationType = $this->action->associationType;
        $action->associationId = $this->action->associationId;
        $action->createDate = $now;
        $action->lastUpdated = $now;
        $action->completeDate = $now;
        $action->complete = 'Yes';
        $action->updatedBy = 'admin';
        $action->associationName = $this->action->associationName;
        $action->visibility = $this->action->visibility;
        $action->assignedTo = $this->action->assignedTo;
        $action->actionDescription = Yii::t('marketing',
                        '{recordType} has opened the email sent on ', array(
                        '{recordType}' => Modules::displayName(false,
                            $this->action->associationType)
        ));
        $action->actionDescription .= Formatter::formatLongDateTime($this->action->createDate) . "<br>";
        $action->actionDescription .= $this->action->actionDescription;
        
        return $action;
    }
    
    private function createEmailOpenedEvent() {
        $event = new Events;
        $event->type = 'email_opened';
        $event->subtype = 'email';
        $event->user = $this->action->assignedTo;
        $event->associationType = $this->action->associationType;
        $event->associationId = $this->action->associationId;

        return $event;
    }

}
