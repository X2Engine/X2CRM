<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
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
        
		if($this->owner->request->getPathInfo() == 'notifications/get') {	// skip all the loading if this is a chat/notification update
			$timezone = $this->owner->db->createCommand()->select('timeZone')->from('x2_profile')->where('id=1')->queryScalar();	// set the timezone to the admin's
			if(!isset($timezone))
				$timezone = 'UTC';
			date_default_timezone_set($timezone);
			
			// Yii::import('application.models.*');
			// foreach(scandir('protected/modules') as $module){
				// if(file_exists('protected/modules/'.$module.'/register.php'))
					// Yii::import('application.modules.'.$module.'.models.*');
			// }
			
			return;
		}
		Yii::import('application.models.*');
		Yii::import('application.controllers.X2Controller');
		Yii::import('application.controllers.x2base');
		Yii::import('application.components.*');
		// Yii::import('application.components.ERememberFiltersBehavior');
		// Yii::import('application.components.EButtonColumnWithClearFilters');


		// $this->owner->messages->forceTranslation = true;
		$this->owner->messages->onMissingTranslation = array(new TranslationLogger,'log');

		$this->owner->params->admin = CActiveRecord::model('Admin')->findByPk(1);
		$this->owner->params->profile = CActiveRecord::model('Profile')->findByAttributes(array('username'=>$this->owner->user->getName()));
		
		// die( var_dump($this->owner->request->getPathInfo())); //->getRoute();
		if(!$this->owner->user->isGuest) {
			
			// use the admin's profile as default
			$this->owner->params->profile = CActiveRecord::model('Profile')->findByAttributes(array('username'=>$this->owner->user->getName()));
		
			$session = Session::model()->findByAttributes(array('user'=>$this->owner->user->getName()));
			if(isset($session)) {
				if($session->lastUpdated + $this->owner->params->admin->timeout < time() ) {
					$session->delete();
					$this->owner->user->logout();
				} else if($this->owner->request->getPathInfo() != 'site/checkNotifications') {
					$session->lastUpdated = time();
					$session->save();
				}
			} else {
				$this->owner->user->logout();
				// $this->redirect(Yii::app()->controller->createUrl('site/logout'));
			}
			if(!is_null($this->owner->user->getId()) && $this->owner->user->getName()!='admin'){
				$this->owner->params->roles = $this->owner->db->createCommand()	// lookup the user's roles
						->select('roleId')
						->from('x2_role_to_user')
						->where('type="user" AND userId='.$this->owner->user->getId())->queryColumn();

				$this->owner->params->groups = $this->owner->db->createCommand()		// lookup the user's groups
						->select('groupId')
						->from('x2_group_to_user')
						->where('userId='.$this->owner->user->getId())->queryColumn();
						
				$groupRoles = Yii::app()->db->createCommand()
					->select('x2_role_to_user.roleId')
					->from('x2_group_to_user')
					->join('x2_role_to_user','x2_role_to_user.userId=x2_group_to_user.groupId AND x2_group_to_user.userId="'.Yii::app()->user->getId().'" AND type="group"')
					->queryColumn();
				// foreach($this->owner->params->groups as $groupId) {		// lookup roles for all the user's groups
						// $groupRoles += $this->owner->db->createCommand()
						// ->select('roleId')
						// ->from('x2_role_to_user')
						// ->where('type="group" AND userId='.$groupId)->queryColumn();
				// }
				$this->owner->params->roles = array_unique($this->owner->params->roles + $groupRoles);		// combine all the roles, remove duplicates
			}
		}
		
		$modules=$this->owner->modules;
		$arr=array();
		foreach(scandir('protected/modules') as $module){
			if(file_exists("protected/modules/$module/register.php")){
				$arr[$module]=ucfirst($module);
				Yii::import("application.modules.$module.models.*");
			}
            
		}
		foreach($arr as $key=>$module){
			$record=Modules::model()->findByAttributes(array('name'=>$key));
			if(isset($record) && $record->visible)
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
			
		$proLogo = 'images/x2engine_crm_pro.png';
		if(file_exists($proLogo) && hash_file('md5',$proLogo) == 'fffc90810b8e9b65b6097119ba74fb93')
			$this->owner->params->edition = 'pro';
			
		setlocale(LC_ALL, 'en_US.UTF-8');

		// set base path and theme path globals for JS
		Yii::app()->clientScript->registerScript('setParams','
		var	yii = {
			baseUrl: "'.Yii::app()->baseUrl.'",
			themeBaseUrl: "'.Yii::app()->theme->baseUrl.'"
		},
		notifUpdateInterval = '.$this->owner->params->admin->chatPollTime.';
		', CClientScript::POS_HEAD);
	}
}
