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




Yii::import('application.components.behaviors.LinkableBehavior');
Yii::import('application.modules.users.models.*');
Yii::import('application.components.behaviors.NormalizedJSONFieldsBehavior');
Yii::import('application.components.behaviors.WidgetLayoutJSONFieldsBehavior');
Yii::import('application.components.behaviors.SmartSearchModelBehavior');
Yii::import('application.components.sortableWidget.SortableWidget');

/**
 * This is the model class for table "x2_profile".
 * @package application.models
 */
class Profile extends X2ActiveRecord {

    /**
     * username of guest profile record 
     */
    const GUEST_PROFILE_USERNAME = '__x2_guest_profile__';

    private $_isActive;

    public $photo; // used for avatar upload

    /**
     * @var string Used in the search scenario to uniquely identify this model. Allows filters
     *  to be saved for each grid view.
     */
    public $uid;

    /**
     * @var bool If true, grid views displaying models of this type will have their filter and
     *  sort settings saved in the database instead of in the session
     */
    public $dbPersistentGridSettings = false;

    public function __construct(
        $scenario = 'insert', $uid = null, $dbPersistentGridSettings = false){

        if ($uid !== null) {
            $this->uid = $uid;
        }
        $this->dbPersistentGridSettings = $dbPersistentGridSettings;
        parent::__construct ($scenario);
    }


    public function getName () {
        return $this->fullName;
    }


    public function setIsActive ($isActive) {
        $this->_isActive = $isActive;
    }

    public function getIsActive () {
        if (isset ($this->_isActive)) {
            return $this->_isActive;
        } else {
            return null;
        }
    }

