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
 * ApplicationConfigBehavior is a behavior for the application.
 * It loads additional config paramenters that cannot be statically
 * written in config/main
 *
 * @property string $externalBaseUrl (read-only) the base URL of the web application,
 * 	independent of whether there is a web request.
 * @property integer $suID (read-only) substitute user ID in the case that no user session is available.
 * @property User $suModel Substitute web user model in the case that no user session is available.
 * @package X2CRM.components
 */
class ApplicationConfigBehavior extends CBehavior {

    private $_externalBaseUrl;

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
     * Load dynamic app configuration.
     *
     * Per the onBeginRequest key in the array returned by {@link events()},
     * this method will be called when the request has begun. It allows for
     * many extra configuration tasks to be run on a per-request basis
     * without having to extend {@link Yii} and override its methods.
     */
    public function beginRequest(){
        // $t0 = microtime(true);
        $noSession = $this->owner->params->noSession;
        if(!$noSession){
            if($this->owner->request->getPathInfo() == 'notifications/get'){ // skip all the loading if this is a chat/notification update
                Yii::import('application.components.X2WebUser');
                Yii::import('application.components.X2MessageSource');
                Yii::import('application.components.Formatter');
                Yii::import('application.components.TransformedFieldStorageBehavior');
                Yii::import('application.components.permissions.*');
                if(!$this->owner->user->getIsGuest())
                    $profData = $this->owner->db->createCommand()->select('timeZone, language')->from('x2_profile')->where('id='.$this->owner->user->getId())->queryRow(); // set the timezone to the admin's
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
                $this->owner->params->admin = CActiveRecord::model('Admin')->findByPk(1);
                // Yii::import('application.models.*');
                // foreach(scandir('protected/modules') as $module){
                // if(file_exists('protected/modules/'.$module.'/register.php'))
                // Yii::import('application.modules.'.$module.'.models.*');
                // }
                return;
            }
        } else{
            // Set time zone based on the default value
            date_default_timezone_set(Profile::model()->tableSchema->getColumn('timeZone')->defaultValue);
        }

        // Import directories
        Yii::import('application.models.*');
        Yii::import('application.controllers.X2Controller');
        Yii::import('application.controllers.x2base');
        Yii::import('application.components.*');
        Yii::import('application.components.util.*');
        Yii::import('application.components.permissions.*');
        Yii::import('application.modules.media.models.Media');
        Yii::import('application.modules.groups.models.Groups');
        Yii::import('application.extensions.gallerymanager.models.*');
        // Yii::import('application.components.ERememberFiltersBehavior');
        // Yii::import('application.components.EButtonColumnWithClearFilters');
        // $this->owner->messages->forceTranslation = true;
        $this->owner->messages->onMissingTranslation = array(new TranslationLogger, 'log');
        $this->owner->params->admin = CActiveRecord::model('Admin')->findByPk(1);
        $notGuest = True;
        $uname = 'admin'; // Will always be admin in a console command
        if(!$noSession){
            $uname = $this->owner->user->getName();
            $notGuest = !$this->owner->user->getIsGuest();
            // Set up encryption:
            $key = $this->owner->basePath.'/config/encryption.key';
            $iv = $this->owner->basePath.'/config/encryption.iv';
            if(extension_loaded('openssl') && extension_loaded('mcrypt') && file_exists($key) && file_exists($iv)){
                EncryptedFieldsBehavior::setup($key, $iv);
            }else{
                // Use unsafe method with encryption
                EncryptedFieldsBehavior::setupUnsafe();
            }
        }

        $sessionId = isset($_SESSION['sessionId']) ? $_SESSION['sessionId'] : session_id();

        // Set profile
        $this->owner->params->profile = X2Model::model('Profile')->findByAttributes(array('username' => $uname));
        $session = X2Model::model('Session')->findByPk($sessionId);
        if(isset($this->owner->params->profile)){
            $_SESSION['fullscreen'] = $this->owner->params->profile->fullscreen;
        }


        if(!$noSession){
            if($notGuest && !($this->owner->request->getPathInfo() == 'site/getEvents')){
                $this->owner->user->setReturnUrl($this->owner->request->requestUri);
                if($session !== null){
                    if($session->lastUpdated + $this->owner->params->admin->timeout < time()){
                        SessionLog::logSession($this->owner->user->getName(), $sessionId, 'activeTimeout');
                        $session->delete();
                        $this->owner->user->logout(false);
                    }else{
                        $session->lastUpdated = time();
                        $session->update(array('lastUpdated'));

                        $this->owner->params->sessionStatus = $session->status;
                    }
                }else{
                    $this->owner->user->logout(false);
                }


                $userId = $this->owner->user->getId();
                if(!is_null($userId)){
                    $this->owner->params->groups = Groups::getUserGroups($userId);
                    $this->owner->params->roles = Roles::getUserRoles($userId);

                    $this->owner->params->isAdmin = $this->owner->user->checkAccess('AdminIndex');
                }
            }elseif(!($this->owner->request->getPathInfo() == 'site/getEvents')){
                $guestRole = Roles::model()->findByAttributes(array('name' => 'Guest'));
                if(isset($guestRole))
                    $this->owner->params->roles = array($guestRole->id);
            }
        }

        $modules = $this->owner->modules;
        $arr = array();
        foreach(scandir($this->owner->basePath.'/../protected/modules') as $module){
            if(file_exists("protected/modules/$module/register.php")){
                $arr[$module] = ucfirst($module);
                Yii::import("application.modules.$module.models.*");
            }
        }
        foreach($arr as $key => $module){
            $record = Modules::model()->findByAttributes(array('name' => $key));
            if(isset($record))
                $modules[] = $key;
        }
        $this->owner->setModules($modules);
        $adminProf = X2Model::model('Profile')->findByPk(1);
        $this->owner->params->adminProfile = $adminProf;

        // set currency
        $this->owner->params->currency = $this->owner->params->admin->currency;

        // set language
        if(!empty($this->owner->params->profile->language))
            $this->owner->language = $this->owner->params->profile->language;
        else if(isset($adminProf))
            $this->owner->language = $adminProf->language;
        else
            $this->owner->language = '';

        $locale = $this->owner->locale;
        $curSyms = array();
        foreach($this->owner->params->supportedCurrencies as $curCode){
            $curSyms[$curCode] = $locale->getCurrencySymbol($curCode);
        }
        $this->owner->params->supportedCurrencySymbols = $curSyms; // Code to symbol
        // set timezone
        if(!empty($this->owner->params->profile->timeZone))
            date_default_timezone_set($this->owner->params->profile->timeZone);
        elseif(!empty($adminProf->timeZone))
            date_default_timezone_set($adminProf->timeZone);
        else
            date_default_timezone_set('UTC');

        $logo = Media::model()->findByAttributes(array('associationId' => 1, 'associationType' => 'logo'));
        if(isset($logo))
            $this->owner->params->logo = $logo->fileName;
        

        // set edition
        if(YII_DEBUG){
            if(PRO_VERSION)
                $this->owner->params->edition = 'pro';
            else
                $this->owner->params->edition = 'opensource';
        } else{
            $this->owner->params->edition = $this->owner->params->admin->edition;
        }

        if($this->owner->params->edition === 'pro'){
            $proLogo = 'images/x2engine_crm_pro.png';
            if(!file_exists($proLogo) || hash_file('md5', $proLogo) !== '31a192054302bc68e1ed5a59c36ce731')
                $this->owner->params->edition = 'opensource';
        }

        setlocale(LC_ALL, 'en_US.UTF-8');

        // set base path and theme path globals for JS
        if(!$noSession){
            if($notGuest){
                $profile = $this->owner->params->profile;
                if(isset($profile)){
                    $where = 'fileName = "'.$profile->notificationSound.'"';
                    $uploadedBy = $this->owner->db->createCommand()->select('uploadedBy')->from('x2_media')->where($where)->queryRow();
                    if(!empty($uploadedBy['uploadedBy'])){
                        $notificationSound = $this->owner->baseUrl.'/uploads/media/'.$uploadedBy['uploadedBy'].'/'.$profile->notificationSound;
                    }else{
                        $notificationSound = $this->owner->baseUrl.'/uploads/'.$profile->notificationSound;
                    }
                    $yiiString = '
                    var	yii = {
                        baseUrl: "'.$this->owner->baseUrl.'",
                        scriptUrl: "'.$this->owner->request->scriptUrl.'",
                        themeBaseUrl: "'.$this->owner->theme->baseUrl.'",
                        language: "'.($this->owner->language == 'en' ? '' : $this->owner->getLanguage()).'",
                        datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
                        timePickerFormat: "'.Formatter::formatTimePicker().'",
                        profile: '.CJSON::encode($this->owner->params->profile->getAttributes()).',
                        notificationSoundPath: "'.$notificationSound.'"
                    },
                    x2 = {};
					x2.DEBUG = '.(YII_DEBUG ? 'true' : 'false').';
                    x2.notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
                    ';
                }else{
                    $yiiString = '
                var	yii = {
                    baseUrl: "'.$this->owner->baseUrl.'",
                    scriptUrl: "'.$this->owner->request->scriptUrl.'",
                    themeBaseUrl: "'.$this->owner->theme->baseUrl.'",
                    language: "'.($this->owner->language == 'en' ? '' : $this->owner->getLanguage()).'",
                    datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
                    timePickerFormat: "'.Formatter::formatTimePicker().'"
                },
                x2 = {};
				x2.DEBUG = '.(YII_DEBUG ? 'true' : 'false').';
                x2.notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
                ';
                }
            }else{
                $yiiString = '
			var	yii = {
				baseUrl: "'.$this->owner->baseUrl.'",
				scriptUrl: "'.$this->owner->request->scriptUrl.'",
				themeBaseUrl: "'.$this->owner->theme->baseUrl.'",
				language: "'.($this->owner->language == 'en' ? '' : $this->owner->getLanguage()).'",
				datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
				timePickerFormat: "'.Formatter::formatTimePicker().'"
			},
			x2 = {};
			x2.DEBUG = '.(YII_DEBUG ? 'true' : 'false').';
			x2.notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
			';
            }

            $userAgentStr = strtolower($this->owner->request->userAgent);
            $isAndroid = preg_match('/android/', $userAgentStr);
            if($isAndroid){
                define('IS_ANDROID', true);
                $yiiString .= '
					x2.isAndroid = true;
				';
            }else{
                define('IS_ANDROID', false);
                $yiiString .= '
					x2.isAndroid = false;
				';
            }
            $isIPad = preg_match('/ipad/', $userAgentStr);
            if($isIPad){
                define('IS_IPAD', true);
                $yiiString .= '
					x2.isIPad = true;
				';
            }else{
                define('IS_IPAD', false);
                $yiiString .= '
					x2.isIPad = false;
				';
            }

            $this->owner->clientScript->registerScript('setParams', $yiiString, CClientScript::POS_HEAD);
            $cs = $this->owner->clientScript;
            $baseUrl = $this->owner->request->baseUrl;
            $jsVersion = '?'.$this->owner->params->buildDate;
            /* $cs->scriptMap=array(
              'backgroundImage.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'json2.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'layout.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'media.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'modernizr.custom.66175.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'publisher.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'relationships.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'tags.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'translator.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'widgets.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              'x2forms.js'=>$baseUrl.'/js/all.min.js'.$jsVersion,
              ); */
        }
    }

