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




Yii::import('application.components.behaviors.JSONEmbeddedModelFieldsBehavior');
Yii::import('application.components.TwitterAPI.TwitterAPIExchange');

/**
 * This is the model class for table "x2_admin".
 * @package application.models
 */
class Admin extends X2ActiveRecord {

    protected $_oldAttributes = array();

    /**
     * Returns the static model of the specified AR class.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    public static function getDoNotEmailLinkDefaultText() {
        return Yii::t('admin', 'I do not wish to receive these emails.');
    }

    public static function getDoNotEmailDefaultPage() {
        $message = Yii::t(
                        'admin', 'You will no longer receive emails from this sender.');
        return '<html><head><title>' . $message .
                '</title></head><body>' . $message . '</body></html>';
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'x2_admin';
    }

    /**
     * Gets Google integration credentials
     * 
     * @var boolean $refresh Specify if refresh enabled 
     */
    private $_googleIntegrationCredentials;

    public function getGoogleIntegrationCredentials($refresh = false) {
        if (!isset($this->_googleIntegrationCredentials) || $refresh) {
            $credId = Yii::app()->settings->googleCredentialsId;
            if ($credId && ($credentials = Credentials::model()->findByPk($credId))) {
                $this->_googleIntegrationCredentials = array(
                    'apiKey' => $credentials->auth->apiKey,
                    'clientId' => $credentials->auth->clientId,
                    'clientSecret' => $credentials->auth->clientSecret,
                );
            }
        }
        return $this->_googleIntegrationCredentials;
    }

    /**
     * Check if hub is enabled
     * 
     * @return boolean If hub enabled
     */
    public function checkHubEnabled() {
        if ($this->hubCredentialsId) {
            $creds = Credentials::model()->findByPk($this->hubCredentialsId);
            return $creds && $creds->auth && $creds->auth->hubEnabled;
        }
        return false;
    }
    
    /**
     * @param string $type Specify optional activity type
     */
    public function getGoogleApiKey($type = null) {
        $types = array('maps', 'staticmap', 'directions', 'geocoding');

        // Check hub first
        $apiKey = null;
        if (HubConnectionBehavior::checkHubEnabled()) {
            $creds = Credentials::model()->findByPk($this->hubCredentialsId);
            if (!(in_array($type, $types) && !$creds->auth->enableGoogleMaps)) {
                $hub = Yii::app()->controller->attachBehavior('HubConnectionBehavior', new HubConnectionBehavior);
                $apiKey = $hub->getGoogleApiKey(Yii::app()->user->id, $type);
            }
        }

        // Use google integration settings if hub not enabled
        if (empty($apiKey)) {
            $creds = $this->getGoogleIntegrationCredentials();
            if ($creds && isset($creds['apiKey'])) {
                $apiKey = $creds['apiKey'];
            }
        }

        return $apiKey;
    }

    /**
     * Checks if Google Maps API is enabled
     * 
     * @return boolean If maps is enabled
     */
    public function getEnableMaps() {
        // Check hub first
        if (HubConnectionBehavior::checkHubEnabled()) {
            $hubCreds = Credentials::model()->findByPk(Yii::app()->settings->hubCredentialsId);
            if ($hubCreds instanceof X2HubConnector && $hubCreds->auth && $hubCreds->auth->enableGoogleMaps) {
                return true;
            }
        }

        // Use google integration settings if hub not enabled
        return Yii::app()->settings->googleIntegration;
    }

    /**
     * Saves attributes on initial model lookup
     */
    public function afterFind() {
        $this->_oldAttributes = $this->getAttributes();
        parent::afterFind();
    }

