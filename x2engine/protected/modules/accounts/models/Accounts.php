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




Yii::import('application.models.X2Model');

/**
 * This is the model class for table "x2_accounts".
 *
 * @package application.modules.accounts.models
 */
class Accounts extends X2Model {

    /**
     * Returns the static model of the specified AR class.
     * @return Accounts the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_accounts';
    }

    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'accounts',
                'icon' => 'accounts_icon.png',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'InlineEmailModelBehavior' => array(
                'class' => 'application.components.behaviors.InlineEmailModelBehavior',
            ),
            'AddressBehavior' => array(
                'class' => 'application.components.behaviors.AddressBehavior',
            ),
            'DuplicateBehavior' => array(
                'class' => 'application.components.behaviors.DuplicateBehavior',
            ),
            'MergeableBehavior' => array(
                'class' => 'MergeableBehavior',
                'restrictedFields' => array (
                    'id', 'nameId', 'dupeCheck', 
                    // legacy field
                    'associatedContacts'
                )
            ),
             'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
        ));
    }

    public function duplicateFields() {
        return array_merge(array(
            'tickerSymbol',
            'website',
        ), parent::duplicateFields());
    }

    /**
     * Responds to {@link CModel::onBeforeValidate} event.
     * Fixes the revenue field before validating.
     *
     * @return boolean whether validation should be executed. Defaults to true.
     *//*
      public function beforeValidate() {
      $this->annualRevenue = Formatter::parseCurrency($this->annualRevenue,false);
      return parent::beforeValidate();
      } */

    public static function parseContacts($arr) {
        $str = "";
        foreach ($arr as $contact) {
            $str.=$contact . " ";
        }
        return $str;
    }

    public static function parseContactsTwo($arr) {
        $str = "";
        foreach ($arr as $id => $contact) {
            $str.=$id . " ";
        }
        return $str;
    }

    public static function editContactArray($arr, $model) {

        $pieces = explode(" ", $model->associatedContacts);
        unset($arr[0]);

        foreach ($pieces as $contact) {
            if (array_key_exists($contact, $arr)) {
                unset($arr[$contact]);
            }
        }

        return $arr;
    }

    public static function editUserArray($arr, $model) {

        $pieces = explode(', ', $model->assignedTo);
        unset($arr['Anyone']);
        unset($arr['admin']);
        foreach ($pieces as $user) {
            if (array_key_exists($user, $arr)) {
                unset($arr[$user]);
            }
        }
        return $arr;
    }

    public static function editUsersInverse($arr) {

        $data = array();

        foreach ($arr as $username)
            $data[] = CActiveRecord::model('User')->findByAttributes(array('username' => $username));

        $temp = array();
        foreach ($data as $item) {
            if (isset($item))
                $temp[$item->username] = $item->firstName . ' ' . $item->lastName;
        }
        return $temp;
    }

    public static function editContactsInverse($arr) {
        $data = array();

        foreach ($arr as $id) {
            if ($id != '')
                $data[] = CActiveRecord::model('Contacts')->findByPk($id);
        }
        $temp = array();

        foreach ($data as $item) {
            $temp[$item->id] = $item->firstName . ' ' . $item->lastName;
        }
        return $temp;
    }

    public static function getAvailableContacts($accountId = 0) {

        $availableContacts = array();

        $criteria = new CDbCriteria;
        $criteria->addCondition("accountId='$accountId'");
        $criteria->addCondition(array("accountId=''"), 'OR');


        $contactRecords = CActiveRecord::model('Contacts')->findAll($criteria);
        foreach ($contactRecords as $record)
            $availableContacts[$record->id] = $record->name;

        return $availableContacts;
    }

    public static function getContacts($accountId) {
        $contacts = array();
        $contactRecords = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId' => $accountId));
        if (!isset($contactRecords))
            return array();

        foreach ($contactRecords as $record)
            $contacts[$record->id] = $record->name;

        return $contacts;
    }

    public static function setContacts($contactIds, $accountId) {

        $account = CActiveRecord::model('Accounts')->findByPk($accountId);

        if (!isset($account))
            return false;

        // get all contacts currently associated
        $oldContacts = CActiveRecord::model('Contacts')->findAllByAttributes(array('accountId' => $accountId));
        foreach ($oldContacts as $contact) {
            if (!in_array($contact->id, $contactIds)) {
                $contact->accountId = 0;
                $contact->company = '';  // dissociate if they are no longer in the list
                $contact->save();
            }
        }

        // now set association for all contacts in the list
        foreach ($contactIds as $id) {
            $contactRecord = CActiveRecord::model('Contacts')->findByPk($id);
            $contactRecord->accountId = $account->id;
            $contactRecord->company = $account->name;
            $contactRecord->save();
        }
        return true;
    }
    
    /**
     * Searches in current user's contacts
     * 
     * @return type
     */
    public function searchMyAccounts() {
        $criteria = new CDbCriteria;

        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        // $condition = 'assignedTo="'.Yii::app()->user->getName().'"';
        // $parameters=array('limit'=>ceil(Profile::getResultsPerPage()));
        // $parameters['condition']=$condition;
        // $criteria->scopes=array('findAll'=>array($parameters));

        return $this->searchBase($criteria);
    }

    /**
     * Searches newest contacts
     * 
     * @return type
     */
    public function searchNewAccounts() {
        $criteria = new CDbCriteria;
        $condition = 't.createDate > ' . mktime(0, 0, 0);
        $accessLevel = Yii::app()->user->checkAccess('ContactsView') ? 1 : 0;
        $conditions = $this->getAccessConditions($accessLevel);
        foreach ($conditions as $arr) {
            $criteria->addCondition($arr['condition'], $arr['operator']);
            $criteria->params = array_merge($criteria->params, $arr['params']);
        }

        $parameters = array('limit' => ceil(Profile::getResultsPerPage()));

        $parameters['condition'] = $condition;
        $criteria->scopes = array('findAll' => array($parameters));

        return $this->searchBase($criteria);
    }

    public function search($pageSize = null, $uniqueId = null) {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria, $pageSize);
    }

    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);
        
        if (isset($list)) {
            $search = $list->queryCriteria();
            
            $this->compareAttributes($search);
           
            return new SmartActiveDataProvider('Accounts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC' // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else { //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

}