    /**
     * Magic getter for {@link externalBaseUrl}; in the case that web request data
     * isn't available, it uses a config file.
     *
     * @return type
     */
    public function getExternalBaseUrl(){
        if(!isset($this->_externalBaseUrl)){
            if($this->owner->params->noSession){
                $this->_externalBaseUrl = '';
                // Use the web API config file to construct the URL
                $file = realpath($this->owner->basePath.'/../webLeadConfig.php');
                if($file){
                    include($file);
                    if(isset($url))
                        $this->_externalBaseUrl = $url;
                }
                if(!isset($this->_externalBaseUrl)){
                    $this->_externalBaseUrl = ''; // Default
                    if($this->owner->hasProperty('request')){
                        // If this is an API request, there is still hope yet to resolve it
                        try{
                            $this->_externalBaseUrl = $this->owner->request->baseUrl;
                        }catch(Exception $e){

                        }
                    }
                }
            }else{
                $this->_externalBaseUrl = $this->owner->baseUrl;
            }
        }
        return $this->_externalBaseUrl;
    }

	/**
	 * Substitute user ID magic getter.
	 *
	 * If the user has already been looked up or set, method will defer to its
	 * value for id.
	 * @return type
	 */
	public function getSuID(){
        if(!isset($this->_suID)){
            if($this->isInSession){
                $this->_suID = $this->owner->user->getId();
            }elseif(isset($this->_suModel)){
                $this->_suID = $this->_suModel->id;
            }else{
                $this->_suID = 1;
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
            if(!$app->params->hasProperty('noSession')){
                $this->_isInSession = true;
            }else{
                if(!isset(Yii::app()->user) || Yii::app()->user->isGuest){
                    $app->params->noSession=true;
                }
                $this->_isInSession = !$app->params->noSession;
            }
        }
        return $this->_isInSession;
    }
    
    /**
     * Substitute user model magic getter.
     *
     * @return User
     */
    public function getSuModel(){
        if(!isset($this->_suModel)){
            if($this->isInSession)
                $this->_suID == $this->owner->user->getId();
            $this->_suModel = User::model()->findByPk($this->suID);
        }
        return $this->_suModel;
    }

    /**
     * Magic getter for substitute user model
     * @param User $user
     */
    public function setSuModel(User $user){
        $this->_suModel = $user;
    }

}