    /**
     * Returns the static model of the specified AR class.
     * @return Profile the static model class
     */
    public static function model($className = __CLASS__){
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName(){
        return 'x2_profile';
    }


    public function getLanguageOptions () {
        $languageDirs = scandir('./protected/messages'); // scan for installed language folders
        if(is_dir('./custom/protected/messages')){
            $languageDirs += scandir('./custom/protected/messages');
        }
        sort($languageDirs);
        $languages = array('en' => 'English');

        foreach ($languageDirs as $code) {  // look for langauges name
            $name = $this->getLanguageName($code, $languageDirs);  // in each item in $languageDirs
            if ($name !== false)
                $languages[$code] = $name; // add to $languages if name is found
        }
        return $languages;
    }

    /**
     * Obtain the name of the language given its 2-5 letter code.
     *
     * If a language pack was found for the language code, return its full
     * name. Otherwise, return false.
     *
     * @param string $code
     * @param array $languageDirs
     * @return mixed
     */
    public function getLanguageName($code, $languageDirs) { // lookup language name for the language code provided
        if (in_array($code, $languageDirs)) { // is the language pack here?
            if(file_exists("custom/protected/messages/$code/app.php")){
                $appMessageFile = "custom/protected/messages/$code/app.php";
            }else{
                $appMessageFile = "protected/messages/$code/app.php";
            }
            if (file_exists($appMessageFile)) { // attempt to load 'app' messages in
                $appMessages = include($appMessageFile);     // the chosen language
                if (is_array($appMessages) and isset($appMessages['languageName']) && $appMessages['languageName'] != 'Template')
                    return $appMessages['languageName'];       // return language name
            }
        }
        return false; // false if languge pack wasn't there
    }

    public function behaviors(){
        Yii::import ('application.components.behaviors.ActiveRecordBehavior');
        Yii::import ('application.components.behaviors.FileFieldBehavior');
        // Skip loading theme settins if this request isn't associated with a session, eg API
        $theme = (Yii::app()->params->noSession ? array() :
            ThemeGenerator::getProfileKeys(true, true, false));

        $that = $this;
        return array(
            'StaticFieldsBehavior' => array(
                'class' => 'application.components.behaviors.StaticFieldsBehavior',
                'translationCategory' => 'profile',
                'fields' => array (
                    array (
                        'fieldName' => 'fullName',
                        'attributeLabel' => 'Full Name',
                        'type' => 'varchar',
                    ),
                    array (
                        'fieldName' => 'tagLine',
                        'attributeLabel' => 'Tag Line',
                        'type' => 'varchar',
                    ),
                    array (
                        'fieldName' => 'username',
                        'attributeLabel' => 'Username',
                        'type' => 'varchar',
                    ),
                    array (
                        'fieldName' => 'officePhone',
                        'attributeLabel' => 'Office Phone',
                        'type' => 'phone',
                    ),
                    array (
                        'fieldName' => 'cellPhone',
                        'attributeLabel' => 'Cell Phone',
                        'type' => 'phone',
                    ),
                    array (
                        'fieldName' => 'emailAddress',
                        'attributeLabel' => 'Email Address',
                        'type' => 'email',
                    ),
                    array (
                        'fieldName' => 'language',
                        'attributeLabel' => 'Language',
                        'type' => 'dropdown',
                        'includeEmpty' => false,
                        'linkType' => function () use ($that) {
                            return $that->getLanguageOptions ();
                        },
                    ),
                    array (
                        'fieldName' => 'googleId',
                        'attributeLabel' => 'Google ID',
                        'type' => 'email',
                    ),
                ),
            ),
            'FileFieldBehavior' => array(
                'class' => 'application.components.behaviors.FileFieldBehavior',
                'attribute' => 'avatar',
                'fileAttribute' => 'photo',
                'fileType' => FileFieldBehavior::IMAGE,
                'getFilename' => function (CUploadedFile $file) {
                    $time = time();
                    $rand = chr(rand(65, 90));
                    $salt = $time . $rand;
                    $name = md5($salt . md5($salt) . $salt);
                    return 'uploads/protected/'.$name.'.'.$file->getExtensionName ();
                }
            ),
            'LinkableBehavior' => array(
                'class' => 'LinkableBehavior',
                'baseRoute' => '/profile',
                'autoCompleteSource' => null,
                'module' => 'profile'
            ),
            'ERememberFiltersBehavior' => array(
                'class' => 'application.components.behaviors.ERememberFiltersBehavior',
                'defaults' => array(),
                'defaultStickOnClear' => false
            ),
            'NormalizedJSONFieldsBehavior' => array(
                'class' => 'application.components.behaviors.NormalizedJSONFieldsBehavior',
                'transformAttributes' => array(
                    'theme' => array_merge($theme, array(
                        'backgroundColor', 'menuBgColor', 'menuTextColor', 'pageHeaderBgColor',
                        'pageHeaderTextColor', 'activityFeedWidgetBgColor',
                        'activityFeedWidgetTextColor', 'backgroundImg', 'backgroundTiling',
                        'pageOpacity', 'themeName', 'private', 'owner', 'loginSound',
                        'notificationSound', 'gridViewRowColorOdd', 'gridViewRowColorEven',
                        'enableLoginBgImage')),
                ),
            ),
            'JSONFieldsDefaultValuesBehavior' => array(
                'class' => 'application.components.behaviors.JSONFieldsDefaultValuesBehavior',
                'transformAttributes' => array(
                    'miscLayoutSettings' => array(
                        'themeSectionExpanded'=>true, // preferences theme sub section
                        'unhideTagsSectionExpanded'=>true, // preferences tag sub section
                        'x2flowShowLabels'=>true, // flow node labels
                        'profileInfoIsMinimized'=>false, // profile page profile info section
                        // 'fullProfileInfo'=>false, // profile page profile info section
                        'perStageWorkflowView'=>true, // selected workflow view interface
                        'columnWidth'=>50, // selected workflow view interface
                        'recordViewColumnWidth'=>65, 
                        'enableTransactionalView'=>false, 
                        'enableJournalView'=>true, 
                        'viewModeActionSubmenuOpen'=>true, 
                    ),
                ),
                'maintainCurrentFieldsOrder' => true
            ),
            'SmartSearchModelBehavior' => array (
                'class' => 'application.components.behaviors.SmartSearchModelBehavior',
            )
        );
    }

    /**
     * Save default layouts 
     */
    public function afterSave () {
        parent::afterSave ();
        foreach ($this->_widgetLayouts as $name => $settings) {
            if ($settings) $settings->save ();
        }
    }

    public function getProfileWidgetLayout () {
        return $this->getWidgetLayout ('ProfileWidgetLayout')->settings->attributes;
    }

    public function setProfileWidgetLayout ($layout) {
        $this->getWidgetLayout ('ProfileWidgetLayout')->settings->attributes = $layout;
    }

    public function getTopicsWidgetLayout () {
        return $this->getWidgetLayout ('TopicsWidgetLayout')->settings->attributes;
    }

    public function setTopicsWidgetLayout ($layout) {
        $this->getWidgetLayout ('TopicsWidgetLayout')->settings->attributes = $layout;
    }

     
    public function getDataWidgetLayout () {
        return $this->getWidgetLayout ('DataWidgetLayout')->settings->attributes;
    }

    public function setDataWidgetLayout ($layout) {
        $this->getWidgetLayout ('DataWidgetLayout')->settings->attributes = $layout;
    }
     

    public function getRecordViewWidgetLayout () {
        return $this->getWidgetLayout ('RecordViewWidgetLayout')->settings->attributes;
    }

    public function setRecordViewWidgetLayout ($layout) {
        $this->getWidgetLayout ('RecordViewWidgetLayout')->settings->attributes = $layout;
    }


    /**
     * @return array validation rules for model attributes.
     */
    public function rules(){
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('fullName, username, status', 'required'),
            array('status, lastUpdated, disableNotifPopup, allowPost, defaultCalendar', 'numerical', 'integerOnly' => true),
            array('enableFullWidth,showSocialMedia,showDetailView,disablePhoneLinks,disableTimeInTitle,showTours', 'boolean'), //,showWorkflow
            array('emailUseSignature', 'length', 'max' => 10),
            array('startPage', 'length', 'max' => 30),
            array('googleId', 'unique'),
            array('isActive', 'numerical'),
            array('fullName', 'length', 'max' => 60),
            array('username, updatedBy', 'length', 'max' => 20),
            array('officePhone, extension, cellPhone, language', 'length', 'max' => 40),
            array('timeZone', 'length', 'max' => 100),
            array('widgets, tagLine, emailAddress', 'length', 'max' => 255),
            array('widgetOrder', 'length', 'max' => 512),
            array('emailSignature', 'safe'),
            array('notes, avatar, gridviewSettings, formSettings, widgetSettings', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('id, fullName, username, officePhone, extension, cellPhone, emailAddress, lastUpdated, language', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations(){
        return array(
            'user' => array(self::HAS_ONE, 'User', array ('username' => 'username'))
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels(){
        return array(
            'id' => Yii::t('profile', 'ID'),
            'fullName' => Yii::t('profile', 'Full Name'),
            'username' => Yii::t('profile', 'Username'),
            'officePhone' => Yii::t('profile', 'Office Phone'),
            'extension' => Yii::t('profile','Extension'),
            'cellPhone' => Yii::t('profile', 'Cell Phone'),
            'emailAddress' => Yii::t('profile', 'Email Address'),
            'notes' => Yii::t('profile', 'Notes'),
            'status' => Yii::t('profile', 'Status'),
            'tagLine' => Yii::t('profile', 'Tag Line'),
            'lastUpdated' => Yii::t('profile', 'Last Updated'),
            'updatedBy' => Yii::t('profile', 'Updated By'),
            'avatar' => Yii::t('profile', 'Avatar'),
            'allowPost' => Yii::t('profile', 'Allow users to post on your profile?'),
            'disablePhoneLinks' => Yii::t('profile', 'Disable phone field links?'),
            'disableTimeInTitle' => Yii::t('profile','Disable timer display in page title?'),
            'disableNotifPopup' => Yii::t('profile', 'Disable notifications pop-up?'),
            'language' => Yii::t('profile', 'Language'),
            'timeZone' => Yii::t('profile', 'Time Zone'),
            'widgets' => Yii::t('profile', 'Widgets'),
            // 'groupChat'=>Yii::t('profile','Enable group chat?'),
            'widgetOrder' => Yii::t('profile', 'Widget Order'),
            'widgetSettings' => Yii::t('profile', 'Widget Settings'),
            'resultsPerPage' => Yii::t('profile', 'Results Per Page'),
            /* 'menuTextColor' => Yii::t('profile', 'Menu Text Color'),
              'menuBgColor' => Yii::t('profile', 'Menu Color'),
              'menuTextColor' => Yii::t('profile', 'Menu Text Color'),
              'pageHeaderBgColor' => Yii::t('profile', 'Page Header Color'),
              'pageHeaderTextColor' => Yii::t('profile', 'Page Header Text Color'),
              'activityFeedWidgetBgColor' => Yii::t('profile', 'Activity Feed Widget Background Color'),
              'backgroundColor' => Yii::t('profile', 'Background Color'),
              'backgroundTiling' => Yii::t('profile', 'Background Tiling'),
              'pageOpacity' => Yii::t('profile', 'Page Opacity'), */
            'startPage' => Yii::t('profile', 'Start Page'),
            'showSocialMedia' => Yii::t('profile', 'Show Social Media'),
            'showDetailView' => Yii::t('profile', 'Show Detail View'),
            // 'showWorkflow'=>Yii::t('profile','Show Workflow'),
            'gridviewSettings' => Yii::t('profile', 'Gridview Settings'),
            'formSettings' => Yii::t('profile', 'Form Settings'),
            'emailUseSignature' => Yii::t('profile', 'Email Signature'),
            'emailSignature' => Yii::t('profile', 'My Signature'),
            'enableFullWidth' => Yii::t('profile', 'Enable Full Width Layout'),
            'googleId' => Yii::t('profile', 'Google ID'),
            'address' => Yii::t('profile', 'Address'),
            'enableTwoFactor' => Yii::t('profile', 'Enable Two Factor Authentication'),
        );
    }

    /**
     * Masks method in SmartSearchModelBehavior. Enables sorting by lastLogin and isActive.
     */
    public function getSort () {
        $attributes = array();
        foreach($this->owner->attributes as $name => $val) {
            $attributes[$name] = array(
                'asc' => 't.'.$name.' ASC',
                'desc' => 't.'.$name.' DESC',
            );
        }
        $attributes['lastLogin'] = array (
            'asc' => '(SELECT lastLogin from x2_users '.
                'WHERE x2_users.username=t.username) ASC',
            'desc' => '(SELECT lastLogin from x2_users '.
                'WHERE x2_users.username=t.username) DESC',
        );
        $attributes['isActive'] = array (
            'asc' => 
                '(SELECT DISTINCT user '.
                    'FROM x2_sessions '.
                    'WHERE t.username=x2_sessions.user AND '.
                        'x2_sessions.lastUpdated > '.(time () - 900).
                ') DESC ',
            'desc' => 
                '(SELECT DISTINCT user '.
                    'FROM x2_sessions '.
                    'WHERE t.username=x2_sessions.user AND '.
                        'x2_sessions.lastUpdated > '.(time () - 900).
                ') ASC',
        );
        return $attributes;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search($resultsPerPage=null, $uniqueId=null, $excludeAPI=false){
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->distinct = true;
        $criteria->compare('id', $this->id);
        $criteria->compare('fullName', $this->fullName, true);
        $criteria->compare('username', $this->username, true);
        $criteria->compare('username', '<>'.self::GUEST_PROFILE_USERNAME, true);
        $criteria->compare('officePhone', $this->officePhone, true);
        $criteria->compare('cellPhone', $this->cellPhone, true);
        $criteria->compare('emailAddress', $this->emailAddress, true);
        $criteria->compare('status', $this->status);
        $criteria->compare('tagLine',$this->tagLine,true);

        // Filter on is active model property
        if (!isset ($this->isActive)) { // invalid isActive value
        } else if ($this->isActive) { // select all users with new session records
            $criteria->join = 
                'JOIN x2_sessions ON x2_sessions.user=username and '.
                'x2_sessions.lastUpdated > "'.(time () - 900).'"';
        } else { // select all users with old session records or no session records
            $criteria->join = 
                'JOIN x2_sessions ON (x2_sessions.user=username and '.
                'x2_sessions.lastUpdated <= "'.(time () - 900).'") OR '.
                'username not in (select x2_sessions.user from x2_sessions as x2_sessions)';
        }

        if ($excludeAPI) {
            if ($criteria->condition !== '') {
                $criteria->condition .= ' AND username!=\'API\'';
            } else { 
                $criteria->condition = 'username!=\'API\'';
            }
        }

        return $this->smartSearch ($criteria, $resultsPerPage);
    }

    /**
     * Sets a miscLayoutSetting JSON property to the specified value
     *
     * @param string $settingName The name of the JSON property
     * @param string $settingValue The value that the JSON property will bet set to
     */
    public static function setMiscLayoutSetting (
        $settingName, $settingValue, $suppressEcho=false) {

        $model = Profile::model ()->findByPk (Yii::app()->user->getId());
        $settings = $model->miscLayoutSettings;
        if (!in_array ($settingName, array_keys ($settings))) {
            echo 'failure';
            return;
        }
        $settings[$settingName] = $settingValue;
        $model->miscLayoutSettings = $settings;
        $echoVal = '';
        if (!$model->save ()) {
            //AuxLib::debugLog ('Error: setMiscLayoutSetting: failed to save model');
            $echoVal = 'failure';
        } else {
            $echoVal = 'success';
        }

        if (!$suppressEcho) echo $echoVal;
    }

    public static function setDetailView($value){
        $model = Profile::model()->findByPk(Yii::app()->user->getId()); // set user's preference for contact detail view
        $model->showDetailView = ($value == 1) ? 1 : 0;
        $model->upadte(array('showDetailView'));
    }

    public static function getDetailView(){
        $model = Profile::model()->findByPk(Yii::app()->user->getId()); // get user's preference for contact detail view
        return $model->showDetailView;
    }

    // public static function getSocialMedia() {
    // $model = Profile::model()->findByPk(Yii::app()->user->getId());    // get user's preference for contact social media info
    // return $model->showSocialMedia;
    // }


    public function getAttribute($name, $renderFlag = false, $makeLinks = false) {
        if ($name === 'signature') {
            return $this->getSignature ($renderFlag);
        } else {
            return parent::getAttribute ($name);
        }
    }

    public function getSignature($html = false){
        $adminRule = Yii::app()->settings->emailUseSignature;
        $userRule = $this->emailUseSignature;
        $signature = '';

        switch($adminRule){
            case 'admin': $signature = Yii::app()->settings->emailSignature;
                break;
            case 'user':
                switch($userRule){
                    case 'user': $signature = $signature = $this->emailSignature;
                        break;
                    case 'admin': Yii::app()->settings->emailSignature;
                        break;
                    case 'group': $signature == '';
                        break;
                    default: $signature == '';
                }
                break;
            case 'group': $signature == '';
                break;
            default: $signature == '';
        }


        $signature = preg_replace(
                array(
            '/\{first\}/',
            '/\{last\}/',
            '/\{phone\}/',
            '/\{group\}/',
            '/\{email\}/',
                ), array(
            $this->user->firstName,
            $this->user->lastName,
            $this->officePhone,
            '',
            $html ? CHtml::mailto($this->emailAddress) : $this->emailAddress,
                ), $signature
        );
        if($html){
            $signature = Formatter::convertLineBreaks($signature);
        }

        return $signature;
    }

    public static function getResultsPerPage(){
        if(!Yii::app()->user->isGuest)
            $resultsPerPage = Yii::app()->params->profile->resultsPerPage;
        // $model = Profile::model()->findByPk(Yii::app()->user->getId());    // get user's preferred results per page
        // $resultsPerPage = $model->resultsPerPage;

        return empty($resultsPerPage) ? 15 : $resultsPerPage;
    }

    public static function getPossibleResultsPerPage(){
        return array(
            10 => Yii::t('app', '{n} rows', array('{n}' => '10')),
            20 => Yii::t('app', '{n} rows', array('{n}' => '20')),
            30 => Yii::t('app', '{n} rows', array('{n}' => '30')),
            40 => Yii::t('app', '{n} rows', array('{n}' => '40')),
            50 => Yii::t('app', '{n} rows', array('{n}' => '50')),
            75 => Yii::t('app', '{n} rows', array('{n}' => '75')),
            100 => Yii::t('app', '{n} rows', array('{n}' => '100')),
        );
    }

    // lookup user's settings for a gridview (visible columns, column widths)
    public static function getGridviewSettings($gvSettingsName = null){
        if(!Yii::app()->user->isGuest)
            // converts JSON string to assoc. array
            $gvSettings = json_decode(Yii::app()->params->profile->gridviewSettings, true); 
        if(isset($gvSettingsName)){
            $gvSettingsName = strtolower($gvSettingsName);
            if(isset($gvSettings[$gvSettingsName]))
                return $gvSettings[$gvSettingsName];
            else
                return null;
        } elseif(isset($gvSettings)){
            return $gvSettings;
        }else{
            return null;
        }
    }

    // add/update settings for a specific gridview, or save all at once
    public static function setGridviewSettings($gvSettings, $gvSettingsName = null){
        if(!Yii::app()->user->isGuest){
            if(isset($gvSettingsName)){
                $fullGvSettings = Profile::getGridviewSettings();
                $fullGvSettings[strtolower($gvSettingsName)] = $gvSettings;
                // encode array in JSON
                Yii::app()->params->profile->gridviewSettings = json_encode($fullGvSettings); 
            }else{
                // encode array in JSON
                Yii::app()->params->profile->gridviewSettings = json_encode($gvSettings); 
            }
            return Yii::app()->params->profile->update(array('gridviewSettings'));
        }else{
            return null;
        }
    }

    // lookup user's settings for a gridview (visible columns, column widths)
    public static function getFormSettings($formName = null){
        if(!Yii::app()->user->isGuest){
            $formSettings = json_decode(Yii::app()->params->profile->formSettings, true); // converts JSON string to assoc. array
            if($formSettings == null)
                $formSettings = array();
            if(isset($formName)){
                $formName = strtolower($formName);
                if(isset($formSettings[$formName]))
                    return $formSettings[$formName];
                else
                    return array();
            } else{
                return $formSettings;
            }
        }else{
            return array();
        }
    }

    // add/update settings for a specific form, or save all at once
    public static function setFormSettings($formSettings, $formName = null){
        if(isset($formName)){
            $fullFormSettings = Profile::getFormSettings();
            $fullFormSettings[strtolower($formName)] = $formSettings;
            Yii::app()->params->profile->formSettings = json_encode($fullFormSettings); // encode array in JSON
        }else{
            Yii::app()->params->profile->formSettings = json_encode($formSettings); // encode array in JSON
        }
        return Yii::app()->params->profile->update(array('formSettings'));
    }

    public static function getWidgets(){

        if(Yii::app()->user->isGuest) // no widgets if the user isn't logged in
            return array();
        // $model = Profile::model('Profile')->findByPk(Yii::app()->user->getId());
        $model = Yii::app()->params->profile;
        if(!isset($model)){
            $model = Profile::model()->findByPk(Yii::app()->user->getId());
        }

        $registeredWidgets = array_keys(Yii::app()->params->registeredWidgets);

        $widgetNames = ($model->widgetOrder == '') ? array() : explode(":", $model->widgetOrder);
        $visibility = ($model->widgets == '') ? array() : explode(":", $model->widgets);
        $widgetList = array();
        $updateRecord = false;

        for($i = 0; $i < count($widgetNames); $i++){

            if(!in_array($widgetNames[$i], $registeredWidgets)){ // check the main cfg file
                unset($widgetNames[$i]);       // if widget isn't listed,
                unset($visibility[$i]);        // remove it from database fields
                $updateRecord = true;
            }else{
                $widgetList[$widgetNames[$i]] = array(
                    'id' => 'widget_'.$widgetNames[$i], 
                    'visibility' => isset ($visibility[$i]) ? $visibility[$i] : 1,
                    'params' => array());
            }
        }

        foreach($registeredWidgets as $class){   // check list of widgets in main cfg file
            if(!in_array($class, array_keys($widgetList))){        // if they aren't in the list,
                $widgetList[$class] = array(
                    'id' => 'widget_'.$class, 'visibility' => 1,
                    'params' => array()); // add them at the bottom

                $widgetNames[] = $class; // add new widgets to widgetOrder array
                $visibility[] = 1;   // and visibility array
                $updateRecord = true;
            }
        }

        if($updateRecord){
            $model->widgetOrder = implode(':', $widgetNames); // update database fields
            $model->widgets = implode(':', $visibility);   // if there are new widgets
            $model->update(array('widgetOrder', 'widgets'));
        }

        return $widgetList;
    }

    public static function getWidgetSettings(){
        if(Yii::app()->user->isGuest) // no widgets if the user isn't logged in
            return array();

        // if widget settings haven't been set, give them default values
        if(Yii::app()->params->profile->widgetSettings == null){
            $widgetSettings = self::getDefaultWidgetSettings();

            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update(array('widgetSettings'));
        }

        $widgetSettings = json_decode(Yii::app()->params->profile->widgetSettings);

        if(!isset($widgetSettings->MediaBox)){
            $widgetSettings->MediaBox = array('mediaBoxHeight' => 150, 'hideUsers' => array());
            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update(array('widgetSettings'));
        }

        return json_decode(Yii::app()->params->profile->widgetSettings);
    }

    /**
    * get an array of default widget values
    * @return Array of default values for widgets
    *
    **/
    public static function getDefaultWidgetSettings(){
        return  array(
                'ChatBox' => array(
                    'chatboxHeight' => 300,
                    'chatmessageHeight' => 50,
                ),
                'NoteBox' => array(
                    'noteboxHeight' => 200,
                    'notemessageHeight' => 50,
                ),
                'DocViewer' => array(
                    'docboxHeight' => 200,
                ),
                'TopSites' => array(
                    'topsitesHeight' => 200,
                    'urltitleHeight' => 10,
                ),
                'MediaBox' => array(
                    'mediaBoxHeight' => 150,
                    'hideUsers' => array(),
                ),
                'TimeZone' => array(
                    'clockType' => 'analog'
                ),
                'SmallCalendar' => array(
                    'justMe' => 'false'
                ),
                'FilterControls' => array(
                    'order' => array()
                )
            );
    }

    /**
    * Method to change a specific value in a widgets settings
    * @param string    $widget Name of widget
    * @param string    $setting Name of setting within the widget
    * @param variable  $value to insert into the setting  
    * @return boolean  false if profile did not exist
    */
    public static function changeWidgetSetting($widget, $setting, $value){
        $profile = Yii::app()->params->profile;
        if(isset($profile)){
            $widgetSettings = self::getWidgetSettings();

            if(!isset($widgetSettings->$widget))
                self::getWidgetSetting($widget);


            $widgetSettings->$widget->$setting = $value;
            
            Yii::app()->params->profile->widgetSettings = CJSON::encode($widgetSettings);
            Yii::app()->params->profile->update(array('widgetSettings'));
            return true;
        }

        return false;
    }

    /**
    * Safely retrieves the settings of a widget, and pulls from the default if the setting does not exist
    * @param string $widget The settings to return.
    * @param string $setting Optional. 
    * @return Object widget settings object
    * @return String widget settings string (if $setting is set)
    */
    public static function getWidgetSetting($widget, $setting=null){
        $widgetSettings = self::getWidgetSettings();

        // Check if the widget setting exists
        $defaultSettings = self::getDefaultWidgetSettings();
        if(!isset($widgetSettings->$widget)){
            $widgetSettings->$widget = $defaultSettings[$widget];
            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update(array('widgetSettings'));
            $widgetSettings = self::getWidgetSettings();

        // Check if the setting exists
        } else if( isset($setting) && !isset($widgetSettings->$widget->$setting)){
            $widgetSettings->$widget->$setting = $defaultSettings[$widget][$setting];
            Yii::app()->params->profile->widgetSettings = json_encode($widgetSettings);
            Yii::app()->params->profile->update(array('widgetSettings'));
            $widgetSettings = self::getWidgetSettings();
        }

        if( !isset($setting) )
            return $widgetSettings->$widget;
        else
            return $widgetSettings->$widget->$setting;
    }

    public function getLink(){

        $noSession = Yii::app()->params->noSession;
        if(!$noSession){
            if($this->id == Yii::app()->user->id)
                return CHtml::link(Yii::t('app', 'your feed'), array($this->baseRoute.'/'.$this->id));
            else
                return CHtml::link(Yii::t('app', '{name}\'s feed', array('{name}' => $this->fullName)), array($this->baseRoute.'/'.$this->id));
        } else{
            return CHtml::link($this->fullName, Yii::app()->absoluteBaseUrl.'/index.php'.$this->baseRoute.'/'.$this->id);
        }
    }

    /**
     * Initializes widget layout. The layout is a set of associative arrays with the following 
     * format:
     * array (
     * 'left'=> array()
     *  'content' => array(
     *    'widget1'=> array(
     *      'name' => 'widget name',
     *    )
     *  )
     * 'right' => array()
     * )
     *
     * The layout should be json encoded and saved in profile layout property.
     *
     * @return array
     */
    function initLayout(){
        $layout = array(
            'left' => array(
                'ProfileInfo' => array(
                    'title' => 'Profile Info',
                    'minimize' => false,
                ),
                'EmailInboxMenu' => array(
                    'title' => 'Inbox Menu',
                    'minimize' => false,
                ),
                'ActionMenu' => array(
                    'title' => 'Actions',
                    'minimize' => false,
                ),
                'TopContacts' => array(
                    'title' => 'Top Contacts',
                    'minimize' => false,
                ),
                'RecentItems' => array(
                    'title' => 'Recently Viewed',
                    'minimize' => false,
                ),
                'ActionTimer' => array(
                    'title' => 'Action Timer',
                    'minimize' => true,
                ),
                'UserCalendars' => array(
                    'title' => 'User Calendars',
                    'minimize' => false,
                ),
                'CalendarFilter' => array(
                    'title' => 'Filter',
                    'minimize' => false,
                ),
                'GroupCalendars' => array(
                    'title' => 'Group Calendars',
                    'minimize' => false,
                ),
                'FilterControls' => array(
                    'title' => 'Filter Controls',
                    'minimize' => false,
                ),
                'SimpleFilterControlEventTypes' => array(
                    'title' => 'Event Types',
                    'minimize' => false,
                ),
            ),
            'right' => array(
                'SmallCalendar' => array(
                    'title' => 'Small Calendar',
                    'minimize' => false,
                ),
                'ChatBox' => array(
                    'title' => 'Activity Feed',
                    'minimize' => false,
                ),
                'GoogleMaps' => array(
                    'title' => 'Google Map',
                    'minimize' => false,
                ),
                'OnlineUsers' => array(
                    'title' => 'Active Users',
                    'minimize' => false,
                ),
                'TagCloud' => array(
                    'title' => 'Tag Cloud',
                    'minimize' => false,
                ),
                'TimeZone' => array(
                    'title' => 'Clock',
                    'minimize' => false,
                ),
                'SmallCalendar' => array(
                    'title' => 'Calendar',
                    'minimize' => false,
                ),
                'QuickContact' => array(
                    'title' => 'Quick Contact',
                    'minimize' => false,
                ),
                'MediaBox' => array(
                    'title' => 'Files',
                    'minimize' => false,
                ),
            ),
            'hiddenRight' => array(
                'ActionMenu' => array(
                    'title' => 'My Actions',
                    'minimize' => false,
                ),
                'MessageBox' => array(
                    'title' => 'Message Board',
                    'minimize' => false,
                ),
                'NoteBox' => array(
                    'title' => 'Note Pad',
                    'minimize' => false,
                ),
                'DocViewer' => array(
                    'title' => 'Doc Viewer',
                    'minimize' => false,
                ),
                'TopSites' => array(
                    'title' => 'Top Sites',
                    'minimize' => false,
                ),
            ),
        );
        if(Yii::app()->contEd('pro')){
            if(file_exists('protected/config/proWidgets.php')){
                foreach(include('protected/config/proWidgets.php') as $loc=>$data){
                    if (isset ($layout[$loc]))
                        $layout[$loc] = array_merge($layout[$loc],$data);
                }
            }
        }
        return $layout;
    }


    /**
     * Private helper function to update users layout elements to match the set of layout
     * elements specified in initLayout ().
     */
    private function addRemoveLayoutElements($position, &$layout, $initLayout){
        $changed = false;
        if (!isset ($layout[$position])) {
            $changed = true;
            $layout[$position] = array ();
        }
        if (!isset ($layout['hiddenRight'])) {
            $changed = true;
            $layout['hiddenRight'] = array ();
        }

        if ($position === 'right') {
            $initLayoutWidgets = array_merge($initLayout[$position], $initLayout['hiddenRight']);
            $layoutWidgets = array_merge($layout[$position], $layout['hiddenRight']);
            $initLayoutWidgetsHidden = $initLayout['hiddenRight'];
            $hiddenPostion = 'hiddenRight';
        } else {
            $initLayoutWidgets = $initLayout[$position];
            $initLayoutWidgetsHidden = array ();
            $hiddenPostion = $position;
            $layoutWidgets = $layout[$position];
        }

        // add new widgets
        $arrayDiff =
                array_diff(array_keys($initLayoutWidgets), array_keys($layoutWidgets));

        foreach($arrayDiff as $elem){
            if (isset ($initLayoutWidgetsHidden[$elem])) {
                $insertAt = $hiddenPostion;
            } else {
                $insertAt = $position;
            }
            // unshift key-value pair
            $layout[$insertAt] = array(
                $elem => $initLayoutWidgets[$elem]) + $layout[$insertAt];
            $changed = true;
        }

        // remove obsolete widgets
        $arrayDiff =
                array_diff(array_keys($layoutWidgets), array_keys($initLayoutWidgets));
        foreach($arrayDiff as $elem){
            if(in_array ($elem, array_keys ($layout[$position]))) {
                unset($layout[$position][$elem]);
                $changed = true;
            } else if($position === 'right' && 
                in_array ($elem, array_keys ($layout['hiddenRight']))) {

                unset($layout['hiddenRight'][$elem]);
                $changed = true;
            }
        }

        // ensure that widget properties are the same as those in the default layout
        foreach($layout[$position] as $name=>$arr){
            if (in_array ($name, array_keys ($initLayoutWidgets)) &&
                $initLayoutWidgets[$name]['title'] !== $arr['title']) {

                $layout[$position][$name]['title'] = $initLayoutWidgets[$name]['title'];
                $changed = true;
            }
        }

        if ($position === 'right') {
            foreach($layout['hiddenRight'] as $name=>$arr){
                if (in_array ($name, array_keys ($initLayoutWidgets)) &&
                    $initLayoutWidgets[$name]['title'] !== $arr['title']) {

                    $layout['hiddenRight'][$name]['title'] = 
                        $initLayoutWidgets[$name]['title'];
                    $changed = true;
                }
            }
        }

        if($changed){
            $this->layout = json_encode($layout);
            $this->update(array('layout'));
        }
    }

    /**
     * Returns the layout for the user's widgets as an associative array.
     *
     * @return array
     */
    public function getLayout(){
        $layout = $this->getAttribute('layout');

        $initLayout = $this->initLayout();

        if(!$layout){ // layout hasn't been initialized?
            $layout = $initLayout;
            $this->layout = json_encode($layout);
            $this->update(array('layout'));
        }else{
            $layout = json_decode($layout, true); // json to associative array
            if (!is_array ($layout)) $layout = array ();

            $this->addRemoveLayoutElements('left', $layout, $initLayout);
            $this->addRemoveLayoutElements('right', $layout, $initLayout);
        }

        return $layout;
    }

    public function getHiddenProfileWidgetMenu () {
        $profileWidgetLayout = $this->profileWidgetLayout;

        $hiddenProfileWidgetsMenu = '';
        $hiddenProfile = false;
        $hiddenWidgets = array ();
        foreach($profileWidgetLayout as $name => $widgetSettings){
            $hidden = $widgetSettings['hidden'];
            $softDeleted = $widgetSettings['softDeleted'];
            if ($hidden && !$softDeleted) {
                $hiddenWidgets[$name] = Yii::t('app',$widgetSettings['label']);
                $hiddenProfile = true;
            }
        }
        $hiddenWidgets = ArrayUtil::asorti ($hiddenWidgets);
        foreach ($hiddenWidgets as $name => $label) {
            $hiddenProfileWidgetsMenu .= 
                '<li>
                    <span class="x2-hidden-widgets-menu-item profile-widget" id="'.$name.'">'.
                        CHtml::encode ($label).
                    '</span>
                </li>';
        }
        $menu = '<div id="x2-hidden-profile-widgets-menu-container" style="display:none;">';
        $menu .= '<ul id="x2-hidden-profile-widgets-menu" class="x2-hidden-widgets-menu-section">';
        $menu .= $hiddenProfileWidgetsMenu;
        $menu .= '<li><span class="no-hidden-profile-widgets-text" '.
                 ($hiddenProfile ? 'style="display:none;"' : '').'>'.
                 Yii::t('app', 'No Hidden Widgets').
                 '</span></li>';
        $menu .= '</ul>';
        $menu .= '</div>';
        return $menu;
    }

    /**
     *  Returns an html list of hidden widgets used in the Widget Menu
     */
    public function getWidgetMenu(){
        $layout = $this->getLayout();
        $widgetType = Yii::app()->controller instanceof TopicsController ?
            'topics' : 'recordView';
        $layoutName = $widgetType.'WidgetLayout';
        $recordViewWidgetLayout = $this->$layoutName;

        $hiddenRecordViewWidgetMenu = '';
        foreach ($recordViewWidgetLayout as $widgetClass => $settings) {
            if ($settings['hidden']) {
                $hiddenRecordViewWidgetMenu .=
                    '<li>
                        <span class="x2-hidden-widgets-menu-item '.$widgetType.'-widget" 
                          id="'.$widgetClass.'">'.
                            CHtml::encode ($settings['label']).
                        '</span>
                    </li>';
            }
        }

        // used to determine where section dividers should be placed
        $hiddenCenter = $hiddenRecordViewWidgetMenu !== '';
        $hiddenRight = !empty ($layout['hiddenRight']);

        $menu = '<div id="x2-hidden-widgets-menu">';
        $menu .= '<ul id="x2-hidden-recordView-widgets-menu" 
            class="x2-hidden-widgets-menu-section">';
        $menu .= $hiddenRecordViewWidgetMenu;
        $menu .= '</ul>';
        $menu .= '<ul id="x2-hidden-right-widgets-menu" class="x2-hidden-widgets-menu-section">';
        $menu .= '<li '.(($hiddenCenter && $hiddenRight) ? '' : 'style="display: none;"').
            'class="x2-hidden-widgets-menu-divider"></li>';
        foreach($layout['hiddenRight'] as $name => $widget){
            $menu .= '<li><span class="x2-hidden-widgets-menu-item widget-right" id="'.$name.'">'.
                $widget['title'].'</span></li>';
        }
        $menu .= '</ul>';
        $menu .= '</div>';

        return $menu;
    }

    /**
     * Saves a layout to the user's profile as a json string
     *
     * @param array $layout
     */
    public function saveLayout($layout){
        $this->layout = json_encode($layout);
        $this->update(array('layout'));
    }

    /**
     * Renders the avatar image with max dimension 95x95
     * @param int $id the profile id 
     */
    public static function renderFullSizeAvatar ($id, $dimensionLimit=95) {
        if ($id instanceof Profile) {
            $model = $id;
        } else {
            $model = Profile::model ()->findByPk ($id);
        }
        if (isset($model->avatar) && $model->avatar != '' && !file_exists($model->avatar)
                && strpos($model->avatar, 'uploads') !== false && strpos($model->avatar, 'protected') === false) {
            $path = explode(DIRECTORY_SEPARATOR, $model->avatar);
            $pathUserFolder = explode(DIRECTORY_SEPARATOR, $model->avatar);
            $oldPathIndex = array_search('uploads',$path);
            $oldPathIndexUserFolder = array_search('uploads',$pathUserFolder);
            array_splice($pathUserFolder, $oldPathIndexUserFolder+1, 0, 'protected/media/'.$model->username);
            array_splice($path, $oldPathIndex+1, 0, 'protected');
            $newPath = implode(DIRECTORY_SEPARATOR, $path);
            $newPathUserFolder = implode(DIRECTORY_SEPARATOR, $pathUserFolder);
            if(file_exists($newPath)){
                $model->avatar = $newPath;
                $model->update(array('avatar'));
            } else if(file_exists($newPathUserFolder)){
                $model->avatar = $newPathUserFolder;
                $model->update(array('avatar'));                
            }
        }
        if(isset($model->avatar) && $model->avatar!='' && file_exists($model->avatar)) {
            $imgSize = @getimagesize($model->avatar);
            if(!$imgSize)
                $imgSize = array(45,45);

            $maxDimension = max($imgSize[0],$imgSize[1]);

            $scaleFactor = 1;
            if($maxDimension > $dimensionLimit)
                $scaleFactor = $dimensionLimit / $maxDimension;

            $imgSize[0] = round($imgSize[0] * $scaleFactor);
            $imgSize[1] = round($imgSize[1] * $scaleFactor);
            return Profile::renderAvatarImage($id, $imgSize[0], $imgSize[1], array (
                'class' => 'avatar-image'
            ));
        } else {
            if (Yii::app()->getEdition() == 'opensource') {
                return X2Html::fa ('user', array(
                    'class' => 'avatar-image default-avatar',
                    'style' => "font-size: ${dimensionLimit}px",
                ));
            } else {
                return '<img id="avatar-image" width="'
                . $dimensionLimit . '" height="'.$dimensionLimit
                . '" src='.Yii::app()->request->baseUrl
                . "/themes/x2engine/images/eventIcons/default.png".'>';
            }
        }
    }

    /**
     * Renders the avatar image with max dimension 95x95
     * @param int $id the profile id 
     */
    public static function renderEditableAvatar ($id) {
        $userId = Yii::app()->user->id;

        Yii::app()->controller->renderPartial('editableAvatar',
            array('id' => $id, 'editable' => $id == $userId)
        );
    }
    
    public static function renderAvatarImage($id, $width, $height, array $htmlOptions = array ()){
        $model = Profile::model ()->findByPk ($id);
        if(!empty($model->avatar)){
            $file = Yii::app()->file->set($model->avatar);
            if ($file->exists) {
                return CHtml::tag ('img', X2Html::mergeHtmlOptions (array (
                    'id'=>"avatar-image",
                    'class'=>"avatar-upload", 
                    'width'=>$width, 
                    'height'=>$height,
                    'src'=>"data:image/x-icon;base64,".base64_encode($file->getContents()),
                ), $htmlOptions));
            }
        }
    }

    public function getLastLogin () {
        return $this->user['lastLogin'];
    }

    /**
     * Request a two factor authentication code to be sent to the user's cell phone
     * @param bool $init Initialize two factor auth, Profile is not required to have two factor enabled
     * @return bool Whether the code was successfully sent
     */
    public function requestTwoFA($init = false) {
        $settings = Yii::app()->settings;
        if ($settings->twoFactorCredentialsId && ($this->enableTwoFactor || $init)) {
            $creds = Credentials::model()->findByPk($settings->twoFactorCredentialsId);
            if ($creds && $creds->auth) {
                if (get_class($creds->auth) === 'X2HubConnector' && $creds->auth->hubEnabled && $creds->auth->enableTwoFactor) {
                    $sent = false;
                    $hub = Yii::app()->controller->attachBehavior('HubConnectionBehavior', new HubConnectionBehavior);
                    $code = $hub->requestTwoFA($this);
                    if ($code) $sent = true;
                } else if (get_class($creds->auth) === 'TwilioAccount') {
                    // Trim hex to at most 12 digits to account for conversion to dec without exponents
                    $rand = hexdec(substr(bin2hex(openssl_random_pseudo_bytes(12)), 0, 12));
                    $code = sprintf('%06d', substr($rand, 0, 6)); // trim or pad to 6 digits
                    $message = Yii::t('profile', 'Your X2CRM verification code is ').$code;
                    $twilio = Yii::app()->controller->attachBehavior('TwilioBehavior', new TwilioBehavior);
                    $twilio->initialize(array(
                        'sid' => $creds->auth->sid,
                        'token' => $creds->auth->token,
                        'from' => $creds->auth->from,
                    ));
                    $sent = $twilio->sendSMSMessage($this->cellPhone, $message);
                }
                if (isset($code)) {
                    Yii::app()->db->createCommand()
                        ->delete('x2_twofactor_auth', 'userId = :id', array(
                            ':id' => $this->id,
                        ));
                    $inserted = Yii::app()->db->createCommand()
                        ->insert('x2_twofactor_auth', array(
                            'userId' => $this->id,
                            'requested' => time(),
                            'code' => $code,
                        ));
                    return $sent && $inserted;
                }
            }
        }
        return false;
    }

    /**
     * Verify a two factor authentication code
     * @param string $code User submitted verification code
     * @return bool Whether verification code is valid
     */
    public function verifyTwoFACode($code) {
        $verification = Yii::app()->db->createCommand()
            ->select('code')
            ->from('x2_twofactor_auth')
            ->where('userId = :id AND requested >= :requested', array(
                ':id' => $this->id,
                ':requested' => time() - (60 * 5), // within the past 5 minutes
            ))->queryScalar();
        return $code === $verification;
    }
     
    /**
     * Checks for a valid enforced default theme and returns it if it exists. 
     * @return mixed theme array or null if no valid default theme exists
     */
    public function getDefaultTheme () {
        $admin = Yii::app()->settings;
        $theme = Media::model()->findByPk ($admin->defaultTheme);
        if ($theme) {
            $themeDecoded = CJSON::decode ($theme->description);
            if (is_array ($themeDecoded)) {
                /*
                This is a dependency on the internal behavior of NormalizedJSONFieldsBehavior. To eliminate
                this dependency, profile's theme attribute and the media model's description
                attribute should be refactored to use JSONEmbeddedModelBehavior so that they
                share the same JSON structure.
                */
                $behaviors = $this->behaviors ();
                return ArrayUtil::normalizeToArrayR (array_map (function ($a) {
                        return null;
                    }, array_flip (
                        $behaviors['NormalizedJSONFieldsBehavior']['transformAttributes']['theme'])),
                    $themeDecoded);
            }
        } 
        return null;
    }
     

    /**
     * Return theme after checking for an enforced default 
     */
    public function getTheme () {
        $admin = Yii::app()->settings;
         
        if ($admin->enforceDefaultTheme && $admin->defaultTheme !== null) {
            $theme = $this->getDefaultTheme ();
            if ($theme) return $theme;
        } 
         
        return $this->theme;
    }
    
    public function setLoginSound($soundId){
        $this->theme = array_merge($this->theme, array('loginSound'=>$soundId));
    }
    
    public function setNotificationSound($soundId){
        $this->theme = array_merge($this->theme, array('notificationSound'=>$soundId));
    }

    /**
     * Get the default email template for the specified module 
     * @param string $moduleName
     * @return mixed null if the module has no default template, the id of the default template
     *  otherwise
     */
    public function getDefaultEmailTemplate ($moduleName) {
        $defaultEmailTemplates = CJSON::decode ($this->defaultEmailTemplates);
        if (isset ($defaultEmailTemplates[$moduleName])) {
            return $defaultEmailTemplates[$moduleName];
        } else {
            return null;
        }
    }

    /**
     * @return array usernames of users available to receive leads
     */
    public function getUsernamesOfAvailableUsers () {
        return array_map (function ($row) {
            return $row['username'];
        }, Yii::app()->db->createCommand ("
            select username from x2_profile 
            where leadRoutingAvailability=1
        ")->queryAll ());
    }

    
    /**
     * Retrieve email inboxes that this user has selected to have displayed
     * @return array email inbox models indexed by name
     */
    public function getEmailInboxes () {
        if (!(Yii::app()->controller instanceof EmailInboxesController)) {
            return array ();
        }
        if (!isset ($this->emailInboxes)) 
            return array ();
        $emailInboxIds = CJSON::decode ($this->emailInboxes);
        if (!is_array ($emailInboxIds)) 
            return array();
        $emailInboxes = array ();
        $newEmailInboxIds = array ();
        foreach ($emailInboxIds as $id) {
            $emailInbox = EmailInboxes::Model ()->findByPk ($id);
            if ($emailInbox && 
                Yii::app()->controller->checkPermissions ($emailInbox, 'view')) {

                $emailInboxes[$emailInbox->name] = $emailInbox;
                $newEmailInboxIds[] = $id;
            } 
        }
        // remove ids for nonexistent inboxes and inboxes for which the user lacks view permissions
        if (count (array_diff ($emailInboxIds, $newEmailInboxIds))) {
            $this->emailInboxes = CJSON::encode ($newEmailInboxIds);
            $this->update ('emailInboxes');
        }
        return $emailInboxes;
    }

    /**
     * @param array $inboxIds ids of EmailInboxes records
     */
    public function setEmailInboxes (array $inboxIds) {
        $this->emailInboxes = CJSON::encode ($inboxIds);
    }
    

    /**
     * @return Profile 
     */
    public function getGuestProfile () {
        return $this->findByAttributes (array ('username' => self::GUEST_PROFILE_USERNAME)); 
    }

    /**
     * @param string $name name of settings class
     * @return Settings 
     */
    private $_widgetLayouts = array (
        'ProfileWidgetLayout' => null,
         
        'DataWidgetLayout' => null,
         
        'RecordViewWidgetLayout' => null,
        'TopicsWidgetLayout' => null,
    );
    public function getWidgetLayout ($name) {
        if (!$this->_widgetLayouts[$name]) {
            $attributes = array ( 
                'recordType' => 'Profile',
                'recordId' => $this->id,
                'isDefault' => 1,
                'embeddedModelName' => $name,
            );
            $model = Settings::model ()->findByAttributes ($attributes);
            if (!$model) {
                $model = new Settings;
                $model->setAttributes ($attributes, false);
                $model->unpackAll ();
                $model->save ();
            }
            $this->_widgetLayouts[$name] = $model;
        }
        return $this->_widgetLayouts[$name];
    }
      
}
