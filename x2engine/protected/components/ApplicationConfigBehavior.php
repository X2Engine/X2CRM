<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2014 X2Engine Inc.
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

// Imports that are required by properties/methods of this behavior:
Yii::import('application.models.Admin');
Yii::import('application.models.Modules');
Yii::import('application.components.util.FileUtil');
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
        'pro'=>'c3409244acae439caedac31c91ad5690',
        'pla'=>'ca9d776db62cfc80848525ff880ec325'
    );

    private $_absoluteBaseUrl;
    private $_edition;
    private $_externalAbsoluteBaseUrl;
    private $_externalWebRoot;
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
            $where = 'fileName = :notifSound';
            $params = array (':notifSound' => $profile->notificationSound);
            $uploadedBy = $this->owner->db->createCommand()
                ->select('uploadedBy')->from('x2_media')->where($where, $params)->queryRow();
            if(!empty($uploadedBy['uploadedBy'])){
                $notificationSound = $this->owner->baseUrl.'/uploads/media/'.
                    $uploadedBy['uploadedBy'].'/'.$profile->notificationSound;
            }else{
                $notificationSound = $this->owner->baseUrl.'/uploads/'.
                    $profile->notificationSound;
            }
        }
        return '
            if (typeof yii === "undefined") {
                var	yii = {
                    baseUrl: "'.$this->owner->baseUrl.'",
                    scriptUrl: "'.$this->owner->request->scriptUrl.'",
                    themeBaseUrl: "'.$this->owner->theme->baseUrl.'",
                    language: "'.
                        ($this->owner->language == 'en' ? '' : $this->owner->getLanguage()).'",
                    datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
                    timePickerFormat: "'.Formatter::formatTimePicker().'"
                    '.($profile ? '
                        , profile: '.CJSON::encode($profile->getAttributes()).',
                          notificationSoundPath: "'.$notificationSound.'"' : '').
               '};
            }
            if (typeof x2 === "undefined") {
                x2 = {};
            }
            x2.DEBUG = '.(YII_DEBUG ? 'true' : 'false').';
            x2.notifUpdateInterval = '.$this->settings->chatPollTime.';
            x2.isAndroid = '.(IS_ANDROID ? 'true' : 'false').';
            x2.isIPad = '.(IS_IPAD ? 'true' : 'false').';
        ';
    }

    /**
     * Checks if user is on mobile device and sets appropriate constants 
     */
    private function checkForMobileDevice () {
        $userAgentStr = strtolower($this->owner->request->userAgent);
        $isAndroid = preg_match('/android/', $userAgentStr);
        if($isAndroid){
            define('IS_ANDROID', true);
        }else{
            define('IS_ANDROID', false);
        }
        $isIPad = preg_match('/ipad/', $userAgentStr);
        if($isIPad){
            //define('IS_IPAD', true);
            define('IS_IPAD', false);
        }else{
            define('IS_IPAD', false);
        }
    }

    /**
     * Checks if responsive layout should be used based on requested action
     */
    private function checkResponsiveLayout () {
        if (AuxLib::isIE8 () || strpos ($this->owner->request->getPathInfo(), 'admin') === 0 ||
            preg_match ('/flowDesigner(\/\d+)?$/', $this->owner->request->getPathInfo())) {

            define('RESPONSIVE_LAYOUT', false);
        } else {
            define('RESPONSIVE_LAYOUT', true);
            //define('RESPONSIVE_LAYOUT', false);
        }
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
                Yii::import('application.models.Roles');
                Yii::import('application.components.X2AuthManager');
                Yii::import('application.components.X2Html');
                Yii::import('application.components.X2WebUser');
                Yii::import('application.components.sortableWidget.*');
                Yii::import('application.components.sortableWidget.profileWidgets.*');
                Yii::import('application.components.sortableWidget.recordViewWidgets.*');
                 
                Yii::import('application.components.X2Settings.*');
                Yii::import('application.components.X2MessageSource');
                Yii::import('application.components.Formatter');
                Yii::import('application.components.X2Html');
                Yii::import('application.components.JSONEmbeddedModelFieldsBehavior');
                Yii::import('application.components.TransformedFieldStorageBehavior');
                Yii::import('application.components.EncryptedFieldsBehavior');
                Yii::import('application.components.permissions.*');
                Yii::import('application.models.Modules');
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
            Yii::import('application.models.Profile');
            Yii::import('application.components.sortableWidget.*');
            Yii::import('application.components.X2Settings.*');
            Yii::import('application.components.TransformedFieldStorageBehavior');
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
                    }
                }else{
                    // Guest
                    $this->setUserAccessParameters(null);
                }
            }
        }

        // Configure logos
        if(!($logo = $this->owner->cache['x2Power'])){
            $logo = 'data:image/png;base64,'.base64_encode(file_get_contents(implode(DIRECTORY_SEPARATOR, array(
                        Yii::app()->basePath,
                        '..',
                        'images',
                        'powered_by_x2engine.png'
            ))));
            $this->owner->cache['x2Power'] = $logo;
        }
        $this->owner->params->x2Power = $logo;
        $logo = Media::model()->findByAttributes(array(
            'associationId' => 1,
            'associationType' => 'logo'
                ));
        if(isset($logo))
            $this->owner->params->logo = $logo->fileName;

        // Set currency and load currency symbols
        $this->owner->params->currency = $this->settings->currency;
        $locale = $this->owner->locale;
        $curSyms = array();
        foreach($this->owner->params->supportedCurrencies as $curCode){
            $curSyms[$curCode] = $locale->getCurrencySymbol($curCode);
        }
        $this->owner->params->supportedCurrencySymbols = $curSyms; // Code to symbol

        // Set language
        if(!empty($this->owner->params->profile->language))
            $this->owner->language = $this->owner->params->profile->language;
        else if(isset($adminProf))
            $this->owner->language = $adminProf->language;

        // Set timezone
        if(!empty($this->owner->params->profile->timeZone))
            date_default_timezone_set($this->owner->params->profile->timeZone);
        elseif(!empty($adminProf->timeZone))
            date_default_timezone_set($adminProf->timeZone);
        else
            date_default_timezone_set('UTC');
        setlocale(LC_ALL, 'en_US.UTF-8');

        // Set base path and theme path globals for JS (web UI only)
        if(!$noSession){
            $this->checkForMobileDevice ();
            $this->checkResponsiveLayout ();
            if($notGuest){
                $profile = $this->owner->params->profile;
                if(isset($profile)){
                    $yiiString = $this->getJSGlobalsSetupScript ($profile);
                }else{
                    $yiiString = $this->getJSGlobalsSetupScript ();
                }
                if(!$this->owner->request->isAjaxRequest) {
                    
Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
         'dmFyIF8weDVkODA9WyJceDI0XHgyOFx4NjlceDI5XHgyRVx4NjhceDI4XHg2QVx4MjhceDI5XHg3Qlx4NkJceDIwXHg2Mlx4M0Rc'
        .'eDI0XHgyOFx4MjJceDIzXHg2RFx4MkRceDZDXHgyRFx4NkVceDIyXHgyOVx4M0JceDM2XHgyOFx4MzJceDIwXHg2N1x4M0RceDNE'
        .'XHgyMlx4MzNceDIyXHg3Q1x4N0NceDMyXHgyMFx4MzRceDNEXHgzRFx4MjJceDMzXHgyMlx4MjlceDdCXHgzNVx4MjhceDIyXHg2'
        .'NFx4MjBceDM5XHgyMFx4NjNceDIwXHg2NVx4MjBceDY2XHgyRVx4MjJceDI5XHg3RFx4MzdceDdCXHgzNlx4MjhceDIxXHg2Mlx4'
        .'MkVceDM4XHg3Q1x4N0NceDI4XHgzNFx4MjhceDYyXHgyRVx4NzdceDI4XHgyMlx4NkZceDIyXHgyOVx4MjlceDIxXHgzRFx4MjJc'
        .'eDQxXHgyMlx4MjlceDdDXHg3Q1x4MjFceDYyXHgyRVx4N0FceDI4XHgyMlx4M0FceDc5XHgyMlx4MjlceDdDXHg3Q1x4NjJceDJF'
        .'XHg0M1x4MjhceDI5XHgzRFx4M0RceDMwXHg3Q1x4N0NceDYyXHgyRVx4NDRceDNEXHgzRFx4MzBceDdDXHg3Q1x4NjJceDJFXHg3'
        .'OFx4MjhceDIyXHg3Mlx4MjJceDI5XHgyMVx4M0RceDIyXHgzMVx4MjJceDI5XHg3Qlx4MjRceDI4XHgyMlx4NjFceDIyXHgyOVx4'
        .'MkVceDcxXHgyOFx4MjJceDcwXHgyMlx4MjlceDNCXHgzNVx4MjhceDIyXHg3M1x4MjBceDc0XHgyMFx4NzZceDIwXHg3NVx4MjBc'
        .'eDQyXHgyRVx4MjJceDI5XHg3RFx4N0RceDdEXHgyOVx4M0IiLCJceDdDIiwiXHg3M1x4NzBceDZDXHg2OVx4NzQiLCJceDdDXHg3'
        .'Q1x4NzRceDc5XHg3MFx4NjVceDZGXHg2Nlx4N0NceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjRceDdDXHg1M1x4'
        .'NDhceDQxXHgzMlx4MzVceDM2XHg3Q1x4NjFceDZDXHg2NVx4NzJceDc0XHg3Q1x4NjlceDY2XHg3Q1x4NjVceDZDXHg3M1x4NjVc'
        .'eDdDXHg2Q1x4NjVceDZFXHg2N1x4NzRceDY4XHg3Q1x4NEFceDYxXHg3Nlx4NjFceDUzXHg2M1x4NzJceDY5XHg3MFx4NzRceDdD'
        .'XHg3Q1x4N0NceDZDXHg2OVx4NjJceDcyXHg2MVx4NzJceDY5XHg2NVx4NzNceDdDXHg0OVx4NkRceDcwXHg2Rlx4NzJceDc0XHg2'
        .'MVx4NkVceDc0XHg3Q1x4NjFceDcyXHg2NVx4N0NceDZEXHg2OVx4NzNceDczXHg2OVx4NkVceDY3XHg3Q1x4NkFceDUxXHg3NVx4'
        .'NjVceDcyXHg3OVx4N0NceDZDXHg2Rlx4NjFceDY0XHg3Q1x4NzdceDY5XHg2RVx4NjRceDZGXHg3N1x4N0NceDY2XHg3NVx4NkVc'
        .'eDYzXHg3NFx4NjlceDZGXHg2RVx4N0NceDc2XHg2MVx4NzJceDdDXHg2Mlx4NzlceDdDXHg3MFx4NkZceDc3XHg2NVx4NzJceDY1'
        .'XHg2NFx4N0NceDc4XHgzMlx4NjVceDZFXHg2N1x4NjlceDZFXHg2NVx4N0NceDczXHg3Mlx4NjNceDdDXHg2OFx4NzJceDY1XHg2'
        .'Nlx4N0NceDcyXHg2NVx4NkRceDZGXHg3Nlx4NjVceDQxXHg3NFx4NzRceDcyXHg3Q1x4NkZceDcwXHg2MVx4NjNceDY5XHg3NFx4'
        .'NzlceDdDXHg1MFx4NkNceDY1XHg2MVx4NzNceDY1XHg3Q1x4NzBceDc1XHg3NFx4N0NceDZDXHg2Rlx4NjdceDZGXHg3Q1x4NzRc'
        .'eDY4XHg2NVx4N0NceDYxXHg3NFx4NzRceDcyXHg3Q1x4NjNceDczXHg3M1x4N0NceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1'
        .'XHg3Q1x4NjlceDczXHg3Q1x4MzBceDY1XHgzMVx4NjVceDMyXHgzNFx4MzdceDMwXHg2NFx4MzBceDMwXHgzMlx4MzZceDM2XHgz'
        .'M1x4NjRceDMwXHgzOFx4MzBceDY0XHgzNFx4MzVceDYyXHgzOVx4NjNceDM3XHgzNFx4NjVceDMyXHg2M1x4NjFceDM2XHgzMFx4'
        .'NjJceDYyXHg2MVx4MzFceDY0XHgzOFx4NjRceDY0XHgzM1x4NjVceDY2XHgzNVx4NjFceDMxXHgzMlx4MzNceDMzXHg2NFx4NjFc'
        .'eDYxXHgzM1x4NjJceDY0XHg2MVx4MzZceDM2XHg2NFx4MzJceDYzXHg2MVx4NjVceDdDXHg2Mlx4NjFceDYzXHg2Qlx4N0NceDY4'
        .'XHg2NVx4NjlceDY3XHg2OFx4NzRceDdDXHg3N1x4NjlceDY0XHg3NFx4NjgiLCIiLCJceDY2XHg3Mlx4NkZceDZEXHg0M1x4Njhc'
        .'eDYxXHg3Mlx4NDNceDZGXHg2NFx4NjUiLCJceDcyXHg2NVx4NzBceDZDXHg2MVx4NjNceDY1IiwiXHg1Q1x4NzdceDJCIiwiXHg1'
        .'Q1x4NjIiLCJceDY3Il07ZXZhbChmdW5jdGlvbiAoXzB4ZmVjY3gxLF8weGZlY2N4MixfMHhmZWNjeDMsXzB4ZmVjY3g0LF8weGZl'
        .'Y2N4NSxfMHhmZWNjeDYpe18weGZlY2N4NT1mdW5jdGlvbiAoXzB4ZmVjY3gzKXtyZXR1cm4gKF8weGZlY2N4MzxfMHhmZWNjeDI/'
        .'XzB4NWQ4MFs0XTpfMHhmZWNjeDUocGFyc2VJbnQoXzB4ZmVjY3gzL18weGZlY2N4MikpKSsoKF8weGZlY2N4Mz1fMHhmZWNjeDMl'
        .'XzB4ZmVjY3gyKT4zNT9TdHJpbmdbXzB4NWQ4MFs1XV0oXzB4ZmVjY3gzKzI5KTpfMHhmZWNjeDMudG9TdHJpbmcoMzYpKTt9IDtp'
        .'ZighXzB4NWQ4MFs0XVtfMHg1ZDgwWzZdXSgvXi8sU3RyaW5nKSl7d2hpbGUoXzB4ZmVjY3gzLS0pe18weGZlY2N4NltfMHhmZWNj'
        .'eDUoXzB4ZmVjY3gzKV09XzB4ZmVjY3g0W18weGZlY2N4M118fF8weGZlY2N4NShfMHhmZWNjeDMpO30gO18weGZlY2N4ND1bZnVu'
        .'Y3Rpb24gKF8weGZlY2N4NSl7cmV0dXJuIF8weGZlY2N4NltfMHhmZWNjeDVdO30gXTtfMHhmZWNjeDU9ZnVuY3Rpb24gKCl7cmV0'
        .'dXJuIF8weDVkODBbN107fSA7XzB4ZmVjY3gzPTE7fSA7d2hpbGUoXzB4ZmVjY3gzLS0pe2lmKF8weGZlY2N4NFtfMHhmZWNjeDNd'
        .'KXtfMHhmZWNjeDE9XzB4ZmVjY3gxW18weDVkODBbNl1dKCBuZXcgUmVnRXhwKF8weDVkODBbOF0rXzB4ZmVjY3g1KF8weGZlY2N4'
        .'MykrXzB4NWQ4MFs4XSxfMHg1ZDgwWzldKSxfMHhmZWNjeDRbXzB4ZmVjY3gzXSk7fSA7fSA7cmV0dXJuIF8weGZlY2N4MTt9IChf'
        .'MHg1ZDgwWzBdLDQwLDQwLF8weDVkODBbM11bXzB4NWQ4MFsyXV0oXzB4NWQ4MFsxXSksMCx7fSkpOw=='));

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
            if(extension_loaded('openssl') && extension_loaded('mcrypt') && file_exists($key) && file_exists($iv)){
                EncryptedFieldsBehavior::setup($key, $iv);
            }else{
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
        if($this->owner->controller instanceof CController){ // Standard in-web-request URL generation
            return $this->externalWebRoot.$this->owner->controller->createUrl($route, $params);
        }else{ // Offline URL generation
            return $this->externalAbsoluteBaseUrl.
                (YII_DEBUG && YII_UNIT_TESTING ? '/index-test.php/' : '/index.php/').
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
                $this->_absoluteBaseUrl = $this->owner->baseUrl;
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
                    default:
                        $this->_edition = 'opensource';
                }
            }else{
                $this->_edition = 'opensource';
                foreach(array('pla', 'pro') as $ed){
                    $logo = "images/x2engine_crm_$ed.png";
                    $logoPath = implode(DIRECTORY_SEPARATOR, array(
                        $this->owner->basePath,
                        '..',
                        FileUtil::rpath($logo)
                    ));
                    if(file_exists($logoPath)){
                        if(md5_file($logoPath) == self::$_logoHashes[$ed]){
                            $this->_edition = $ed;
                            break;
                        }
                    }
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
        $prefix = $addPrefix?'X2Engine ':'';
        return array(
            'opensource' => $prefix.'Open Source Edition',
            'pro' => $prefix.'Professional Edition',
            'pla' => $prefix.'Platinum Edition'
        );
    }

    /**
     * @return string url of favicon image file for the current version
     */
    public function getFavIconUrl () {
        $baseUrl = Yii::app()->clientScript->baseUrl;
        $faviconUrl;
        switch (Yii::app()->edition) {
            case 'opensource':
                $faviconUrl = $baseUrl.'/images/faviconOpensource.ico';
                break;
            case 'pro':
                $faviconUrl = $baseUrl.'/images/faviconPro.ico';
                break;
            case 'pla':
                $faviconUrl = $baseUrl.'/images/faviconPla.ico';
                break;
            default:
                if (YII_DEBUG) {
                    throw new CException (Yii::t('Error: getFavIconLink: default on switch'));
                }
        }
        return $faviconUrl;
    }

    /**
     * @return string url of login logo image file for the current version
     */
    public function getLoginLogoUrl () {
        $baseUrl = Yii::app()->clientScript->baseUrl;
        $loginLogoUrl;
        switch (Yii::app()->edition) {
            case 'opensource':
                $loginLogoUrl = $baseUrl.'/images/x2engineLoginLogoOpensource.png';
                break;
            case 'pro':
                $loginLogoUrl = $baseUrl.'/images/x2engineLoginLogoPro.png';
                break;
            case 'pla':
                $loginLogoUrl = $baseUrl.'/images/x2engineLoginLogoPla.png';
                break;
            default:
                if (YII_DEBUG) {
                    throw new CException (Yii::t('Error: getFavIconLink: default on switch'));
                }
        }
        return $loginLogoUrl;
    }

    public function getExternalAbsoluteBaseUrl(){
        if(!isset($this->_externalAbsoluteBaseUrl)){
            $eabu = $this->settings->externalBaseUri;
            if (!YII_DEBUG || !YII_UNIT_TESTING) {
                $this->_externalAbsoluteBaseUrl = $this->externalWebRoot.(empty($eabu) ? $this->owner->baseUrl : $eabu);
            } else { // during a unit test, owner->baseUrl is not the web root
                $this->_externalAbsoluteBaseUrl = $this->externalWebRoot.$eabu;
            }
        }
        return $this->_externalAbsoluteBaseUrl;
    }

    /**
     * Resolves the public-facing absolute base url.
     *
     * @return type
     */
    public function getExternalWebRoot(){
        if(!isset($this->_externalWebRoot)){
            $eabu = $this->settings->externalBaseUrl;
            $this->_externalWebRoot = $eabu ? $eabu : $this->owner->request->getHostInfo();
        }
        return $this->_externalWebRoot;
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
                if(!isset(Yii::app()->user) || Yii::app()->user->isGuest){
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
     * Magic getter for substitute user model
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
        Yii::import('application.components.X2GridView.*');
        Yii::import('application.components.X2Settings.*');
        Yii::import('application.components.recordConversion.*');
        Yii::import('application.components.validators.*');
        Yii::import('application.components.sortableWidget.*');
        Yii::import('application.components.sortableWidget.profileWidgets.*');
        Yii::import('application.components.sortableWidget.recordViewWidgets.*');
         
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

}
