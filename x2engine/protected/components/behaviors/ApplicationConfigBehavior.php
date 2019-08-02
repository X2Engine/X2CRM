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





// Imports that are required by properties/methods of this behavior:
Yii::import('application.models.Admin');
Yii::import('application.models.Modules');
Yii::import('application.components.util.EncryptUtil');
Yii::import('application.components.util.FileUtil');
Yii::import('application.components.util.ResponseUtil');
Yii::import('application.modules.users.models.*');
Yii::import('application.modules.media.models.Media');


/**
 * ApplicationConfigBehavior is a behavior for the application. It loads
 * additional config paramenters that cannot be statically written in config/main
 * and is also used for features in common with the console application.
 *
 * @property string $absoluteBaseUrl (read-only) the base URL of the web
 *  application, independent of whether there is a web request.
 * @property string $edition The "edition" of the software.
 * @property array $editionHierarchy Information about software sets as defined
 *  in the static configuration file protected/data/editionHierarchy.php
 * @property array $editions (read-only) The editions that apply to the app.
 * @property string $externalAbsoluteBaseUrl (read-only) the absolute base url
 *  of the application to use when creating URLs to be viewed publicly
 * @property string $externalWebRoot (read-only) The absolute base webroot URL
 *  of the application to use when creating URLs to be viewed publicly, from
 *  the internet (i.e. the web lead capture form, email tracking links, etc.)
 * @property integer|bool $locked Integer (timestamp) if the application is
 *  locked; false otherwise.
 * @property string $lockFile Path to the lock file
 * @property Admin $settings The admin model containing settings for the app.
 * @property integer $suID (read-only) substitute user ID in the case that no
 *  user session is available.
 * @property User $suModel Substitute web user model in the case that no user
 *  session is available.
 * @package application.components
 */
class ApplicationConfigBehavior extends CBehavior {

    /**
     * Stores information about software edition sets (for manually setting
     * edition, when testing software subsets)
     * @var type
     */
    private static $_editions;

    /**
     * Software edition detection based on logo presence.
     * @var type 
     */
    private static $_logoHashes = array(
        'pro'=> '0e666bd65d6204fa76ea1aec2a0f3217',
        'pla'=> '1d4ffaf4d1f7af03f294217214a63fd2',
        'ent'=> '1d4ffaf4d1f7af03f294217214a63fd2'
    );

    private $_absoluteBaseUrl;
    private $_edition;
    private $_externalAbsoluteBaseUrl;
    private $_externalWebRoot;
    private $_externalWebDomain;

    /**
     * Signifies whether the CryptUtil method has been initialized already
     * @var bool
     */
    private $_cryptInit = false;
    private $_settings;
    
    /**
     * If the application is locked, this will be an integer corresponding to
     * the date that the application was locked. Otherwise, it will be false.
     * @var mixed
     */
    private $_locked;


    /**
     * Substitute user ID. Used in the case of API calls and console commands
     * when the web user component is not available (because there is no user
     * session in such cases).
     *
     * @var integer
     */
    private $_suID;

    /**
     * Substitute user model (used in api and console scenarios)
     * @var User
     */
    private $_suModel;

    /**
     * Distinguishes whether the model is being used inside an actual user session.
     * @var bool
     */
    private $_isInSession;

    /**
     * Declares events and the event handler methods.
     *
     * See yii documentation on behavior; this is an override of
     * {@link CBehavior::events()}
     */
    public function events(){
        return array_merge(parent::events(), array(
            'onBeginRequest' => 'beginRequest',
        ));
    }

    public function getUpdateServer() {
        return X2_UPDATE_BETA ? 'http://beta.x2planet.com' : 'https://x2planet.com';
    }

