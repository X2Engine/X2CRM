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




abstract class ActionFormModelBase extends X2FormModel {
    public $associationType;
    public $associationId;
    public $associationName;
    public $assignedTo;
    public $dueDate;
    public $completeDate;
    public $timerIds;
    public $skipActionTimers = true;

    public function rules () {
        return array (
            array (
                'associationId', 'validateAssociationId',
            ),
            array (
                'associationName', 'validateAssociationName',
            ),
            array (
                'assignedTo', 'validateAssignedTo',
            ),
            array (
                'associationType, assignedTo', 'required',
            ),
            array (
                'timerIds', 'safe',
            ),
        );
    }

    /**
     * Set dynamic default date values 
     */
    public function validateAssignedTo ($attribute) {
        $value = $this->$attribute;
        if (empty ($value)) $this->$attribute = Yii::app()->user->getName ();
    }

    /**
     * Set dynamic default date values 
     */
    public function validateDate ($attribute) {
        $value = $this->$attribute;
        if (empty ($value)) $this->$attribute = time ();
    }

    /**
     * Check for negative time ranges 
     */
    public function validateCompleteDate ($attribute) {
        $value = $this->$attribute;
        if (strtotime($this->dueDate) > strtotime($this->completeDate))
            // User specified a negative time range! Let's say that the
            // starting time is equal to when it ended (which is earlier)
            $this->dueDate = $this->completeDate;
    }

    /**
     * if association name is sent without id, try to lookup the record by name and type
     */
    public function validateAssociationId ($attribute) {
        $value = $this->$attribute;
        if (is_string ($this->associationName) && $this->associationName !== '') {
            $associatedModel = X2Model::getModelOfTypeWithName (
                $this->associationType, $this->associationName);
            if ($associatedModel) {
                $this->associationId = $associatedModel->id;
            } else {
                $this->addError ('associationName', Yii::t('actions', 'Invalid association name'));
            }
        }
        if (!isset ($this->associationId)) return false;
    }

    /**
     * Synchronize association name with association type and id 
     */
    public function validateAssociationName ($attribute) {
        $association = X2Model::getAssociationModel($this->associationType, $this->associationId);
        if ($association) {
            
            if (Yii::app()->contEd('pla') && $this->associationType === 'anoncontact')
                $this->associationName = "Anonymous Contact #".$this->associationId;
            else 
            
                $this->associationName = $association->name;
        }
    }

    public function attributeLabels () {
        return array (
            'assignedTo' => Yii::t('actions', 'Assigned To'),
            'dueDate' => Yii::t('actions', 'Due Date'),
            'priority' => Yii::t('actions', 'Priority'),
            'visibility' => Yii::t('actions', 'Visibility'),
            'reminder' => Yii::t('actions', 'Reminder'),
        );
    }

    private $_action;
    public function getAction ($new=false) {
        if (!isset ($this->_action) || $new) {
            if(!Yii::app()->user->isGuest){
                $model = new Actions;
            }else{
                $model = new Actions('guestCreate');
            }
            if (!empty ($this->type)) $model->disableBehavior ('changelog');
            $this->_action = $model;
        }
        return $this->_action;
    }

    public function validate ($attributes=null, $clearErrors=true) {
        $valid = parent::validate ();
        $attributes = $this->getAttributes ();
        $this->action->setX2Fields ($attributes);
        $this->action->type = $this->type;
        $valid &= $this->action->validate ();
        // synchronize errors
        $this->addErrors ($this->action->getErrors ());
        $this->action->addErrors ($this->getErrors ());
        return $valid;
    }

    public function mergeErrors (Actions $model) {
        $model->addErrors ($this->getErrors ());
    }
}

?>
