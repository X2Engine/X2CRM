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
 * @package X2CRM.components
 */
class ApplicationConfigBehavior extends CBehavior {

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
                Yii::import('application.components.Formatter');
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
                // Yii::import('application.models.*');
                // foreach(scandir('protected/modules') as $module){
                // if(file_exists('protected/modules/'.$module.'/register.php'))
                // Yii::import('application.modules.'.$module.'.models.*');
                // }
                return;
            }
        } else{
            // Set time zone
            date_default_timezone_set(Profile::model()->tableSchema->getColumn('timeZone')->defaultValue);
        }
        Yii::import('application.models.*');
        Yii::import('application.controllers.X2Controller');
        Yii::import('application.controllers.x2base');
        Yii::import('application.components.*');
        Yii::import('application.modules.media.models.Media');
        Yii::import('application.modules.groups.models.Groups');
        // Yii::import('application.components.ERememberFiltersBehavior');
        // Yii::import('application.components.EButtonColumnWithClearFilters');
        // $this->owner->messages->forceTranslation = true;
        $this->owner->messages->onMissingTranslation = array(new TranslationLogger, 'log');
        $this->owner->params->admin = CActiveRecord::model('Admin')->findByPk(1);
        $notGuest = True;
        $uname = 'admin';
        if(!$noSession){
            $uname = $this->owner->user->getName();
            $notGuest = !$this->owner->user->isGuest;
        }

        $sessionId = isset($_SESSION['sessionId']) ? $_SESSION['sessionId'] : session_id();


        $this->owner->params->profile = CActiveRecord::model('Profile')->findByAttributes(array('username' => $uname));
        $session = X2Model::model('Session')->findByPk($sessionId);
        if(isset($this->owner->params->profile)){
            $_SESSION['fullscreen'] = $this->owner->params->profile->fullscreen;
        }


        if($notGuest && !$noSession && !($this->owner->request->getPathInfo() == 'site/getEvents')){
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

                $this->owner->params->isAdmin = $this->owner->user->checkAccess('AdminIndex', $userId);
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
        $adminProf = ProfileChild::model()->findByPk(1);

        // set currency
        $this->owner->params->currency = $this->owner->params->admin->currency;

        // set language
        if(!empty($this->owner->params->profile->language))
            $this->owner->language = $this->owner->params->profile->language;
        else if(isset($adminProf))
            $this->owner->language = $adminProf->language;
        else
            $this->owner->language = '';

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
        $this->owner->params->edition = $this->owner->params->admin->edition;

        if($this->owner->params->edition === 'pro'){
            $proLogo = 'images/x2engine_crm_pro.png';
            if(!file_exists($proLogo) || hash_file('md5', $proLogo) !== '31a192054302bc68e1ed5a59c36ce731')
                $this->owner->params->edition = 'opensource';
        }

        setlocale(LC_ALL, 'en_US.UTF-8');

        // set base path and theme path globals for JS
        if($notGuest){
            $profile = X2Model::model('ProfileChild')->findByPk(Yii::app()->user->getId());
            if(isset($profile)){
                $where = 'fileName = "'.$profile->notificationSound.'"';
                $uploadedBy = Yii::app()->db->createCommand()->select('uploadedBy')->from('x2_media')->where($where)->queryRow();
                if(!empty($uploadedBy['uploadedBy'])){
                    $notificationSound = Yii::app()->baseUrl.'/uploads/media/'.$uploadedBy['uploadedBy'].'/'.$profile->notificationSound;
                }else{
                    $notificationSound = Yii::app()->baseUrl.'/uploads/'.$profile->notificationSound;
                }
                $yiiString = '
                    var	yii = {
                        baseUrl: "'.Yii::app()->baseUrl.'",
                        scriptUrl: "'.Yii::app()->request->scriptUrl.'",
                        themeBaseUrl: "'.Yii::app()->theme->baseUrl.'",
                        language: "'.(Yii::app()->language == 'en' ? '' : Yii::app()->getLanguage()).'",
                        datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
                        timePickerFormat: "'.Formatter::formatTimePicker().'",
                        profile: "'.addslashes(CJSON::encode(Yii::app()->params->profile->attributes)).'",
                        notificationSoundPath: "'.$notificationSound.'"
                    },
                    x2 = {},
                    notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
                    ';
            }else{
                $yiiString = '
                var	yii = {
                    baseUrl: "'.Yii::app()->baseUrl.'",
                    scriptUrl: "'.Yii::app()->request->scriptUrl.'",
                    themeBaseUrl: "'.Yii::app()->theme->baseUrl.'",
                    language: "'.(Yii::app()->language == 'en' ? '' : Yii::app()->getLanguage()).'",
                    datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
                    timePickerFormat: "'.Formatter::formatTimePicker().'"
                },
                x2 = {},
                notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
                ';
            }
        }else{
            $yiiString = '
			var	yii = {
				baseUrl: "'.Yii::app()->baseUrl.'",
				scriptUrl: "'.Yii::app()->request->scriptUrl.'",
				themeBaseUrl: "'.Yii::app()->theme->baseUrl.'",
				language: "'.(Yii::app()->language == 'en' ? '' : Yii::app()->getLanguage()).'",
				datePickerFormat: "'.Formatter::formatDatePicker('medium').'",
				timePickerFormat: "'.Formatter::formatTimePicker().'"
			},
			x2 = {},
			notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
			';
        }
        if(!$noSession){
            Yii::app()->clientScript->registerScript('setParams', $yiiString, CClientScript::POS_HEAD);
            $cs = Yii::app()->clientScript;
            $baseUrl = Yii::app()->request->baseUrl;
            $jsVersion = '?'.Yii::app()->params->buildDate;
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

}