    /**
     * Returns a JS string which declares two global JS dictionaries if they haven't already been 
     * declared. Additional properties of the yii global are declared if the user has a profile.
     * The globals would already have been decalared in the case that this is an AJAX request in 
     * which registered scripts are being sent back in response to the client.
     *
     * @param object if set, additional profile specific properties are declared
     * @returns string A JS string
     */
    public function getJSGlobalsSetupScript ($profile=null) {
        if ($profile) {
            $notificationSound = '';
            if(!empty($profile->notificationSound) && is_numeric ($profile->notificationSound)) {
                $notificationSound = Yii::app()->createExternalUrl('/media/media/getFile', array(
                    'id' => $profile->notificationSound,
                ));
            }
        }
        $yii = array (
            'baseUrl' => $this->owner->baseUrl,
            'absoluteBaseUrl' => $this->owner->absoluteBaseUrl,
            'scriptUrl' => $this->owner->request->scriptUrl,
            'themeBaseUrl' => $this->owner->theme->baseUrl,
            'language' =>
                ($this->owner->language == 'en' ? '' : $this->owner->getLanguage()),
            'datePickerFormat' => Formatter::formatDatePicker('medium'),
            'timePickerFormat' => Formatter::formatTimePicker(),
        );
        if ($profile) {
            $yii['profile'] = $profile->getAttributes ();
            $yii['notificationSoundPath'] = $notificationSound;
        }
        $x2 = array (
            'DEBUG' => YII_DEBUG,
            'DEV_MODE' => X2_DEV_MODE,
            'UNIT_TESTING' => YII_UNIT_TESTING,
            'isGuest' => !$this->owner->params->noSession && $this->owner->user->getIsGuest(),
            'notifUpdateInterval' => $this->settings->chatPollTime,
            'isAndroid' => AuxLib::isAndroid (),
            'isIPad' => AuxLib::isIPad (),
            'isMobileApp' => $this->isMobileApp (),
            'isPhoneGap' => $this->isPhoneGap (),
        );
        $setX2 = '';
        foreach ($x2 as $key => $val) {
            $setX2 .= "x2.$key = ".CJSON::encode ($val).";\n";
        }
        return '
            ;(function () {
                if (typeof yii === "undefined") {
                    yii = '.CJSON::encode (
                        $yii
                    ).';
                }
                if (typeof x2 === "undefined") {
                    x2 = {};
                }
                '.$setX2.'
            }) ();
        ';
    }

    /**
     * Load dynamic app configuration.
     *
     * Per the onBeginRequest key in the array returned by {@link events()},
     * this method will be called when the request has begun. It allows for
     * many extra configuration tasks to be run on a per-request basis
     * without having to extend {@link Yii} and override its methods.
     */
    public function beginRequest(){
        // About the "noSession" property/variable:
        //
        // This variable, if true, indicates that the application is running in
        // the context of either an API call or a console command, in which case
        // there would not be the typical authenticated user and session
        // variables one would need in a web request
        //
        // It's necessary because originally this method was written with
        // absolutely no regard for compatibility with the API or Yii console,
        // and thus certain lines of code that make references to the usual web
        // environment with cookie-based authentication (which would fail in
        // those cases) needed to be kept inside of conditional statements that
        // are skipped over if in the console/API.
        $this->owner->params->noSession =
                $this->owner->params->noSession
                || strpos($this->owner->request->getPathInfo(),'api/')===0
                || strpos($this->owner->request->getPathInfo(),'api2/')===0;
        $noSession = $this->owner->params->noSession;

        if(!$noSession){
            if($this->owner->request->getPathInfo() == 'notifications/get'){ // skip all the loading if this is a chat/notification update
                Yii::import('application.models.Events');
                Yii::import('application.components.behaviors.ActiveRecordBehavior');
                Yii::import('application.components.X2UrlManager');
                Yii::import('application.components.Formatter');
                Yii::import('application.components.FieldFormatter');
                Yii::import('application.controllers.x2base');
                Yii::import('application.controllers.X2Controller');
                Yii::import('application.components.util.AuxLib');
                Yii::import('application.models.Roles');
                Yii::import('application.components.X2AuthManager');
                Yii::import('application.components.X2Html');
                Yii::import('application.components.X2WebUser');
                Yii::import('application.components.sortableWidget.*');
                Yii::import('application.components.sortableWidget.profileWidgets.*');
                Yii::import('application.components.sortableWidget.recordViewWidgets.*');
                 
                Yii::import('application.components.sortableWidget.dataWidgets.*');
                 
                Yii::import('application.components.X2Settings.*');
                Yii::import('application.components.X2MessageSource');
                Yii::import('application.components.X2Html');
                Yii::import('application.components.behaviors.CalendarInviteBehavior');
                Yii::import('application.components.behaviors.JSONEmbeddedModelFieldsBehavior');
                Yii::import('application.components.behaviors.MergeableBehavior');
                Yii::import('application.components.behaviors.TransformedFieldStorageBehavior');
                Yii::import('application.components.behaviors.EncryptedFieldsBehavior');
                Yii::import('application.components.permissions.*');
                Yii::import('application.models.Modules');
                // import all the models
                Yii::import('application.models.Social');
                Yii::import('application.models.Profile');
                Yii::import('application.models.Notification');
                Yii::import('application.models.Fields');
                foreach(scandir('protected/modules') as $module){
                    if(file_exists('protected/modules/'.$module.'/register.php'))
                        Yii::import('application.modules.'.$module.'.models.*');
                }
                if(!$this->owner->user->getIsGuest())
                    $profData = $this->owner->db->createCommand()
                        ->select('timeZone, language')
                        ->from('x2_profile')
                        ->where('id='.$this->owner->user->getId())->queryRow(); // set the timezone to the admin's
                if(isset($profData)){
                    if(isset($profData['timeZone'])){
                        $timezone = $profData['timeZone'];
                    }
                    if(isset($profData['language'])){
                        $language = $profData['language'];
                    }else{

                    }
                }
                if(!isset($timezone))
                    $timezone = 'UTC';
                if(!isset($language))
                    $language = 'en';
                date_default_timezone_set($timezone);
                $this->owner->language = $language;
                Yii::import('application.models.X2ActiveRecord');
                Yii::import('application.models.X2Model');
                Yii::import('application.models.Dropdowns');
                Yii::import('application.models.Admin');
                $this->cryptInit();
                
                // Yii::import('application.models.*');
                // foreach(scandir('protected/modules') as $module){
                // if(file_exists('protected/modules/'.$module.'/register.php'))
                // Yii::import('application.modules.'.$module.'.models.*');
                // }
                return;
            }
        }else{
            Yii::import('application.models.X2ActiveRecord');
            Yii::import('application.models.Profile');
            Yii::import('application.components.sortableWidget.*');
            Yii::import('application.components.X2Settings.*');
            Yii::import('application.components.behaviors.TransformedFieldStorageBehavior');
            // Set time zone based on the default value
            date_default_timezone_set(Profile::model()->tableSchema->getColumn('timeZone')->defaultValue);
        }

        $this->importDirectories();
        
        $this->cryptInit();

        if (YII_DEBUG) $this->owner->params->timer = new TimerUtil;
        
        $this->owner->messages->onMissingTranslation = array(new TranslationLogger, 'log');

        // Set profile
        //
        // Get the Administrator's and the current user's profile.
        $adminProf = Profile::model()->findByPk(1);
        $this->owner->params->adminProfile = $adminProf;

        // Use a separate domain for static assets if requested
        if ($this->owner->settings->enableAssetDomains)
            Yii::app()->assetManager->enableAssetDomains();

        if(!$noSession){ // Typical web session:
            $notGuest = !$this->owner->user->getIsGuest();

            if($notGuest) {
                $this->owner->params->profile = X2Model::model('Profile')->findByAttributes(array(
                    'username' => $this->owner->user->getName()
                        ));
                $this->setSuModel($this->owner->params->profile->user);
            } else {
                $this->owner->params->profile = Profile::model ()->getGuestProfile ();
            }
        } else {
            // Use the admin profile as the user profile.
            //
            // If a different profile is desired in an API call or console
            // command, a different profile should be loaded.
            //
            // Using "admin" as the default profile should not affect
            // permissions (that's what the "suModel" property is for). It is
            // merely to account for cases where there is a reference to the
            // "profile" property of some model or component class that would
            // break the application outside the scope of a web request with a
            // session and cookie-based authentication.
            $notGuest = false;
            $this->owner->params->profile = $adminProf;
            $userModel = $this->owner->params->profile->user;
            $this->setSuModel($userModel instanceof User
                    ? $userModel
                    : User::model()->findByPk(1));
        }
        
        
        // Set session variables
        if(!$noSession){
            $sessionId = isset($_SESSION['sessionId']) ? $_SESSION['sessionId'] : session_id();
            $session = X2Model::model('Session')->findByPk($sessionId);
            if(!empty($this->owner->params->profile)){
                $_SESSION['fullscreen'] = $this->owner->params->profile->fullscreen;
            }
            if(!($this->owner->request->getPathInfo() == 'site/getEvents')){
                if($notGuest){
                    $this->owner->user->setReturnUrl($this->owner->request->requestUri);
                    if($session != null){
                        $timeout = Roles::getUserTimeout($this->owner->user->getId());
                        if($session->lastUpdated + $timeout < time()){
                            SessionLog::logSession($this->owner->user->getName(), $sessionId, 'activeTimeout');
                            $session->delete();
                            $this->owner->user->logout(false);
                            $this->_suModel = null;
                            $this->_suID = null;
                            $this->setUserAccessParameters(null);
                        }else{
                            // Print a warning message
                            if($this->owner->session['debugEmailWarning']){
                                $this->owner->session['debugEmailWarning'] = 0;
                                $this->owner->user->setFlash('admin.debugEmailMode',
                                        Yii::t('app', 'Note, email debugging mode '
                                                . 'is enabled. Emails will not '
                                                . 'actually be delivered.'));
                            }

                            $session->lastUpdated = time();
                            $session->update(array('lastUpdated'));

                            $this->owner->params->sessionStatus = $session->status;
                        }
                    }else{
                        $this->owner->user->logout(false);
                        if ($this->isMobileApp () || $this->isPhoneGap ()) {
                            $this->owner->getRequest ()->redirect (
                                $this->owner->createUrl('mobile/login'));
                        } else {
                            $this->owner->getRequest ()->redirect (
                                $this->owner->createUrl('site/login'));
                        }
                    }
                }else{
                    // Guest
                    $this->setUserAccessParameters(null);
                }
            }
        }

        // Configure logos
        if(!($logo = $this->owner->cache['x2Power'])){
            $logo = 'data:image/png;base64,'.base64_encode(
                file_get_contents(implode(DIRECTORY_SEPARATOR, array(
                    Yii::app()->basePath,
                    '..',
                    'images',
                    'powered_by_x2engine.png'
            ))));
            $this->owner->cache['x2Power'] = $logo;
        }
        $this->owner->params->x2Power = $logo;

        // Set currency and load currency symbols
        $this->owner->params->currency = $this->settings->currency;
        $locale = $this->owner->locale;
        $curSyms = array();
        foreach($this->owner->params->supportedCurrencies as $curCode){
            $curSyms[$curCode] = $locale->getCurrencySymbol($curCode);
        }
        $this->owner->params->supportedCurrencySymbols = $curSyms; // Code to symbol

        // Set language
        if(!empty($this->owner->params->profile->language) && $notGuest)
            $this->owner->language = $this->owner->params->profile->language;
        else if(isset($adminProf))
            $this->owner->language = $adminProf->language;

        // Set timezone
        if(!empty($this->owner->params->profile->timeZone) && $notGuest)
            date_default_timezone_set($this->owner->params->profile->timeZone);
        elseif(!empty($adminProf->timeZone))
            date_default_timezone_set($adminProf->timeZone);
        else
            date_default_timezone_set('UTC');
        setlocale(LC_ALL, 'en_US.UTF-8');

        // Set base path and theme path globals for JS (web UI only)
        if(!$noSession){
            if($notGuest){
                $profile = $this->owner->params->profile;
                if(isset($profile)){
                    $yiiString = $this->getJSGlobalsSetupScript ($profile);
                }else{
                    $yiiString = $this->getJSGlobalsSetupScript ();
                }
                if(!$this->owner->request->isAjaxRequest) {
                    
Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
    'dmFyIF8weDZjNzM9WyJceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjQiLCJceDZDXHg2R'
    .'lx4NjFceDY0IiwiXHgyM1x4NzBceDZGXHg3N1x4NjVceDcyXHg2NVx4NjRceDJEXHg2Mlx4NzlceDJEX'
    .'Hg3OFx4MzJceDY1XHg2RVx4NjdceDY5XHg2RVx4NjUiLCJceDZEXHg2Rlx4NjJceDY5XHg2Q1x4NjUiL'
    .'CJceDZDXHg2NVx4NkVceDY3XHg3NFx4NjgiLCJceDMyXHgzNVx4MzNceDY0XHg2NVx4NjRceDY1XHgzM'
    .'Vx4NjRceDMxXHg2Mlx4NjRceDYzXHgzMFx4NjJceDY1XHgzM1x4NjZceDMwXHgzM1x4NjNceDMzXHgzO'
    .'Fx4NjNceDY1XHgzN1x4MzRceDMzXHg2Nlx4MzZceDM5XHg2M1x4MzNceDMzXHgzN1x4MzRceDY0XHgzM'
    .'Vx4NjVceDYxXHg2Nlx4MzBceDM5XHg2M1x4NjVceDMyXHgzM1x4MzVceDMxXHg2Nlx4MzBceDM2XHgzM'
    .'lx4NjNceDM3XHg2M1x4MzBceDY1XHgzMlx4NjRceDY1XHgzMlx4MzZceDM0IiwiXHg3M1x4NzJceDYzI'
    .'iwiXHg2MVx4NzRceDc0XHg3MiIsIlx4M0FceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1IiwiXHg2O'
    .'Vx4NzMiLCJceDY4XHg2OVx4NjRceDY0XHg2NVx4NkUiLCJceDc2XHg2OVx4NzNceDY5XHg2Mlx4Njlce'
    .'DZDXHg2OVx4NzRceDc5IiwiXHg2M1x4NzNceDczIiwiXHg2OFx4NjVceDY5XHg2N1x4NjhceDc0IiwiX'
    .'Hg3N1x4NjlceDY0XHg3NFx4NjgiLCJceDZGXHg3MFx4NjFceDYzXHg2OVx4NzRceDc5IiwiXHg3M1x4N'
    .'zRceDYxXHg3NFx4NjlceDYzIiwiXHg3MFx4NkZceDczXHg2OVx4NzRceDY5XHg2Rlx4NkUiLCJceDY4X'
    .'Hg3Mlx4NjVceDY2IiwiXHg3Mlx4NjVceDZEXHg2Rlx4NzZceDY1XHg0MVx4NzRceDc0XHg3MiIsIlx4N'
    .'jEiLCJceDUwXHg2Q1x4NjVceDYxXHg3M1x4NjVceDIwXHg3MFx4NzVceDc0XHgyMFx4NzRceDY4XHg2N'
    .'Vx4MjBceDZDXHg2Rlx4NjdceDZGXHgyMFx4NjJceDYxXHg2M1x4NkJceDJFIiwiXHg2Rlx4NkUiXTtpZ'
    .'ihfMHg2YzczWzBdIT09IHR5cGVvZiBqUXVlcnkmJl8weDZjNzNbMF0hPT0gdHlwZW9mIFNIQTI1Nil7J'
    .'Ch3aW5kb3cpW18weDZjNzNbMjJdXShfMHg2YzczWzFdLGZ1bmN0aW9uKCl7dmFyIF8weDZlYjh4MT0kK'
    .'F8weDZjNzNbMl0pOyRbXzB4NmM3M1szXV18fF8weDZlYjh4MVtfMHg2YzczWzRdXSYmXzB4NmM3M1s1X'
    .'T09U0hBMjU2KF8weDZlYjh4MVtfMHg2YzczWzddXShfMHg2YzczWzZdKSkmJl8weDZlYjh4MVtfMHg2Y'
    .'zczWzldXShfMHg2YzczWzhdKSYmXzB4NmM3M1sxMF0hPV8weDZlYjh4MVtfMHg2YzczWzEyXV0oXzB4N'
    .'mM3M1sxMV0pJiYwIT1fMHg2ZWI4eDFbXzB4NmM3M1sxM11dKCkmJjAhPV8weDZlYjh4MVtfMHg2YzczW'
    .'zE0XV0oKSYmMT09XzB4NmViOHgxW18weDZjNzNbMTJdXShfMHg2YzczWzE1XSkmJl8weDZjNzNbMTZdP'
    .'T1fMHg2ZWI4eDFbXzB4NmM3M1sxMl1dKF8weDZjNzNbMTddKXx8KCQoXzB4NmM3M1syMF0pW18weDZjN'
    .'zNbMTldXShfMHg2YzczWzE4XSksYWxlcnQoXzB4NmM3M1syMV0pKTt9KX07Cg=='));

                }
            }else{
                $yiiString = $this->getJSGlobalsSetupScript ();
            }

            $this->owner->clientScript->registerScript(
                'setParams', $yiiString, CClientScript::POS_HEAD);
            $cs = $this->owner->clientScript;
            $baseUrl = $this->owner->request->baseUrl;
            $jsVersion = '?'.$this->owner->params->buildDate;
            /**
             * To be restored when JavaScript minification is added to the build process:
             * $cs->scriptMap=array(
             * 'backgroundImage.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'json2.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'layout.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'media.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'modernizr.custom.66175.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'publisher.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * //'relationships.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'tags.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'translator.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'widgets.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * 'x2forms.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
             * ); */
        }
    }

    /**
     * Returns true or false for whether or not the application's current edition
     * contains a given edition.
     * 
     * @param string $edition The edition. With "opensource", this function will
     *  always evaluate to true.
     * @return boolean
     */
    public function contEd($edition) {
        return (bool) $this->editionHierarchy[$this->getEdition()][$edition];
    }

    /**
     * Instantiates the encryption utility object so that components depending
     * on {@link EncryptedFieldsBehavior} can also be instantiated.
     */
    public function cryptInit(){
        if(!$this->_cryptInit){
            $key = $this->owner->basePath.'/config/encryption.key';
            $iv = $this->owner->basePath.'/config/encryption.iv';
            if(extension_loaded('openssl') && file_exists($key) && file_exists($iv)){
                EncryptedFieldsBehavior::setup($key, $iv);
            }else if (extension_loaded('openssl') && file_exists($key) && file_exists($iv)) {
                EncryptedFieldsBehavior::setup($key, $iv);
            }else {
                // Use unsafe method with encryption
                EncryptedFieldsBehavior::setupUnsafe();
            }
        }
    }

    /**
     * Creates an URL that is safe to use for public-facing assets.
     *
     * In the case that there is no web request, but the "external" web root is
     * defined, the $_SERVER superglobal doesn't have necessary indexes like
     * "SERVER_PROTOCOL" to construct valid URLs. However, using the user-defined
     * external web root, it will explicitly use the route to generate the URL
     * (and assume the "path" format is always used for URLs).
     *
     * The solution ("Offline URL generation" should really be replaced with
     * something more elegant in the future. It is a crude attempt to replicate
     * URL creation for offline-generated URLs, i.e. for a console command that
     * sends emails. It was deemed, at the time of this special case's writing,
     * impractical to override CUrlManager.createUrl due to the complexity and
     * number of places where the $_SERVER superglobal (which any solution would
     * need to eliminate dependency thereupon) is referenced / depended upon.
     *
     * Provided the convention of always using absolute (and never relative)
     * routes is always adhered to, and the URL style remains "path", this
     * kludge should always work.
     *
     * @param string $route The module/controller/action route
     * @param array $params Query parameters
     */
    public function createExternalUrl($route, $params = array()){
        if(!ResponseUtil::isCli() && $this->owner->controller instanceof CController){ 
            // Standard in-web-request URL generation

            if ($route === '') {
                $route = $this->owner->controller->getId() . '/' . 
                    $this->owner->controller->getAction()->getId();
            } elseif (strpos($route, '/') === false) {
                $route = $this->owner->controller->getId() . '/' . $route;
            }
            if ($route[0] !== '/' && ($module = $this->owner->controller->getModule()) !== null) {
                $route = $module->getId() . '/' . $route;
            }
            $requestUrl = $this->owner->getUrlManager()->createUrlWithoutBase($route, $params);
            return rtrim($this->externalAbsoluteBaseUrl,'/') . $requestUrl;
                
        }else{ // Offline URL generation
            return rtrim($this->externalAbsoluteBaseUrl,'/') .
                (YII_UNIT_TESTING ? '/index-test.php/' : '/index.php/').
                trim($route, '/').'?'.http_build_query($params, '', '&');
        }
    }

    /**
     * Magic getter for {@link absoluteBaseUrl}; in the case that web request data
     * isn't available, it uses a config file.
     *
     * @return type
     */
    public function getAbsoluteBaseUrl(){
        if(!isset($this->_absoluteBaseUrl)){
            if(ResponseUtil::isCli()){
                // It's assumed that in this case, we're dealing with (for example)
                // a cron script that sends emails and has to generate URLs. It
                // needs info about how to access the CRM from the outside...
                $this->_absoluteBaseUrl = '';
                if($this->contEd('pro')
                        && $this->settings->externalBaseUrl
                        && $this->settings->externalBaseUri){
                    // Use the base URL from "public info settings" since it's
                    // available:
                    $this->_absoluteBaseUrl = $this->settings->externalBaseUrl.$this->settings->externalBaseUri;
                }else if($file = realpath($this->owner->basePath.'/../webConfig.php')){
                    // Use the web API config file to construct the URL (our
                    // last hope)
                    include($file);
                    if(isset($url))
                        $this->_absoluteBaseUrl = $url;
                } else {
                    // There's nothing left we can legitimately do and have it
                    // work correctly! Make something up.
                    $this->_absoluteBaseUrl = 'http://localhost';
                }
            }else{
                $this->_absoluteBaseUrl = $this->owner->getBaseUrl (true);
            }
        }
        return $this->_absoluteBaseUrl;
    }

    /**
     * Getter for {@link admin}
     */
    public function getSettings() {
        if(!isset($this->_settings)) {
            $this->cryptInit();
            $this->_settings = CActiveRecord::model('Admin')->findByPk(1);
        }
        return $this->_settings;
    }

    /**
     * Getter for {@link edition}
     *
     * @return string
     */
    public function getEdition() {
        if(!isset($this->_edition)){
            if(YII_DEBUG){
                switch(PRO_VERSION) {
                    case 1:
                        $this->_edition = 'pro';
                        break;
                    case 2:
                        $this->_edition = 'pla';
                        break;
                    case 3:
                        $this->_edition = 'ent';
                        break;
                    default:
                        $this->_edition = 'opensource';
                }
            }else{
                $this->_edition = 'opensource';
                $logo = "images/x2engine_crm_ent.png";
                $logoPath = implode(DIRECTORY_SEPARATOR, array(
                    $this->owner->basePath,
                    '..',
                    FileUtil::rpath($logo)
                ));
                if(file_exists($logoPath)){
                    if(md5_file($logoPath) == self::$_logoHashes['ent'])
                        $this->_edition = 'ent';
                }
            }
        }
        return $this->_edition;
    }

    /**
     * Returns the edition hierarchy defined in the static configuration.
     *
     * @return type
     */
    public function getEditionHierarchy() {
        if(!isset(self::$_editions)) {
            self::$_editions = require(implode(DIRECTORY_SEPARATOR,array(
                Yii::app()->basePath,
                'data',
                'editionHierarchy.php'
            )));
        }
        return self::$_editions;
    }

    /**
     * Returns editions "contained" by the app's current edition
     */
    public function getEditions() {
        return array_filter($this->editionHierarchy[$this->getEdition()]);
    }

    /**
     * Returns the name of the software edition.
     */
    public function getEditionLabel($addPrefix = false) {
        $labels = $this->getEditionLabels($addPrefix);
        return $labels[$this->getEdition()];
    }

    public function getEditionLabels($addPrefix = false) {
        $prefix = $addPrefix?'X2CRM ':'';
        return array(
            'opensource' => $prefix.'Open Source Edition',
            'pro' => $prefix.'Professional Edition',
            'pla' => $prefix.'Platinum Edition',
            'ent' => $prefix.'Enterprise Edition'
        );
    }

    /**
     * @return string url of favicon image file for the current version
     */
    public function getFavIconUrl () {
        $baseUrl = Yii::app()->clientScript->baseUrl;
        return $baseUrl.'/images/favicon.ico';
    }

    /**
     * @return string url of login logo image file for the current version
     */
    public function getLoginLogoUrl () {
        $baseUrl = Yii::app()->clientScript->baseUrl;
        return $baseUrl.'/images/x2engine.png';
    }

    public function getExternalAbsoluteBaseUrl(){
        if(!isset($this->_externalAbsoluteBaseUrl) || YII_UNIT_TESTING){
            $this->_externalAbsoluteBaseUrl = $this->externalWebDomain . $this->externalWebRoot;
        }
        return $this->_externalAbsoluteBaseUrl;
    }
    
    public function getExternalWebRoot() {
        if (!isset($this->_externalWebRoot) || YII_UNIT_TESTING) {
            $eabu = $this->settings->externalBaseUri;
            $this->_externalWebRoot = $eabu ? $eabu : $this->owner->baseUrl;
        }
        return (strpos($this->_externalWebRoot, '/') !== 0) ? '/' . $this->_externalWebRoot
                    : $this->_externalWebRoot;
    }

    /**
     * Resolves the public-facing absolute base url.
     *
     * @return type
     */
    public function getExternalWebDomain() {
        if (!isset($this->_externalWebDomain) || YII_UNIT_TESTING) {
            $eabu = $this->settings->externalBaseUrl;
            $this->_externalWebDomain = $eabu ? $eabu : $this->owner->request->getHostInfo();
        }
        return $this->_externalWebDomain;
    }

    /**
     * "isGuest" wrapper that can be used from CLI
     *
     * Used in biz rules for RBAC items in place of Yii::app()->user->isGuest for
     * the reason that Yii::app()->user is meaningless at the command line
     * @return type
     */
    public function getIsUserGuest() {
        if(php_sapi_name() == 'cli') {
            return false;
        } else {
            return $this->owner->user->isGuest;
        }
    }

	/**
	 * Substitute user ID magic getter.
	 *
	 * If the user has already been looked up or set, method will defer to its
	 * value for id. Defers to the value of id in {@link suModel}.
	 * @return type
	 */
	public function getSuID(){
        if(!isset($this->_suID) || isset($this->_suModel)){
            if(isset($this->_suModel)){
                $this->_suID = (integer) $this->_suModel->id;
            }elseif($this->isInSession){
                $this->_suID = (integer) $this->owner->user->getId();
            }elseif(php_sapi_name() == 'cli'){
                // Assume admin
                $this->_suID = 1;
            }else{
                // Assume nothing/treat as guest
                $this->_suID = null;
            }
        }
        return $this->_suID;
    }

    /**
     * Shortcut method for ascertaining if a user session is available
     * @return type
     */
    public function getIsInSession(){
        if(!isset($this->_isInSession)){
            $app = $this->owner;
            if($app instanceof CConsoleApplication) {
                $this->_isInSession = false;
            } elseif(!$app->params->hasProperty('noSession')){
                $this->_isInSession = true;
            }else{
                if(!isset(Yii::app()->user) || 
                    Yii::app()->user instanceof X2NonWebUser ||
                    Yii::app()->user->isGuest){

                    $app->params->noSession = true;
                }
                $this->_isInSession = !$app->params->noSession;
            }
        }
        return $this->_isInSession;
    }

    /**
     * Returns the lock status of the application.
     * @return boolean
     */
    public function getLocked() {
        if(!isset($this->_locked)){
            $file = $this->lockFile;
            if(!file_exists($file))
                return false;
            $this->_locked = (int) trim(file_get_contents($file));
        }
        return $this->_locked;
    }

    /**
     * Returns the path to the application lock file
     * @return type
     */
    public function getLockFile() {
        return implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'runtime','app.lock'));
    }

    /**
     * Lock the application (non-administrative users cannot use it).
     *
     * If the value evaluates to false, the application will be unlocked.
     *
     * @param type $value
     */
    public function setLocked($value) {
        $this->_locked = $value;
        $file = $this->lockFile;
        if($value == false && file_exists($file)) {
            unlink($file);
        } elseif($value !== false) {
            file_put_contents($this->lockFile,$value);
        }

    }

    /**
     * Substitute user model magic getter.
     *
     * @return User
     */
    public function getSuModel(){
        if(!isset($this->_suModel)){
            if($this->isInSession)
                $this->_suID == $this->getOwner()->getUser()->getId();
            $this->_suModel = User::model()->findByPk($this->getSuID());
        }
        return $this->_suModel;
    }

    /**
     * Substitute user name getter.
     *
     * This is intended to be safer than suModel->userName insofar as it defaults
     * to "Guest" if no name/session has yet been established. It is expected that
     * in console commands, API requests and unit testing, the {@link suModel}
     * property be set as desired, so that this does not evaluate to "Guest"
     */
    public function getSuName(){
        if($this->getIsInSession()) {
            return $this->owner->user->getName();
        }else{
            if(!isset($this->_suModel)){
                return 'Guest';
            }else{
                return $this->_suModel->username;
            }
        }
    }
    
    /**
     * Magic setter for substitute user model
     * @param User $user
     */
    public function setSuModel(User $user){
        $this->_suModel = $user;
        if($user->id !== null)
            $this->setUserAccessParameters($user->id);
    }

    /**
     * Adds parameters that are used to determine user access
     * @param type $userId
     */
    private function setUserAccessParameters($userId) {
        $this->owner->params->groups = Groups::getUserGroups($userId);
        $this->owner->params->roles = Roles::getUserRoles($userId);
        $this->owner->params->isAdmin = $userId !== null
                ? $this->owner->authManager->checkAccess('AdminIndex', $userId)
                : false; 
    }

    /**
     * Import all directories that are used system-wide.
     */
    public function importDirectories(){
        Yii::import('application.models.*');
        Yii::import('application.controllers.X2Controller');
        Yii::import('application.controllers.x2base');
        Yii::import('application.components.*');
        Yii::import('application.components.behaviors.*');
        Yii::import('application.components.formatters.*');
        Yii::import('application.components.X2GridView.*');
        Yii::import('application.components.X2Settings.*');
        Yii::import('application.components.publisher.*');
        Yii::import('application.components.recordConversion.*');
        Yii::import('application.components.validators.*');
        Yii::import('application.components.sortableWidget.*');
        Yii::import('application.components.sortableWidget.profileWidgets.*');
        Yii::import('application.components.sortableWidget.recordViewWidgets.*');
         
        Yii::import('application.components.sortableWidget.dataWidgets.*');
         
        Yii::import('application.components.filters.*');
        Yii::import('application.components.util.*');
        Yii::import('application.components.permissions.*');
        Yii::import('application.modules.media.models.Media');
        Yii::import('application.modules.groups.models.Groups');
        Yii::import('application.modules.charts.models.*');
        Yii::import('application.modules.charts.components.*');
        Yii::import('application.modules.charts.ChartsModule');

        $modules = $this->owner->modules;
        $arr = array();
        $modulePath = implode(DIRECTORY_SEPARATOR,array(
            $this->owner->basePath,
            'modules'
        ));
        foreach(scandir($modulePath) as $module){
            $regScript = implode(DIRECTORY_SEPARATOR,array(
                $modulePath,
                $module,
                'register.php'
            ));
            if(file_exists($regScript)){
                $arr[$module] = ucfirst($module);
                $thisModulePath = "application.modules.$module";
                Yii::import("$thisModulePath.models.*");
                if(is_dir(Yii::getPathOfAlias($thisModulePath).DIRECTORY_SEPARATOR.'components')) {
                    Yii::import("$thisModulePath.components.*");
                }
            }
        }
        foreach($arr as $key => $module){
            $record = Modules::model()->findByAttributes(array('name' => $key));
            if(isset($record))
                $modules[] = $key;
        }
        $this->owner->setModules($modules);
    }

    public function isMobileApp () {
        return isset ($_GET['isMobileApp']) && $_GET['isMobileApp'];
    }

    private function isPhoneGap () {
        return isset ($_COOKIE['isPhoneGap']) && $_COOKIE['isPhoneGap'];
    }

}
