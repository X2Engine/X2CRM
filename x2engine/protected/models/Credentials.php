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
 * Model implementing encrypted, generic credentials storage.
 *
 * @property array $authModelLabels (read-only) labels for embedded models;
 * 	classes to display labels.
 * @property array $defaultCredentials (read-only) all credential default
 * 	records indexed by user ID and service type
 * @property array $defaultSubstitutes (read-only) a map of service types to
 * 	valid embedded classes for storing data for that service. For example, the
 * 	Google account model can be used for sending email just as well as the
 * 	generic email account model, so it would need to be included among a list of
 *  credentials to use as the default email account.
 * @property array $defaultSubstitutesInv (read-only) Like {@link defaultSubstitutes}
 * 	but "inverted"; displays, for a given model class, the list of service types
 * 	for which it can act as a stand-in.
 * @property bool $isInUseBySystem (read-only) indicates whether the attribute
 * 	is being used for some system-wide/generic task.
 * @property array $serviceLabels (read-only) An array of UI-friendly names for service
 * 	keyworkds, i.e. "Email Account" for "email".
 * @property array $sysUseLabels (read-only) An array of labels for system-wide
 * 	uses of system-owned credentials.
 * @package application.models
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class Credentials extends CActiveRecord {

    /**
     * When selecting a Credentials record: this as an ID indicates to use the
     * (legacy) system default method. This should only ever be used in scenarios
     * where "email" is the generic type of service being configured.
     */
    const LEGACY_ID = -1;

    /**
     * When the userId of the record is set to this value, that denotes that it
     * is available system-wide for generic tasks not tied to any one person.
     */
    const SYS_ID = -1;

    private static $_authModelLabels;
    private static $_authModels;
    private static $_defaultCredentials;
    private static $_sysDefaultCredentials;

    /**
     * Effectively the inverse map of {@link sysUseId}; declared statically to
     * avoid having to generate it.
     * @var array
     */
    public static $sysUseAlias = array(
        -1 => 'bulkEmail',
        -2 => 'serviceCaseEmail',
        -3 => 'systemResponseEmail',
        -4 => 'systemNotificationEmail'
    );

    /**
     * When selecting a user to set as the owner: values of this array as the
     * userId field in the x2_credentials_default record indicate that it is
     * it's owned by no one but can be used by anyone, and that it's the default
     * for a particular usage that is generic (i.e. not tied to any one user),
     * i.e. bulk email (which might typically use a non-personal address).
     *
     * Different values of userId derived from this array hence distinguish
     * different types of usage for system-wide credentials.
     * @var array
     */
    public static $sysUseId = array(
        'bulkEmail' => -1,
        'serviceCaseEmail' => -2,
        'systemResponseEmail' => -3,
        'systemNotificationEmail' => -4
    );

    /**
     * For each system use type, define (as a comma-delineated list) the service
     * type for that system use type.
     * @var type
     */
    public static $sysUseTypes = array(
        -1 => 'email',
        -2 => 'email',
        -3 => 'email',
        -4 => 'email'
    );

    /**
     * Stores {@link isInUseBySystem}
     * @var bool
     */
    private $_isInUseBySystem;

    /**
     * Model classes to include/list as valid for storing auth data
     * @var array
     */
    protected $validModels = array(
        'EmailAccount',
        'GMailAccount',
        'MandrillAccount',
        'MailjetAccount',
        'MailgunAccount',
        'Office365EmailAccount',
        'OutlookEmailAccount',
        'SendgridAccount',
        'SESAccount',
        'YahooEmailAccount',
        'RackspaceEmailAccount',
        'TwilioAccount',
        'DocusignAccount',
        'LinkedInAccount',
        'DropboxAccount',
        'TwitterApp',
        'GoogleProject',
        'OutlookProject',
        'JasperServer',
        'X2HubConnector',
    );

    /**
     * Model to chose for Bounce Handling Accounts
     * @var array
     */
    public $validBouncedModels = array(
        'GMailAccount' => 'Google Email Account',
        'YahooEmailAccount' => 'Yahoo Email Account',
        'OutlookEmailAccount' => 'Outlook Email Account',
        'Office365EmailAccount' => 'Office 365 Email Account'
    );
    /**
     * Model classes which support the IMAP protocol
     * @var array
     */
    protected static $imapModels = array(
        'EmailAccount',
        'GMailAccount',
        'Office365EmailAccount',
        'OutlookEmailAccount',
        'YahooEmailAccount',
        'RackspaceEmailAccount',
    );

    public function afterDelete() {
        parent::afterDelete();

        EmailInboxes::model()->updateAll(
                array('credentialId' => null), 'credentialId=:id', array(':id' => $this->id)
        );
    }

    public function attributeLabels() {
        return array(
            'name' => Yii::t('app', 'Name'),
            'userId' => Yii::t('app', 'Owner'),
            'private' => Yii::t('app', 'Private'),
            'isEncrypted' => Yii::t('app', 'Encryption Enabled'),
            'createDate' => Yii::t('app', 'Date Created'),
            'lastUpdated' => Yii::t('app', 'Date Last Updated'),
            'auth' => Yii::t('app', 'Authentication Details'),
        );
    }

    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'User', array('userId' => 'id')),
        );
    }

    public function behaviors() {
        return array(
            'JSONEmbeddedModelFieldsBehavior' => array(
                'class' => 'application.components.behaviors.JSONEmbeddedModelFieldsBehavior',
                'transformAttributes' => array('auth'),
                'templateAttr' => 'modelClass',
                'encryptedFlagAttr' => 'isEncrypted',
            ),
        );
    }


    public function afterSave() {
        if ($this->modelClass &&
                in_array($this->modelClass, array('TwitterApp', 'GoogleProject', 'OutlookProject', 'JasperServer', 'X2HubConnector'))) {

            $modelClass = $this->modelClass;
            $prop = $modelClass::getAdminProperty();
            Yii::app()->settings->$prop = $this->id;
            Yii::app()->settings->save();
        }
        parent::afterSave();
    }

    public function beforeDelete() {
        Yii::app()->db->createCommand()->delete('x2_credentials_default', "credId=:id", array(':id' => $this->id));
        return parent::beforeDelete();
    }

    /**
     * Actions to take during setting of a default, especially when the default
     * is system-wide.
     * @param type $event
     */
    public function defaultHooks($userId, $serviceType) {
        switch ($serviceType) {
            case 'email':
                $adminAliasMap = array(
                    'bulkEmail' => 'emailBulkAccount',
                    'serviceCaseEmail' => 'serviceCaseEmailAccount',
                    'systemResponseEmail' => 'webLeadEmailAccount',
                    'systemNotificationEmail' => 'emailNotificationAccount'
                );

                // For these system use aliases: set the appropriate values in the admin 
                // model for consistency with legacy configuration page in admin
                if (array_key_exists($userId, self::$sysUseAlias)) {
                    $adminAttr = $adminAliasMap[self::$sysUseAlias[$userId]];
                    Yii::app()->settings->{$adminAttr} = $this->id;
                    Yii::app()->settings->update(array($adminAttr));
                }
                break;
        }
    }

    /**
     * Returns the model with default credentials for a given type.
     * @param type $userId The ID of the user whose credentials are being looked up
     * @param type $type The type of service for which credentials are being looked up
     */
    public function findDefault($userId, $serviceType) {
        if (array_key_exists($userId, $this->defaultCredentials)) {
            if (array_key_exists($serviceType, $this->defaultCredentials[$userId])) {
                return self::model()->findByPk($this->defaultCredentials[$userId][$serviceType]);
            }
        }
        // Fallback: return the first model found associated with user ID that meets the criteria for use ($serviceType)
        $criteria = new CDbCriteria(array('condition' => '`userId`=:uid', 'params' => array(':uid' => $userId)));
        if (array_key_exists($serviceType, $this->defaultSubstitutes)) {
            if (count($this->defaultSubstitutes[$serviceType])) {
                $criteria->addInCondition('modelClass', $this->defaultSubstitutes[$serviceType]);
            }
        }
        return self::model()->find($criteria);
    }

    /**
     * An array of credential storage model objects, for reference
     * @return type
     */
    public function getAuthModels() {
        if (!isset(self::$_authModels)) {
            self::$_authModels = array();
            foreach ($this->validModels as $class) {
                self::$_authModels[$class] = new $class;
            }
        }
        return self::$_authModels;
    }

    /**
     * Getter for {@link authModelLabels}
     * @return type
     */
    public function getAuthModelLabels() {
        if (!isset(self::$_authModelLabels)) {
            self::$_authModelLabels = array();
            foreach ($this->authModels as $class => $model) {
                self::$_authModelLabels[$class] = $model->modelLabel();
            }
        }
        return self::$_authModelLabels;
    }

    /**
     * Getter for {@link defaultCredentials}
     * @param type $d
     * @return type
     */
    public function getDefaultCredentials($refresh = false) {
        if (!isset(self::$_defaultCredentials) || $refresh) {
            $allDefaults = Yii::app()->db->createCommand()
                            ->select('*')->from('x2_credentials_default')->queryAll();
            self::$_defaultCredentials = array_fill_keys(array_map(function($d) {
                        return $d['userId'];
                    }, $allDefaults), array());
            foreach ($allDefaults as $d) {
                self::$_defaultCredentials[$d['userId']][$d['serviceType']] = $d['credId'];
            }
        }
        return self::$_defaultCredentials;
    }

    /**
     * Returns the value for {@link defaultSubstitutes}
     */
    public function getDefaultSubstitutes() {
        return array(
            'email' => array(
                'EmailAccount',
                'GMailAccount',
                'MandrillAccount',
                'MailjetAccount',
                'MailgunAccount',
                'Office365EmailAccount',
                'OutlookEmailAccount',
                'SendgridAccount',
                'SESAccount',
                'YahooEmailAccount',
                'RackspaceEmailAccount',
            ),
            'doc' => array(
                'DocusignAccount'
            ),
            'sms' => array(
                'TwilioAccount',
            ),
            'twitter' => array(
                'TwitterApp'
            ),
            'googleProject' => array(
                'GoogleProject'
            ),
            'outlookProject' => array(
                'OutlookProject'
            ),
            'jasperServer' => array(
                'JasperServer'
            ),
            'x2HubConnector' => array(
                'X2HubConnector'
            ),
            'linkedIn' => array('LinkedInAccount'),
            'dropbox' => array('DropboxAccount'),
        );
    }

    /**
     * Returns the value for {@link defaultSubstitutesInv}
     */
    public function getDefaultSubstitutesInv() {
        return array(
            'EmailAccount' => array('email'),
            'GMailAccount' => array('email'),
            'MandrillAccount' => array('email'),
            'MailjetAccount' => array('email'),
            'MailgunAccount' => array('email'),
            'Office365EmailAccount' => array('email'),
            'OutlookEmailAccount' => array('email'),
            'SendgridAccount' => array('email'),
            'SESAccount' => array('email'),
            'YahooEmailAccount' => array('email'),
            'RackspaceEmailAccount' => array('email'),
            'TwilioAccount' => array('sms'),
            'DocusignAccount' => array('doc'),
            'TwitterApp' => array('twitter'),
            'LinkedInAccount' => array('linkedIn'),
            'DropboxAccount' => array('dropbox'),
            'GoogleProject' => array('googleProject'),
            'OutlookProject' => array('outlookProject'),
            'JasperServer' => array('jasperServer'),
            'X2HubConnector' => array('x2HubConnector'),
        );
    }

    /**
     * Gets the default service record ID for the user of a given type.
     */
    public function getDefaultUserAccount($userId = null, $type = 'email') {
        $userId = $userId === null ? Yii::app()->user->id : $userId;
        $defaultCreds = $this->defaultCredentials;
        if (array_key_exists($userId, $defaultCreds)) {
            $userDefaults = $defaultCreds[$userId];
            if (array_key_exists($type, $userDefaults)) {
                return $userDefaults[$type];
            }
            if (array_key_exists($type, $userDefaults))
                return $userDefaults[$type];
        }
        return self::LEGACY_ID;
    }

    /**
     * Getter for {@link sysDefaultCredentials}
     * @param type $refresh
     */
    public function getIsInUseBySystem() {
        if (!isset($this->_isInUseBySystem)) {
            if ($this->userId != self::SYS_ID) {
                $this->_isInUseBySystem = false;
            } else {
                $defaults = $this->getDefaultCredentials();
                $this->_isInUseBySystem = false;
                foreach (self::$sysUseId as $alias => $id) {
                    if (isset($defaults[$id])) {
                        if (in_array($this->id, $defaults[$id])) {
                            $this->_isInUseBySystem = true;
                            break;
                        }
                    }
                }
            }
        }
        return $this->_isInUseBySystem;
    }

    public function getAuthModel() {
        if (!$this->auth instanceof JSONEmbeddedModel) {
            $this->instantiateField('auth');
        }
        return $this->auth;
    }

    /**
     * Returns an appropriate title for create/update pages.
     * @return type
     */
    public function getPageTitle() {
        if (method_exists($this->getAuthModel(), 'getPageTitle')) {

            return $this->getAuthModel()->getPageTitle();
        } else {
            return $this->isNewRecord ?
                    Yii::t('app', "New {service}", array('{service}' => $this->serviceLabel)) :
                    Yii::t('app', 'Editing:') . " <em>{$this->name}</em> ({$this->serviceLabel})";
        }
    }

    /**
     * Obtains the service type label (UI-friendly name for the category of credentials)
     * @return type
     */
    public function getServiceLabel() {
        return $this->authModelLabels[$this->modelClass];
    }

    /**
     * Gets translated labels for each service type ({@link serviceLabels})
     * @return array
     */
    public function getServiceLabels() {
        return array(
            'email' => Yii::t('app', 'Email Account'),
            'sms' => Yii::t('app', 'SMS Account'),
            'twitter' => Yii::t('app', 'Twitter App'),
            'dropbox' => Yii::t('app', 'Dropbox App'),
            'linkedIn' => Yii::t('app', 'LinkedIn App'),
            'googleProject' => Yii::t('app', 'Google Project'),
            'outlookProject' => Yii::t('app', 'Outlook Project'),
            'jasperServer' => Yii::t('app', 'Jasper Server'),
            'doc' => Yii::t('app', 'Document Account'),
            'x2HubConnector' => Yii::t('app', 'X2Hub Connector'),
        );
    }

    /**
     * Gets a UI-friendly list of substitute classes to names for the current
     * embedded model (i.e. for a selector of services for which the current
     * credentials should be used as default)
     * @return array
     */
    public function getSubstituteLabels() {
        $subInv = $this->defaultSubstitutesInv[$this->modelClass];
        $subLab = array();
        $serviceLabels = $this->getServiceLabels();
        foreach ($subInv as $serviceType) {
            $subLab[$serviceType] = $serviceLabels[$serviceType];
        }

        return $subLab;
    }

    /**
     * Returns a list of labels for designated systemwide-use types.
     * @return type 
     */
    public function getSysUseLabel() {
        return array(
            -1 => Yii::t('app', 'Bulk Email Account'),
            -2 => Yii::t('app', 'Service Case Email Account'),
            -3 => Yii::t('app', 'System Response Emailer'),
            -4 => Yii::t('app', 'System Notification Emailer')
        );
    }

    /**
     * @param CModel $model Model whose attribute is being used to specify a set of credentials
     * @param string $name Attribute storing the ID of the credentials record
     * @param string $type Keyword specifying the "service type" (i.e. "email" encompasess 
     *  credentials 
     *  with modelClass "EmailAccount" and "GMailAccount"
     * @param integer $uid The user ID or system role ID for which the input is being generated
     * @param array $htmlOptions HTML options to pass to {@link CHtml::activeDropDownList()}
     * @param boolean $getNameEmailsArr if true, returned array will include array indexed by 
     *  credId which 
     *  contains associated email and name
     * @return array containing values which can be used to instantiate an activeDropDownList.
     *  This inludes an array of credential names as well an array of the options' selected 
     *  attributes.
     */
    public static function getCredentialOptions(
        $model,
        $name,
        $type = 'email',
        $uid = null,
        $htmlOptions = array(),
        $excludeLegacy = false,
        $imapOnly = false,
        $isBouncedAccount = false
    ) {

        // First get credentials available to the user:
        $defaultUserId = in_array($uid, self::$sysUseId) ?
                $uid :
                ($uid !== null ? $uid : Yii::app()->user->id); // The "user" (actual user or system role)
        $uid = Yii::app()->user->id; // The actual user
        // Users can always use their own credentials, it's assumed
        $criteria = $isBouncedAccount ?  new CDbCriteria() : new CDbCriteria(array('params' => array(':uid' => $uid)));
        $staticModel = self::model();
        if (!$isBouncedAccount) {
            $staticModel->userId = self::SYS_ID;
            $criteria->addCondition('userId=:uid');
        }

        // Exclude accounts types that do not support IMAP if requested
        if ($imapOnly) {
            $criteria->addInCondition('modelClass', self::$imapModels);
        }
        $isBouncedAccount = $isBouncedAccount ? 1:0;
        // Exclude accounts types that do not support IMAP if requested
        $criteria->addCondition("isBounceAccount=$isBouncedAccount");

        // Include system-owned credentials
        if (Yii::app()->user->checkAccess(
                        'CredentialsSelectSystemwide', array('model' => $staticModel))) {

            $criteria->addCondition('userId=' . self::SYS_ID, 'OR');
            $criteria->addCondition("isBounceAccount=$isBouncedAccount", 'AND');
        } else { // Select the user's own default
            $defaultUserId = $uid;
        }
        $staticModel->private = 0;

        // Include non-private credentials if the user has access to them
        if (Yii::app()->user->checkAccess(
                        'CredentialsSelectNonPrivate', array('model' => $staticModel))) {
            $criteria->addCondition('private=0', 'OR');
            $criteria->addCondition("isBounceAccount=$isBouncedAccount", 'AND');
        }
        /* Cover only credentials for the given type of third-party service for which the selector 
          field is being used: */
        $criteria->addInCondition('modelClass', $staticModel->defaultSubstitutes[$type]);
        $credRecords = $staticModel->findAll($criteria);
        $credentials = array();
        if ($model === null || $model->$name == null) {
            // Figure out which one is default since it hasn't been set yet
            $defaultCreds = $staticModel->getDefaultCredentials();
            if ($type == 'email' || $type == 'sms' || $type == 'x2HubConnector') {
                $selectedCredentials = self::LEGACY_ID;
            }
            if (array_key_exists($defaultUserId, $defaultCreds)) {
                if (array_key_exists($type, $defaultCreds[$defaultUserId])) {
                    $selectedCredentials = $defaultCreds[$defaultUserId][$type];
                }
            }
        } else {
            // Use the one previously set
            $selectedCredentials = $model->$name;
        }
        // Compose options for the selector
        foreach ($credRecords as $cred) {
            if ($imapOnly && $type == 'email' && $cred->auth->disableInbox) {
                continue;
            }
            $credentials[$cred->id] = $cred->name;
            if ($type == 'email') {
                $credentials[$cred->id] = Formatter::truncateText($credentials[$cred->id] .
                                ' : "' . $cred->auth->senderName . '" <' . $cred->auth->email . '>', 50);
            } else if ($type == 'sms') {
                $credentials[$cred->id] = Formatter::truncateText($credentials[$cred->id] .
                                ' : "' . $cred->auth->from . '"', 50);
            }
        }
        if ($type == 'email' && !$excludeLegacy) {// Legacy email delivery method(s)
            $credentials[self::LEGACY_ID] = Yii::t('app', 'System default (legacy)');
        }
        $options = array();
        $selectedOption = $selectedCredentials;
        foreach ($credentials as $credId => $label) {
            if ($credId == $selectedCredentials) {
                $options[$credId] = array('selected' => 'selected');
            } else {
                $options[$credId] = array('selected' => false);
            }
        }
        if ($type == 'email') {
            $options[self::LEGACY_ID]['class'] = 'legacy-email';
        }

        $htmlOptions['options'] = $options;

        $retDict = array(
            'credentials' => $isBouncedAccount ? array('' => ' - select bounce handling account - ')+$credentials : $credentials,
            'htmlOptions' => $htmlOptions,
            'selectedOption' => $selectedOption
        );
        return $retDict;
    }

    /**
     * Generates a select input for a form that includes a list of credentials
     * available for the current user.
     * @param CModel $model Model whose attribute is being used to specify a set of credentials
     * @param string $name Attribute storing the ID of the credentials record
     * @param string $type Keyword specifying the "service type" (i.e. "email" encompasess 
     *  credentials with modelClass "EmailAccount" and "GMailAccount"
     * @param integer $uid The user ID or system role ID for which the input is being generated
     * @param array $htmlOptions HTML options to pass to {@link CHtml::activeDropDownList()}
     * @param array $excludeLegacy Exclude the sendmail legacy option
     * @param array $imapOnly Hide models which do not support IMAP
     * @param boolean $isBouncedAccount is current email is serving as Bounced Account
     * @return string
     */
    public static function selectorField(
        $model,
        $name,
        $type = 'email',
        $uid = null,
        $htmlOptions = array(),
        $excludeLegacy = false,
        $imapOnly = false,
        $isBouncedAccount = false
    ) {

        $retDict = self::getCredentialOptions($model, $name, $type, $uid, $htmlOptions, $excludeLegacy, $imapOnly, $isBouncedAccount);
        $credentials = $retDict['credentials'];
        $htmlOptions = $retDict['htmlOptions'];
        return CHtml::activeDropDownList($model, $name, $credentials, $htmlOptions);
    }

    /**
     * Given a user id, returns an array of all service types for which the
     * current record is default.
     * @param integer $uid
     * @return array
     */
    public function isDefaultOf($uid) {
        $services = array();
        if (array_key_exists($uid, $this->defaultCredentials)) {
            foreach ($this->defaultCredentials[$uid] as $service => $id) {
                if ($id == $this->id) {
                    $services[] = $service;
                }
            }
        }
        return $services;
    }

    /**
     * Set the default account for a given user to use for a given service.
     * @param type $userId ID of the user whose default is getting set. Null for generic/system 
     *  account.
     * @param type $serviceType Service type, i.e. 'email'
     */
    public function makeDefault($userId, $serviceType, $hooks = true) {
        if ($hooks) {
            $this->defaultHooks($userId, $serviceType);
        }
        Yii::app()->db->createCommand()
                ->delete('x2_credentials_default', 'userId=:uid AND serviceType=:st', array(
                    ':uid' => $userId,
                    ':st' => $serviceType
        ));
        Yii::app()->db->createCommand()
                ->insert('x2_credentials_default', array(
                    'userId' => $userId,
                    'serviceType' => $serviceType,
                    'credId' => $this->id
        ));
    }

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public function rules() {
        return array(
            array('name,private,auth', 'safe'),
            array('userId', 'safe', 'on' => 'create'),
            array('name', 'required')
        );
    }

    public function tableName() {
        return 'x2_credentials';
    }

}