    public function behaviors() {
        $behaviors = array(
            'JSONFieldsBehavior' => array(
                'class' => 'application.components.behaviors.JSONFieldsBehavior',
                'transformAttributes' => array(
                    'twitterRateLimits',
                    'linkedInRateLimits',
                    'dropboxRateLimits',
                    'assetBaseUrls',
                ),
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.behaviors.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'actionPublisherTabs' => array(
                        'PublisherCommentTab' => true,
                        'PublisherActionTab' => true,
                        'PublisherCallTab' => true,
                        'PublisherTimeTab' => true,
                        'PublisherEventTab' => true,
                        'PublisherProductsTab' => false,
                    ),
                    'passwordRequirements' => array(
                        'minLength' => 0,
                        'requireMixedCase' => false,
                        'requireNumeric' => false,
                        'requireSpecial' => false,
                        'requireCharClasses' => 1,
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
        );

        $behaviors['JSONEmbeddedModelFieldsBehavior'] = array(
            'class' => 'application.components.behaviors.JSONEmbeddedModelFieldsBehavior',
            'fixedModelFields' => array('emailDropbox' => 'EmailDropboxSettings'),
            'transformAttributes' => array('emailDropbox'),
        );
        $behaviors['JSONFieldsBehavior']['transformAttributes'][] = 'appliedPackages';

        $behaviors['JSONEmbeddedModelFieldsBehavior']['fixedModelFields']['api2'] = 'Api2Settings';
        $behaviors['JSONEmbeddedModelFieldsBehavior']['transformAttributes'][] = 'api2';
        $behaviors['JSONFieldsBehavior']['transformAttributes'][] = 'ipWhitelist';
        $behaviors['JSONFieldsBehavior']['transformAttributes'][] = 'ipBlacklist';



        return $behaviors;
    }

    public function validateUniqueId($attr) {
        $value = $this->$attr;
        // flush license key info cache when license key changes
        if (!isset($this->_oldAttributes[$attr]) || $value !== $this->_oldAttributes[$attr]) {
            Yii::app()->cache2->delete($this->getLicenseKeyInfoCacheKey());
        }
    }

    /**
     * Custom validator for an array of URLs
     */
    public function validateUrlArray($attr, $params) {
        $values = $this->$attr;
        $urlValidator = new CUrlValidator;
        $allowEmpty = array_key_exists('allowEmpty', $params) && $params['allowEmpty'];

        if (is_array($values)) {
            foreach ($values as $url) {
                if ($urlValidator->validateValue($url) || ($allowEmpty && empty($url)))
                    continue;
                $this->addError(
                        $attr, Yii::t('admin', 'The specified URL "{url}" is not in the correct format.', array(
                            '{url}' => CHtml::encode($url),
                        ))
                );
            }
        }
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('unique_id', 'validateUniqueId'),
            array('emailType,emailFromName, emailFromAddr', 'requiredIfSysDefault', 'field' => 'emailBulkAccount'),
            array('serviceCaseFromEmailName, serviceCaseFromEmailAddress', 'requiredIfSysDefault', 'field' => 'serviceCaseEmailAccount'),
            array('serviceCaseEmailSubject, serviceCaseEmailMessage', 'required'),
            array('batchTimeout, timeout, loginCredsTimeout, webTrackerCooldown, chatPollTime, locationTrackingFrequency, maxUserCount, '
                . 'ignoreUpdates, rrId, onlineOnly, emailBatchSize, emailInterval, emailPort, '
                . 'installDate, updateDate, updateInterval, workflowBackdateWindow, '
                . 'workflowBackdateRange, locationTrackingDistance',
                'numerical', 'integerOnly' => true),
            // accounts, sales,
            array('loginCredsTimeout', 'numerical', 'max' => 365, 'min' => 1),
            array('duplicateFields', 'length', 'max' => 255),
            array('chatPollTime', 'numerical', 'max' => 100000, 'min' => 100),
            array('locationTrackingFrequency', 'numerical', 'max' => 60, 'min' => 1),
            array('locationTrackingDistance', 'numerical', 'max' => 10, 'min' => 1),
            array('maxUserCount', 'numerical', 'max' => 100000, 'min' => 1),
            array('currency', 'length', 'max' => 3),
            array('emailUseAuth, emailUseSignature', 'length', 'max' => 10),
            array('emailType, emailSecurity,gaTracking_internal,gaTracking_public', 'length', 'max' => 20),
            array('webLeadEmail, leadDistribution, emailFromName, emailFromAddr, emailHost, emailUser, emailPass,externalBaseUrl,externalBaseUri', 'length', 'max' => 255),
            // array('emailSignature', 'length', 'max'=>512),
            array('massActionsBatchSize', 'numerical', 'integerOnly' => true, 'min' => 5, 'max' => 100,),
            array('emailBulkAccount,serviceCaseEmailAccount', 'safe'),
            array('emailDropbox' . ',api2', 'safe'),
            array('emailBulkAccount', 'setDefaultEmailAccount', 'alias' => 'bulkEmail'),
            array('serviceCaseEmailAccount', 'setDefaultEmailAccount', 'alias' => 'serviceCaseEmail'),
            array('webLeadEmailAccount', 'setDefaultEmailAccount', 'alias' => 'systemResponseEmail'),
            array('emailNotificationAccount', 'setDefaultEmailAccount', 'alias' => 'systemNotificationEmail'),
            array('emailSignature', 'length', 'max' => 4096),
            array('externalBaseUrl', 'url', 'allowEmpty' => true),
            array('assetBaseUrls', 'validateUrlArray', 'allowEmpty' => false),
            array('externalBaseUrl', 'match', 'pattern' => ':/$:', 'not' => true, 'allowEmpty' => true, 'message' => Yii::t('admin', 'Value must not include a trailing slash.')),
            array('enableWebTracker, disableAnonContactNotifs, locationTrackingSwitch, quoteStrictLock, workflowBackdateReassignment,disableAutomaticRecordTagging,enableAssetDomains, enableUnsubscribeHeader, checkinByDefault, sessionLog, userActionBackdating, properCaseNames', 'boolean'),
            array('historyPrivacy', 'in', 'range' => array('default', 'user', 'group')),
            array('contactNameFormat', 'in', 'range' => array('firstName lastName', 'lastName, firstName')),
            array('corporateAddress', 'length', 'max' => 4096),
            array('gaTracking_internal,gaTracking_public', 'match', 'pattern' => "/'/", 'not' => true, 'message' => Yii::t('admin', 'Invalid property ID')),
            array('appDescription', 'length', 'max' => 255),
            array(
                'appName,x2FlowRespectsDoNotEmail,doNotEmailPage,doNotEmailLinkText,EmailUnSubPage',
                'safe'
            ),
            array('imapPollTimeout', 'numerical', 'max' => 30, 'min' => 5),
            array('triggerLogMax', 'numerical', 'allowEmpty' => true),
            array('maxFailedLogins,failedLoginsBeforeCaptcha', 'numerical', 'min' => 1, 'max' => 100),
            array('maxLoginHistory', 'numerical', 'min' => 10, 'max' => 10000),
            array('loginTimeout', 'numerical', 'min' => 5, 'max' => 1440),
            array('failedLoginsBeforeCaptcha', 'compare', 'compareAttribute' => 'maxFailedLogins',
                'operator' => '<=', 'message' => Yii::t('admin', 'Failed logins before CAPTCHA ' .
                        'must be less than the maximum number of failed logins.'),
            ),
                // The following rule is used by search().
                // Please remove those attributes that should not be searched.
                // array('id, accounts, sales, timeout, webLeadEmail, menuOrder, menuNicknames, chatPollTime, menuVisibility, currency', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'id' => Yii::t('admin', 'ID'),
            // 'accounts' => Yii::t('admin','Accounts'),
            // 'sales' => Yii::t('admin','Opportunities'),
            'timeout' => Yii::t('admin', 'Session Timeout'),
            'loginCredsTimeout' => Yii::t('admin', 'Login Credentials Timeout'),
            'webLeadEmail' => Yii::t('admin', 'Web Lead Email'),
            'enableWebTracker' => Yii::t('admin', 'Enable Web Tracker'),
            'disableAnonContactNotifs' => Yii::t('admin', 'Disable AnonContact Notifications'),
            'webTrackerCooldown' => Yii::t('admin', 'Web Tracker Cooldown'),
            'currency' => Yii::t('admin', 'Currency'),
            'chatPollTime' => Yii::t('admin', 'Notification Poll Time'),
            'ignoreUpdates' => Yii::t('admin', 'Ignore Updates'),
            'rrId' => Yii::t('admin', 'Round Robin ID'),
            'leadDistribution' => Yii::t('admin', 'Lead Distribution'),
            'onlineOnly' => Yii::t('admin', 'Online Only'),
            'disableAutomaticRecordTagging' =>
            Yii::t('profile', 'Disable automatic record tagging?'),
            'duplicateFields' => Yii::t('admin', 'Duplicate Fields'),
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
            'enableColorDropdownLegend' => Yii::t('admin', 'Colorize Dropdown Options?'),
            'installDate' => Yii::t('admin', 'Installed'),
            'updateDate' => Yii::t('admin', 'Last Update'),
            'updateInterval' => Yii::t('admin', 'Version Check Interval'),
            'googleIntegration' => Yii::t('admin', 'Activate Google Integration'),
            'outlookIntegration' => Yii::t('admin', 'Activate Outlook Integration'),
            'inviteKey' => Yii::t('admin', 'Invite Key'),
            'workflowBackdateWindow' => Yii::t('admin', 'Process Backdate Window'),
            'workflowBackdateRange' => Yii::t('admin', 'Process Backdate Range'),
            'workflowBackdateReassignment' => Yii::t('admin', 'Process Backdate Reassignment'),
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
            'contactNameFormat' => Yii::t('admin', 'Contact Name Format'),
            'webLeadEmailAccount' => Yii::t('admin', 'Send As (to web leads)'),
            'emailNotificationAccount' => Yii::t('admin', 'Send As (when notifying users)'),
            'batchTimeout' => Yii::t('admin', 'Time limit on batch actions'),
            'massActionsBatchSize' => Yii::t('admin', 'Batch size for grid view mass actions'),
            'maxUserCount' => Yii::t('admin', 'Manage User Count'),
            'externalBaseUrl' => Yii::t('admin', 'External / Public Base URL'),
            'externalBaseUri' => Yii::t('admin', 'External / Public Base URI'),
            'appName' => Yii::t('admin', 'Application Name'),
            'x2FlowRespectsDoNotEmail' => Yii::t(
                    'app', 'Respect contacts\' "Do not email" settings?'),
            'doNotEmailLinkText' => Yii::t('admin', '"Do not email" Link Text'),
            'doNotEmailLinkPage' => Yii::t('admin', '"Do not email" Page'),
            'doNotEmailPage' => Yii::t('admin', 'Do Not Email Page'),
            'enableAssetDomains' => Yii::t('admin', 'Enable Asset Domains'),
            'twoFactorCredentialsId' => Yii::t('admin', 'Two Factor Auth Credentials'),
            'imapPollTimeout' => Yii::t('admin', 'Email Polling Timeout'),
            'ipBlacklist' => Yii::t('admin', 'IP Blacklist'),
            'ipWhitelist' => Yii::t('admin', 'IP Whitelist'),
            'triggerLogMax' => Yii::t('admin', 'Maximum number of X2Workflow trigger logs'),
            'locationTrackingFrequency' => Yii::t('admin', 'Location Tracking Frequency'),
            'locationTrackingDistance' => Yii::t('admin', 'Location Tracking Distance'),
            'locationTracking' => Yii::t('admin', 'Location Tracking'),
            'enableFingerprinting' => Yii::t('marketing', 'Enable Fingerprinting'),
            'performHostnameLookups' => Yii::t('marketing', 'Perform Hostname Lookups'),
            'identityThreshold' => Yii::t('marketing', 'Identity Threshold'),
            'maxAnonContacts' => Yii::t('marketing', 'Max Anon Contacts'),
            'maxAnonActions' => Yii::t('marketing', 'Max Anon Actions'),
        );
    }

    public function requiredIfSysDefault($attribute, $params) {
        if (empty($this->$attribute) && $this->{$params['field']} == Credentials::LEGACY_ID)
            $this->addError($attribute, Yii::t('yii', '{attribute} cannot be blank.', array('{attribute}' => $this->getAttributeLabel($attribute))));
    }

    public function setDefaultEmailAccount($attribute, $params) {
        if ($this->$attribute != Credentials::LEGACY_ID) {
            $cred = Credentials::model()->findByPk($this->$attribute);
            if ($cred)
                $cred->makeDefault(Credentials::$sysUseId[$params['alias']], 'email', false);
        } else {
            Yii::app()->db->createCommand()->delete('x2_credentials_default', 'userId=:uid AND serviceType=:st', array(':uid' => Credentials::$sysUseId[$params['alias']], ':st' => 'email'));
        }
    }

    /**
     * Record that a number of emails have been sent, to avoid going over the
     * bulk email batch size per interval.
     * 
     * @param integer $nEmail Number of emails that will have been sent
     */
    public function countEmail($nEmail = 1) {
        $now = time();
        if (empty($this->emailStartTime))
            $this->emailStartTime = $now;
        if ($now - $this->emailStartTime > $this->emailInterval) {
            // Reset
            $this->emailStartTime = $now;
            $this->emailCount = 0;
        }
        $this->emailCount += $nEmail;
        $this->update(array('emailCount', 'emailStartTime'));
        return $this->emailCount;
    }

    /**
     * Returns true or false based on whether a number of emails to be sent will
     * exceed the batch maximum.
     *
     * @param integer $nEmail Number of emails to be sent
     */
    public function emailCountWillExceedLimit($nEmail = 1) {
        $now = time();
        if ($now - $this->emailStartTime > $this->emailInterval) {
            $this->emailStartTime = $now;
            $this->emailCount = 0;
        }
        return $this->emailCount + $nEmail > $this->emailBatchSize;
    }

    /**
     * @param array $value This should match the structure of the actionPublisherTabs property
     *  specified in the JSONFieldsDefaultValuesBehavior configuration
     */
    public function setActionPublisherTabs($value) {
        $this->actionPublisherTabs = $value;
        $this->save();
    }

    /**
     * Render button for the advanced security failed logins grid to ban or whitelist
     * an IP address or disable a user account
     */
    public static function renderACLControl($type, $target) {
        // If this is a disable user button, Just render the disable user button and return
        if ($type === 'disable') {
            $user = $target;
            $active = Yii::app()->db->createCommand()
                            ->select('status')
                            ->from('x2_users')
                            ->where('username = :user', array(
                                ':user' => $user,
                            ))->queryScalar();
            if ($active === '1') {
                return CHtml::link(Yii::t('admin', 'Disable'), array('/admin/disableUser?username=' . $target), array('class' => 'x2-button')
                );
            } else {
                $placeholder = Yii::t('admin', 'User is already disabled');
                return '<div class="x2-button disabled" title="' . $placeholder . '">' .
                        Yii::t('admin', 'Disable') .
                        '</div>';
            }
        }

        // Otherwise render a whitelist or ban button
        if ($type === 'blacklist') {
            $buttonText = Yii::t('admin', 'Ban');
            $actionUrl = array(
                '/admin/admin/banIp',
                'ip' => $target
            );
        } else {
            $buttonText = Yii::t('admin', 'Whitelist');
            $actionUrl = array(
                '/admin/admin/whitelistIp',
                'ip' => $target
            );
        }
        $method = Yii::app()->settings->accessControlMethod;
        $class = 'x2-button';
        if ($method !== $type) {
            $placeholder = Yii::t('admin', 'You must change your access control method for ' .
                            'this action to be effective.');
            return '<a class="x2-button disabled" title="' . $placeholder . '">' . $buttonText . '</a>';
        } else {
            return CHtml::link($buttonText, $actionUrl, array(
                        'class' => 'x2-button',
            ));
        }
    }

    public function getDoNotEmailLinkText() {
        if (!empty($this->doNotEmailLinkText)) {
            return $this->doNotEmailLinkText;
        }
        return self::getDoNotEmailLinkDefaultText();
    }

    /**
     * @return string 
     */
    public function renderProductKeyExpirationDate($refresh = false) {
        $html = '';
        $expirationDate = $this->getProductKeyExpirationDate($refresh);
        if ($expirationDate === 'invalid') {
            return '<div class="error-text">' .
                    CHtml::encode(Yii::t('admin', 'Invalid license key')) .
                    '</div>';
        } elseif (!is_numeric($expirationDate)) {
            return '';
        }

        $html .= '<strong>' . Yii::t('admin', 'License Expiration Date') . '</strong>:&nbsp;';
        if ($expirationDate < time()) {
            $html .= '<span class="error-text">' . Yii::app()->dateFormatter->formatDateTime(
                            $expirationDate, 'long', null) . '</span>&nbsp;' .
                    CHtml::encode(Yii::t('admin', '(expired)')) .
                    '<br />';
        } else {
            $html .= Yii::app()->dateFormatter->formatDateTime(
                            $expirationDate, 'long', null) .
                    '<br />';
        }
        return $html;
    }

    public function renderMaxUsers($refresh = false) {
        $html = '';
        $maxUsers = $this->getMaxUsers($refresh);
        $html .= '<strong>' . Yii::t('admin', 'License Max Users') . '</strong>:&nbsp;';
        if (!is_numeric($maxUsers))
            return '';
        $html .= CHtml::encode($maxUsers) . '<br />';
        return $html;
    }

    /**
     * @return string|null expiration date for app product key
     */
    public function getProductKeyExpirationDate($refresh = false) {
        $licenseKeyInfo = $this->getLicenseKeyInfo($refresh);
        if (isset($licenseKeyInfo['dateExpires'])) {
            return $licenseKeyInfo['dateExpires'];
        } else if (isset($licenseKeyInfo['errors']) && $licenseKeyInfo['errors'] === 'invalid') {
            return 'invalid';
        }
    }

    public function getMaxUsers($refresh = false) {
        $licenseKeyInfo = $this->getLicenseKeyInfo($refresh);
        if (isset($licenseKeyInfo['maxUsers'])) {
            return $licenseKeyInfo['maxUsers'];
        }
    }

    /**
     * @return array|null expiration date for app product key
     */
    private $_licenseKeyInfo;

    public function getLicenseKeyInfo($refresh = false) {
        // two-layer caching
        if (!isset($this->_licenseKeyInfo) || $refresh) {
            $cacheKey = $this->getLicenseKeyInfoCacheKey();
            $licenseKeyInfo = Yii::app()->cache2->get($cacheKey);

            if (is_array($licenseKeyInfo)) {
                $this->_licenseKeyInfo = $licenseKeyInfo;
            } else {
                $url = Yii::app()->getUpdateServer() . '/installs/registry/getLicenseKeyInfo';
                $this->_licenseKeyInfo = array();
                if ($licenseKeyInfo = RequestUtil::request(array(
                            'url' => $url,
                            'method' => 'POST',
                            'content' => array(
                                'unique_id' => $this->unique_id,
                            )
                        ))) {
                    $tryJson = json_decode($licenseKeyInfo, 1);
                    if (isset($tryJson['errors'])) {
                        if ($tryJson['errors'] === 'invalid') {
                            $this->_licenseKeyInfo = array(
                                'errors' => 'invalid',
                            );
                        }
                    } elseif (isset($tryJson['dateExpires']) && isset($tryJson['maxUsers'])) {
                        $this->_licenseKeyInfo = array(
                            'dateExpires' => $tryJson['dateExpires'],
                            'maxUsers' => $tryJson['maxUsers'],
                            'hubEnabled' => (isset($tryJson['hubEnabled']) ? $tryJson['hubEnabled'] : false),
                        );
                    }
                }
                Yii::app()->cache2->set($cacheKey, $this->_licenseKeyInfo, 60 * 15);
            }
        }
        return $this->_licenseKeyInfo;
    }

    private function getLicenseKeyInfoCacheKey() {
        return 'Admin::getLicenseKeyInfoCacheKey';
    }

}
