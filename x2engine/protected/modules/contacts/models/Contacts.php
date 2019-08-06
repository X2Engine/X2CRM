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
 * This is the model class for table "x2_contacts".
 *
 * @package application.modules.contacts.models
 */
class Contacts extends X2Model {

    public $name;
    public $verifyCode; // CAPTCHA for weblead form

    /**
     * Returns the static model of the specified AR class.
     * @return Contacts the static model class
     */

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge(parent::relations(), array(
            'fingerprint' => array(self::BELONGS_TO, 'Fingerprint', 'fingerprintId'),
        ));
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_contacts';
    }

    /**
     * Gets contact behaviors
     * 
     * @return type
     */
    public function behaviors() {
        return array_merge(parent::behaviors(), array(
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'contacts',
            ),
            'FingerprintBehavior' => array(
                'class' => 'FingerprintBehavior',
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'AddressBehavior' => array(
                'class' => 'application.components.behaviors.AddressBehavior',
            ),
            'DuplicateBehavior' => array(
                'class' => 'application.components.behaviors.DuplicateBehavior',
            ),
            'ContactsNameBehavior' => array(
                'class' => 'application.components.behaviors.ContactsNameBehavior',
            ),
            'MappableBehavior' => array(
                'class' => 'application.components.behaviors.MappableBehavior',
            ),
            'ModelConversionBehavior' => array(
                    'class' => 'application.components.behaviors.ModelConversionBehavior',
                    'deleteConvertedRecord' => false,)
        ));
    }

    /**
     * Defines contact rules
     * 
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = array_merge(parent::rules(), array(
            array(
                'verifyCode', 'captcha', 'allowEmpty' => !CCaptcha::checkRequirements(),
                'on' => 'webFormWithCaptcha', 'captchaAction' => 'site/webleadCaptcha')
        ));
        return $rules;
    }

    public function duplicateFields() {
        return array_merge(parent::duplicateFields(), array(
            'email',
        ));
    }

    public function afterDelete() {
        parent::afterDelete();
        // Remove associated X2ListItems
        Yii::app()->db->createCommand()
            ->delete('x2_list_items', 'contactId = :id', array(':id' => $this->id));
    }

    /**
     * Updates tracking key after find
     */
    public function afterFind() {
        parent::afterFind();
        if ($this->trackingKey === null && self::$autoPopulateFields) {
            $this->trackingKey = self::getNewTrackingKey();
            $this->update(array('trackingKey'));
        }
    }

    /**
     * Sets tracking key before save
     * 
     * @return boolean whether or not to save
     */
    public function beforeSave() {
        if ($this->trackingKey === null) {
            $this->trackingKey = self::getNewTrackingKey();
        }

        return parent::beforeSave();
    }

    /**
     * Responds when {@link X2Model::afterUpdate()} is called (record saved, but
     * not a new record). Sends a notification to anyone subscribed to this contact.
     *
     * Before executing this, the model must check whether the contact has the
     * "changelog" behavior. That is because the behavior is disabled
     * when checking for duplicates in {@link ContactsController}
     */
    public function afterUpdate() {
        if (!Yii::app()->params->noSession && $this->asa('changelog') &&
                $this->asa('changelog')->enabled) {//$this->scenario != 'noChangelog') {
            // send subscribe emails if anyone has subscribed to this contact
            $result = Yii::app()->db->createCommand()
                    ->select('user_id')
                    ->from('x2_subscribe_contacts')
                    ->where('contact_id=:id', array(':id' => $this->id))
                    ->queryColumn();

            $datetime = Formatter::formatLongDateTime(time());
            $modelLink = CHtml::link($this->name, Yii::app()->controller->createAbsoluteUrl('/contacts/' . $this->id));
            $subject = 'X2Engine: ' . $this->name . ' updated';
            $message = "Hello,<br>\n<br>\n";
            $message .= 'You are receiving this email because you are subscribed to changes made to the contact ' . $modelLink . ' in X2Engine. ';
            $message .= 'The following changes were made on ' . $datetime . ":<br>\n<br>\n";

            foreach ($this->getChanges() as $attribute => $change) {
                if ($attribute != 'lastActivity') {
                    $old = $change[0] == '' ? '-----' : $change[0];
                    $new = $change[1] == '' ? '-----' : $change[1];
                    $label = $this->getAttributeLabel($attribute);
                    $message .= "$label: $old => $new<br>\n";
                }
            }

            $message .= "<br>\nYou can unsubscribe to these messages by going to $modelLink and clicking Unsubscribe.<br>\n<br>\n";

            $adminProfile = Yii::app()->params->adminProfile;
            foreach ($result as $subscription) {
                $subscription = array();
                if (isset($subscription['user_id'])) {
                    $profile = X2Model::model('Profile')->findByPk($subscription['user_id']);
                    if ($profile && $profile->emailAddress && $adminProfile && $adminProfile->emailAddress) {
                        $to = array('to' => array(array($profile->fullName, $profile->emailAddress)));
                        Yii::app()->controller->sendUserEmail($to, $subject, $message, null, Credentials::$sysUseId['systemNotificationEmail']);
                    }
                }
            }
        }

        parent::afterUpdate();
    }
    
    public function findById($id) {
        return X2Model::model('Contacts')->findByPk($id);
    }

    /**
     * Gets an array of names for an assignment dropdown menu
     * 
     * @return type
     */
    public static function getNames() {
        $contactArray = X2Model::model('Contacts')->findAll();
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    /**
     * Gets all public contacts.
     * @return $names An array of strings containing the names of contacts.
     */
    public static function getAllNames() {
        $contactArray = X2Model::model('Contacts')->findAll($condition = 'visibility=1');
        $names = array(0 => 'None');
        foreach ($contactArray as $user) {
            $first = $user->firstName;
            $last = $user->lastName;
            $name = $first . ' ' . $last;
            $names[$user->id] = $name;
        }
        return $names;
    }

    /**
     * Gets contact links
     * 
     * @param type $contacts
     * @return type
     */
    public static function getContactLinks($contacts) {
        if (!is_array($contacts)) {
            $contacts = explode(' ', $contacts);
        }

        $links = array();
        foreach ($contacts as &$id) {
            if ($id != 0) {
                $model = X2Model::model('Contacts')->findByPk($id);
                if (isset($model)) {
                    $links[] = CHtml::link($model->name, array('/contacts/contacts/view', 'id' => $id));
                }
                //$links.=$link.', ';
            }
        }
        return implode(', ', $links);
    }

    /**
     * Gets contact mailing list
     * 
     * @param type $criteria
     * @return type
     */
    public static function getMailingList($criteria) {
        $mailingList = array();

        $arr = X2Model::model('Contacts')->findAll();
        foreach ($arr as $contact) {
            $i = preg_match("/$criteria/i", $contact->backgroundInfo);
            if ($i >= 1) {
                $mailingList[] = $contact->email;
            }
        }
        return $mailingList;
    }

    /**
     * An alias for search ()
     */
    public function searchAll($pageSize = null, CDbCriteria $criteria = null) {
        return $this->search($pageSize, $criteria);
    }

    /**
     * Searches in current user's contacts
     * 
     * @return type
     */
    public function searchMyContacts() {
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
    public function searchNewContacts() {
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

    /**
     * Adds tag filtering to search base 
     */
    public function search($pageSize = null, CDbCriteria $criteria = null) {
        if ($criteria === null) {
            $criteria = new CDbCriteria;
        }

        return $this->searchBase($criteria, $pageSize);
    }

    public function searchAdmin() {
        $criteria = new CDbCriteria;
        return $this->searchBase($criteria);
    }

    /**
     * Searches for an account given an id
     * 
     * @param type $id
     * @return type
     */
    public function searchAccount($id) {
        $criteria = new CDbCriteria;
        $criteria->compare('company', $id);

        return $this->searchBase($criteria);
    }

    /**
     * Gets a DataProvider for all the contacts in the specified list,
     * using this Contact model's attributes as a search filter
     */
    public function searchList($id, $pageSize = null) {
        $list = X2List::model()->findByPk($id);

        if (isset($list)) {
            $search = $list->queryCriteria();

            $this->compareAttributes($search);

            return new SmartActiveDataProvider('Contacts', array(
                'criteria' => $search,
                'sort' => array(
                    'defaultOrder' => 't.lastUpdated DESC'    // true = ASC
                ),
                'pagination' => array(
                    'pageSize' => isset($pageSize) ? $pageSize : Profile::getResultsPerPage(),
                ),
            ));
        } else {    //if list is not working, return all contacts
            return $this->searchBase();
        }
    }

    /**
     * Generates a random tracking key and guarantees uniqueness
     * @return String $key a unique random tracking key
     */
    public static function getNewTrackingKey() {

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        // try up to 100 times to guess a unique key
        for ($i = 0; $i < 100; $i++) {
            $key = '';
            for ($j = 0; $j < 32; $j++) {// generate a random 32 char alphanumeric string
                $key .= substr($chars, rand(0, strlen($chars) - 1), 1);
            }

            // check if this key is already used
            if (X2Model::model('Contacts')->exists('trackingKey="' . $key . '"')) {
                continue;
            } else {
                return $key;
            }
        }
        return null;
    }

    /**
     * Sets values of attributes with values of corresponding attributes in the anon contact record.
     * Also migrates over actions and notifications associated with the anon contact. Finally,
     * the anonymous contact is deleted.
     * 
     * @param AnonContact $anonContact The anonymous contact record whose attributes will be
     *  merged in with this contact
     */
    public function mergeWithAnonContact(AnonContact $anonContact) {
        $fingerprintRecord = $anonContact->fingerprint;

        // Migrate over existing AnonContact data
        if (!isset($this->leadscore)) {
            $this->leadscore = $anonContact->leadscore;
        }
        if (!isset($this->email)) {
            $this->email = $anonContact->email;
        }
        if (!isset($this->reverseIp)) {
            $this->reverseIp = $anonContact->reverseIp;
        }
        $fingerprintRecord->anonymous = false;
        $fingerprintRecord->update('anonymous');
        $this->mergeRelatedRecords($anonContact);
        $this->fingerprintId = $fingerprintRecord->id;
        // Update the fingerprintId so that the Fingerprint is not deleted
        // by afterDelete() when the AnonContact is deleted.
        $this->update(array('fingerprintId'));
        $anonContact->delete();
    }

}
