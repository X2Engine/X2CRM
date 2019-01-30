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
 * Behavior to provide requisite methods for checking for potential duplicate
 * records. Currently only implemented in Contacts and Accounts.
 */
class DuplicateBehavior extends CActiveRecordBehavior {

    // Set constants so that we can change these in the future without issue
    CONST DUPLICATE_FIELD = 'dupeCheck';
    CONST DUPLICATE_LIMIT = 5;

    /**
     * Returns whether or not any duplicate records exist in the database. 
     * 
     * Commonly used as a gate in an if statement for other duplicate 
     * checking functionality.
     * @return boolean 
     */
    public function checkForDuplicates() {
        if ($this->owner->{DuplicateBehavior::DUPLICATE_FIELD} == 0) {
            $criteria = $this->getDuplicateCheckCriteria();
            return $this->owner->count($criteria) > 0;
        }
        return false;
    }

    /**
     * Return a list of potential duplicate records.
     * 
     * Capts at 5 records unless a special parameter is provided so as to prevent
     * possible server crashes from attempting to render large numbers of records.
     * @param boolean $getAll Whether to return all records or just 5
     * @return CActiveDataProvider
     */
    public function getDuplicates($getAll = false, $strict = false) {
        $criteria = $this->getDuplicateCheckCriteria(false, 't', $strict);
        if ($getAll && !empty($criteria->limit)) {
            $criteria = $this->getDuplicateCheckCriteria(true, 't', $strict);
        }
        if (!$getAll) {
            $criteria->limit = DuplicateBehavior::DUPLICATE_LIMIT;
        }
        return $this->owner->findAll($criteria);
    }

    /**
     * Returns the total number of duplicates found (unrestricted by the limit on
     * getDuplicates)
     * @return int
     */
    public function countDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria();
        return $this->owner->count($criteria);
    }

    /**
     * Mark a record as a duplicate.
     * 
     * Set all relevant fields to the proper values for marking a record as duplicate.
     * A duplicate record is private and assigned to 'Anyone', and if there
     * are options for "doNotCall" and "doNotEmail" they need to be turned on.
     * Alternatively, the "delete" string can be passed to delete the record instead
     * of hiding it. This functionality exists in case some future code requires
     * more things to be done on deleting duplicates.
     * @param string $action
     */
    public function markAsDuplicate($action = 'hide') {
        if ($action === 'hide') {
            if ($this->owner->hasAttribute('visibility')) {
                $this->owner->visibility = 0;
            }
            if ($this->owner->hasAttribute('assignedTo')) {
                $this->owner->assignedTo = 'Anyone';
            }
            if ($this->owner->hasAttribute('doNotCall')) {
                $this->owner->doNotCall = 1;
            }
            if ($this->owner->hasAttribute('doNotEmail')) {
                $this->owner->doNotEmail = 1;
            }
            $this->owner->{DuplicateBehavior::DUPLICATE_FIELD} = 1;
            $this->owner->save();
        } elseif ($action === 'delete') {
            $this->owner->delete();
        }
    }

    /**
     * Reset dupeCheck field if duplicate defining fields are changed.
     * 
     * Records have a concept of "duplicate-defining fields" which are the fields
     * that are checked when searching for duplicates (name, email, etc.). If one
     * of those fields is changed in an update, the dupeCheck parameter needs to
     * be reset and the record needs to be checked for possible duplicates again.
     * @param CEvent $event
     */
    public function afterSave($event) {
        if (!$this->owner->getIsNewRecord()) {
            $dupeFields = $this->owner->duplicateFields();
            $oldAttributes = $this->owner->getOldAttributes();
            foreach ($dupeFields as $field) {
                if (array_key_exists($field, $oldAttributes) &&
                        $oldAttributes[$field] !== $this->owner->$field) {
                    $this->resetDuplicateField();
                    break;
                }
            }
        }
    }

    /**
     * Update the dupeCheck field to reflect that a record has been checked.
     * 
     * Set the value in the current record and use updateByPk so that no validation
     * or behaviors from afterSave are called.
     */
    public function duplicateChecked() {
        if ($this->owner->{DuplicateBehavior::DUPLICATE_FIELD} == 0) {
            $this->owner->{DuplicateBehavior::DUPLICATE_FIELD} = 1;
            $this->owner->updateByPk($this->owner->id, array(DuplicateBehavior::DUPLICATE_FIELD => 1));
        }
    }

    /**
     * Reset the dupeCheck field to its unchecked state.
     */
    public function resetDuplicateField() {
        $this->owner->{DuplicateBehavior::DUPLICATE_FIELD} = 0;
        $this->owner->updateByPk($this->owner->id, array(DuplicateBehavior::DUPLICATE_FIELD => 0));
    }

    /**
     * Hide all potential duplicate records.
     * 
     * This is equivalent to a mass version of "markAsDuplicate" but it affects
     * records other than the currenly loaded one.
     */
    public function hideDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria(false, null);
        $attributes = array(
            DuplicateBehavior::DUPLICATE_FIELD => 1,
        );
        if ($this->owner->hasAttribute('visibility')) {
            $attributes['visibility'] = 0;
        }
        if ($this->owner->hasAttribute('assignedTo')) {
            $attributes['assignedTo'] = 'Anyone';
        }
        if ($this->owner->hasAttribute('doNotCall')) {
            $attributes['doNotCall'] = 1;
        }
        if ($this->owner->hasAttribute('doNotEmail')) {
            $attributes['doNotEmail'] = 1;
        }
        $this->owner->updateAll($attributes, $criteria);
    }

    /**
     * Delete all potential duplicate records.
     */
    public function deleteDuplicates() {
        $criteria = $this->getDuplicateCheckCriteria(false, null);
        $this->owner->deleteAll($criteria);
    }
    
    /**
     * Private helper function to get the duplicate criteria.
     * 
     * Caches criteria for later use.
     * @param boolean $refresh Force refresh of cached criteria
     * @return CDbCriteria
     */
    private $_duplicateCheckCriteria = array ();
    private function getDuplicateCheckCriteria($refresh = false, $alias='t', $strict = false) {
        if (!$refresh && isset($this->_duplicateCheckCriteria[$alias])) {
            return $this->_duplicateCheckCriteria[$alias];
        }
        $dupeFields = $this->owner->duplicateFields();
        $criteria = new CDbCriteria();
        $criteria->order = 'createDate ASC';
        foreach ($dupeFields as $fieldName) {
            if (!empty($this->owner->$fieldName)) {
                $criteria->compare($fieldName, $this->owner->$fieldName, false, $strict?"AND":"OR");
            }
        }
        if (empty($criteria->condition)) {
            $criteria->condition = "FALSE";
        } else {
            $criteria->compare('id', "<>" . $this->owner->id, false, "AND");
            if ($this->owner->asa('permissions')) {
                $criteria->mergeWith($this->owner->getAccessCriteria($alias));
            }
        }
        $this->_duplicateCheckCriteria[$alias] = $criteria;
        return $this->_duplicateCheckCriteria[$alias];
    }

}
