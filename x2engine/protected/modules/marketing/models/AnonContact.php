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
 * Model for an anonymous contact. This records information about a lead
 * before they register and are converted to a contact.
 *
 * @package application.models
 */
class AnonContact extends X2Model {

    public $supportsWorkflow = false;
    //public $supportsFieldLevelPermissions = false;

    /**
     * Returns the static model of the specified AR class.
     * @return Fields the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_anon_contact';
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array_merge (parent::relations (), 
                array (
                    'fingerprint' => array(self::BELONGS_TO, 'Fingerprint', 'fingerprintId'),
                )
            );
    }

    /**
     * Ensure a valid tracking key is set
     * @return boolean whether or not to save
     */
    public function beforeSave() {
        if($this->trackingKey === null) {
            $this->trackingKey = X2Model::model('Contacts')->getNewTrackingKey();
        }

        $maxAnonContacts = Yii::app()->settings->maxAnonContacts;
        $count = Yii::app()->db->createCommand()
                ->select('COUNT(*)')
                ->from('x2_anon_contact')
                ->queryScalar();
        if ($count > $maxAnonContacts) {
            // Remove the last modified AnonContact and its associated Actions
            // if the limit has been reached.
            $lastModifiedId = Yii::app()->db->createCommand()
                    ->select('id')
                    ->from('x2_anon_contact')
                    ->order('lastUpdated ASC')
                    ->queryScalar();
            $actions = X2Model::model('Actions')->deleteAllByAttributes(array(
                'associationType' => 'anoncontact',
                'associationId' => $lastModifiedId,
            ));
            // find and then delete so that the onAfterDelete event gets triggered
            $anonContact = X2Model::model('AnonContact')->findByPk($lastModifiedId);
            if ($anonContact) {
                $anonContact->disableBehavior ('changelog');
                $anonContact->delete ();
            }
        }
        return parent::beforeSave();
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        $rules = parent::rules();
        return $rules;
    }

    public function behaviors() {
        return array_merge (parent::behaviors (), array(
            'TagBehavior' => array('class' => 'TagBehavior'),
            'FingerprintBehavior'=>array(
                'class'=>'FingerprintBehavior',
            ),
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'module' => 'marketing',
                'autoCompleteSource' => null,
                'viewRoute' => '/marketing/marketing/anonContactView'
            ),
            'ERememberFiltersBehavior' => array(
                'class'=>'application.components.behaviors.ERememberFiltersBehavior',
                'defaults'=>array(),
                'defaultStickOnClear'=>false
            ),
        ));
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('id', $this->id);
        $criteria->compare('createDate', $this->createDate);
        $criteria->compare('lastUpdated', $this->lastUpdated);
        $criteria->compare('trackingKey', $this->trackingKey);
        $criteria->compare('email', $this->email);
        $criteria->compare('leadscore', $this->leadscore);

        if (!Yii::app()->user->isGuest) {
            $pageSize = Profile::getResultsPerPage();
        } else {
            $pageSize = 20;
        }

        return new SmartActiveDataProvider(get_class($this), array(
            'pagination' => array(
                'pageSize' => $pageSize,
            ),
            'criteria' => $criteria,
        ));
    }

    /**
     * Tries to find an anonymous contact by fingerprint 
     * @param int $fingerprint 
     * @return null|AnonContact
     */
    public function findByFingerprint ($fingerprint, $attributes) {
        $anonContact = null;

        $fingerprintRecord = X2Model::model('Fingerprint')
            ->findByAttributes(array(
                'fingerprint'=>$fingerprint,
                'anonymous'=>1,
            ));

        if (!isset($fingerprintRecord)) {
            // Try a partial match in case the fingerprint has changed
            list ($contact, $bits) = Fingerprint::partialMatch($attributes);
            if ($contact !== null && $contact instanceof AnonContact) {
                $fingerprintRecord = X2Model::model('Fingerprint')
                    ->findByPk($contact->fingerprintId);
            }
        }
        if (isset($fingerprintRecord)) {
            $anonContact = X2Model::model('AnonContact')
                ->findByAttributes(
                    array('fingerprintId'=>$fingerprintRecord->id));
        }
        return $anonContact;
    }

    public function getDisplayName ($plural=true) {
        return Yii::t('app', 'Anonymous Contact'.($plural ? 's' : ''));
    }

}
