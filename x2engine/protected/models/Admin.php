<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
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
 * This is the model class for table "x2_admin".
 * @package X2CRM.models
 */
class Admin extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_admin';
    }

    public function behaviors() {
        return array(
            'JSONEmbeddedModelFieldsBehavior' => array(
                'class' => 'application.components.JSONEmbeddedModelFieldsBehavior',
                'fixedModelFields' => array('emailDropbox' => 'EmailDropboxSettings'),
                'transformAttributes' => array('emailDropbox'),
                
            )
        );
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('emailType,emailFromName, emailFromAddr', 'requiredIfSysDefault', 'field' => 'emailBulkAccount'),
            array('serviceCaseFromEmailName, serviceCaseFromEmailAddress', 'requiredIfSysDefault', 'field' => 'serviceCaseEmailAccount'),
            array('serviceCaseEmailSubject, serviceCaseEmailMessage', 'required'),
            array('timeout, webTrackerCooldown, chatPollTime, ignoreUpdates, rrId, onlineOnly, emailBatchSize, emailInterval, emailPort, installDate, updateDate, updateInterval, workflowBackdateWindow, workflowBackdateRange', 'numerical', 'integerOnly' => true),
            // accounts, sales,
            array('chatPollTime', 'numerical', 'max' => 10000, 'min' => 100),
            array('currency', 'length', 'max' => 3),
            array('emailUseAuth, emailUseSignature', 'length', 'max' => 10),
            array('emailType, emailSecurity,gaTracking_internal,gaTracking_public', 'length', 'max' => 20),
            array('webLeadEmail, leadDistribution, emailFromName, emailFromAddr, emailHost, emailUser, emailPass', 'length', 'max' => 255),
            // array('emailSignature', 'length', 'max'=>512),
            array('batchTimeout','numerical','integerOnly' => true),
            array('emailBulkAccount,serviceCaseEmailAccount,emailDropbox', 'safe'),
            array('emailBulkAccount', 'setDefaultEmailAccount', 'alias' => 'bulkEmail'),
            array('serviceCaseEmailAccount', 'setDefaultEmailAccount', 'alias' => 'serviceCaseEmail'),
	    array('webLeadEmailAccount','setDefaultEmailAccount','alias' => 'systemResponseEmail'),
	    array('emailNotificationAccount','setDefaultEmailAccount','alias'=>'systemNotificationEmail'),
            array('emailSignature', 'length', 'max' => 4096),
            array('externalBaseUrl','url','allowEmpty'=>true),
            array('externalBaseUrl','match','pattern'=>':/$:','not'=>true,'allowEmpty'=>true,'message'=>Yii::t('admin','Value must not include a trailing slash.')),
            array('enableWebTracker, quoteStrictLock, workflowBackdateReassignment,emailDropbox_createContact,emailDropbox_zapLineBreaks,emailDropbox_emptyContact,emailDropbox_logging', 'boolean'),
            array('gaTracking_internal,gaTracking_public', 'match', 'pattern' => "/'/", 'not' => true, 'message' => Yii::t('admin', 'Invalid property ID')),
            array('emailDropbox_alias', 'length', 'max' => 50),
                // The following rule is used by search().
                // Please remove those attributes that should not be searched.
                // array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('admin', 'ID'),
            // 'accounts' => Yii::t('admin','Accounts'),
            // 'sales' => Yii::t('admin','Opportunities'),
            'timeout' => Yii::t('admin', 'Session Timeout'),
            'webLeadEmail' => Yii::t('admin', 'Web Lead Email'),
            'enableWebTracker' => Yii::t('admin', 'Enable Web Tracker'),
            'webTrackerCooldown' => Yii::t('admin', 'Web Tracker Cooldown'),
            'currency' => Yii::t('admin', 'Currency'),
            'chatPollTime' => Yii::t('admin', 'Notification Poll Time'),
            'ignoreUpdates' => Yii::t('admin', 'Ignore Updates'),
            'rrId' => Yii::t('admin', 'Round Robin ID'),
            'leadDistribution' => Yii::t('admin', 'Lead Distribution'),
            'onlineOnly' => Yii::t('admin', 'Online Only'),
            'emailBulkAccount' => Yii::t('admin', 'Send As (when sending bulk email)'),
            'emailFromName' => Yii::t('admin', 'Sender Name'),
            'emailFromAddr' => Yii::t('admin', 'Sender Email Address'),
            'emailBatchSize' => Yii::t('admin', 'Batch Size'),
            'emailInterval' => Yii::t('admin', 'Interval (Minutes)'),
            'emailUseSignature' => Yii::t('admin', 'Email Signatures'),
            'emailSignature' => Yii::t('admin', 'Default Signature'),
            'emailType' => Yii::t('admin', 'Method'),
            'emailHost' => Yii::t('admin', 'Hostname'),
            'emailPort' => Yii::t('admin', 'Port'),
            'emailUseAuth' => Yii::t('admin', 'Authentication'),
            'emailUser' => Yii::t('admin', 'Username'),
            'emailPass' => Yii::t('admin', 'Password'),
            'emailSecurity' => Yii::t('admin', 'Security'),
            'installDate' => Yii::t('admin', 'Installed'),
            'updateDate' => Yii::t('admin', 'Last Update'),
            'updateInterval' => Yii::t('admin', 'Version Check Interval'),
            'googleClientId' => Yii::t('admin', 'Google Client ID'),
            'googleClientSecret' => Yii::t('admin', 'Google Client Secret'),
            'googleAPIKey' => Yii::t('admin', 'Google API Key'),
            'googleIntegration' => Yii::t('admin', 'Activate Google Integration'),
            'inviteKey' => Yii::t('admin', 'Invite Key'),
            'workflowBackdateWindow' => Yii::t('admin', 'Workflow Backdate Window'),
            'workflowBackdateRange' => Yii::t('admin', 'Workflow Backdate Range'),
            'workflowBackdateReassignment' => Yii::t('admin', 'Workflow Backdate Reassignment'),
            'serviceCaseEmailAccount' => Yii::t('admin', 'Send As (to service requesters)'),
            'serviceCaseFromEmailName' => Yii::t('admin', 'Sender Name'),
            'serviceCaseFromEmailAddress' => Yii::t('admin', 'Sender Email Address'),
            'serviceCaseEmailSubject' => Yii::t('admin', 'Subject'),
            'serviceCaseEmailMessage' => Yii::t('admin', 'Email Message'),
            'gaTracking_public' => Yii::t('admin', 'Google Analytics Property ID (public)'),
            'gaTracking_internal' => Yii::t('admin', 'Google Analytics Property ID (internal)'),
            'serviceDistribution' => Yii::t('admin', 'Service Distribution'),
            'serviceOnlineOnly' => Yii::t('admin', 'Service Online Only'),
            'eventDeletionTime' => Yii::t('admin', 'Event Deletion Time'),
            'eventDeletionTypes' => Yii::t('admin', 'Event Deletion Types'),
            'properCaseNames' => Yii::t('admin', 'Proper Case Names'),
            'corporateAddress' => Yii::t('admin', 'Corporate Address'),
            'contactNameFormat' => Yii::t('admin', 'Contact Name Format'),
            'webLeadEmailAccount' => Yii::t('admin','Send As (to web leads)'),
            'emailNotificationAccount' => Yii::t('admin','Send As (when notifying users)'),
            'batchTimeout' => Yii::t('app','Time limit on batch actions'),
            'externalBaseUrl' => Yii::t('app','External / Public Base URL')
        );
    }

    public static function getModelList(){
        $modelList = array();
        foreach(X2Model::model('Modules')->findAllByAttributes(array('editable' => true, 'visible' => 1)) as $module){
            if(X2Model::getModelName($module->name)){
                $modelName = $module->name;
            }else{
                $modelName = ucfirst($module->name);
            }
            if(Yii::app()->user->checkAccess(ucfirst($module->name).'Index', array())){
                $modelList[$modelName] = $module->title;
            }
        }
        return array_map(function($term){
                            return Yii::t('app', $term);
                        }, $modelList);
    }

    public function requiredIfSysDefault($attribute, $params){
        if(empty($this->$attribute) && $this->{$params['field']} == Credentials::LEGACY_ID)
            $this->addError($attribute, Yii::t('yii', '{attribute} cannot be blank.', array('{attribute}' => $this->getAttributeLabel($attribute))));
    }

    public function setDefaultEmailAccount($attribute, $params){
        if($this->$attribute != Credentials::LEGACY_ID){
            $cred = Credentials::model()->findByPk($this->$attribute);
            if($cred)
                $cred->makeDefault(Credentials::$sysUseId[$params['alias']], 'email', false);
        } else{
            Yii::app()->db->createCommand()->delete('x2_credentials_default', 'userId=:uid AND serviceType=:st', array(':uid' => Credentials::$sysUseId[$params['alias']], ':st' => 'email'));
        }
    }

}
