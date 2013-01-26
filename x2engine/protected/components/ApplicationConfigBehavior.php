<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 ********************************************************************************/

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
	public function events() {
		return array_merge(parent::events(), array(
			'onBeginRequest'=>'beginRequest',
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
	public function beginRequest() {
		// $t0 = microtime(true);
        $cli = $this->owner->params->isCli;
		if (!$cli) {
			if ($this->owner->request->getPathInfo() == 'notifications/get') { // skip all the loading if this is a chat/notification update
				$timezone = $this->owner->db->createCommand()->select('timeZone')->from('x2_profile')->where('id=1')->queryScalar(); // set the timezone to the admin's
				if (!isset($timezone))
					$timezone = 'UTC';
				date_default_timezone_set($timezone);
                Yii::import('application.models.X2Model');
				// Yii::import('application.models.*');
				// foreach(scandir('protected/modules') as $module){
				// if(file_exists('protected/modules/'.$module.'/register.php'))
				// Yii::import('application.modules.'.$module.'.models.*');
				// }

				return;
			}
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
		$this->owner->messages->onMissingTranslation = array(new TranslationLogger,'log');
		$this->owner->params->admin = CActiveRecord::model('Admin')->findByPk(1);
		$notGuest = True;
		$uname = 'admin';
		if (!$cli) {
			$uname = $this->owner->user->getName();
			$notGuest = !$this->owner->user->isGuest;
		}
		
		$sessionId = isset($_SESSION['sessionId'])? $_SESSION['sessionId'] : session_id();
		
		
		$this->owner->params->profile = CActiveRecord::model('Profile')->findByAttributes(array('username'=>$uname));
		$session = CActiveRecord::model('Session')->findByPk($sessionId);

		
		if($notGuest && !$cli) {
			if($session !== null) {
				if($session->lastUpdated + $this->owner->params->admin->timeout < time()) {
					$session->delete();
					$this->owner->user->logout();
				} else {
					$session->lastUpdated = time();
					$session->update(array('lastUpdated'));
					
					$this->owner->params->sessionStatus = $session->status;
				}
			} else {
				$this->owner->user->logout();
			}
			
			
			$userId = $this->owner->user->getId();
			if(!is_null($userId)) {
				$this->owner->params->groups = Groups::getUserGroups($userId);
				$this->owner->params->roles = Roles::getUserRoles($userId);
			}
		}
		
		$modules=$this->owner->modules;
		$arr=array();
		foreach(scandir($this->owner->basePath.'/../protected/modules') as $module){
			if(file_exists("protected/modules/$module/register.php")){
				$arr[$module]=ucfirst($module);
				Yii::import("application.modules.$module.models.*");
			}
		}
		foreach($arr as $key=>$module){
			$record=Modules::model()->findByAttributes(array('name'=>$key));
			if(isset($record))
				$modules[]=$key;
		}
		$this->owner->setModules($modules);
		$adminProf = ProfileChild::model()->findByPk(1);

		// set currency
		$this->owner->params->currency = $this->owner->params->admin->currency;
		
		// set language
		if (!empty($this->owner->params->profile->language))
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
			
		$logo = Media::model()->findByAttributes(array('associationId'=>1,'associationType'=>'logo'));
		if(isset($logo))
			$this->owner->params->logo = $logo->fileName;
			
			
		// set edition
		$this->owner->params->edition = $this->owner->params->admin->edition;
		
		if($this->owner->params->edition === 'pro') {
			$proLogo = 'images/x2engine_crm_pro.png';
			if(!file_exists($proLogo) || hash_file('md5',$proLogo) !== '31a192054302bc68e1ed5a59c36ce731')
				$this->owner->params->edition = 'opensource';
		}
		
		setlocale(LC_ALL, 'en_US.UTF-8');
		
		$datePickerFormat = Yii::app()->locale->getDateFormat('short'); // translate Yii date format to jquery
		$datePickerFormat = str_replace('yy', 'y', $datePickerFormat);
		$datePickerFormat = str_replace('MM', 'mm', $datePickerFormat);
		$datePickerFormat = str_replace('M', 'm', $datePickerFormat);
		
		// set base path and theme path globals for JS
		if(!$cli)
			Yii::app()->clientScript->registerScript('setParams','
			var	yii = {
				baseUrl: "'.Yii::app()->baseUrl.'",
				scriptUrl: "'.Yii::app()->request->scriptUrl.'",
				themeBaseUrl: "'.Yii::app()->theme->baseUrl.'",
				language: "'.(Yii::app()->language == 'en'? '' : Yii::app()->getLanguage()).'",
				datePickerFormat: "'.$datePickerFormat.'",
				timePickerFormat: "'.Yii::app()->formatTimePicker().'"
			},
			x2 = {},
			notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
			', CClientScript::POS_HEAD);
	}
}
