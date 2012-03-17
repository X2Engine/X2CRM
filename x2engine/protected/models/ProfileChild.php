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
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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

class ProfileChild extends Profile {

	public function attributeLabels() {
		return array(
			'id'=>Yii::t('profile','ID'),
			'fullName'=>Yii::t('profile','Full Name'),
			'username'=>Yii::t('profile','Username'),
			'officePhone'=>Yii::t('profile','Office Phone'),
			'cellPhone'=>Yii::t('profile','Cell Phone'),
			'emailAddress'=>Yii::t('profile','Email Address'),
			'notes'=>Yii::t('profile','Notes'),
			'status'=>Yii::t('profile','Status'),
			'tagLine'=>Yii::t('profile','Tag Line'),
			'lastUpdated'=>Yii::t('profile','Last Updated'),
			'updatedBy'=>Yii::t('profile','Updated By'),
			'avatar'=>Yii::t('profile','Avatar'),
			'allowPost'=>Yii::t('profile','Allow users to post on your profile?'),
			'language'=>Yii::t('profile','Language'),
			'timeZone'=>Yii::t('profile','Time Zone'),
			'widgets'=>Yii::t('profile','Enable group chat?'),
			'menuBgColor'=>Yii::t('profile','Menu Color'),
			'resultsPerPage'=>Yii::t('profile','Results Per Page'),
			'menuTextColor'=>Yii::t('profile','Menu Text Color'),
			'backgroundColor'=>Yii::t('profile','Background Color'),
			'pageOpacity'=>Yii::t('profile','Page Opacity'),
			'startPage'=>Yii::t('profile','Start Page'),
			'showSocialMedia'=>Yii::t('profile','Show Social Media'),
			'showDetailView'=>Yii::t('profile','Show Detail View'),
			'showDetailView'=>Yii::t('profile','Show Detail View'),
			'showWorkflow'=>Yii::t('profile','Show Workflow'),
			'gridviewSettings'=>Yii::t('profile','Gridview Settings'),
			'formSettings'=>Yii::t('profile','Form Settings'),
			'emailUseSignature' => Yii::t('admin','Email Signature'),
			'emailSignature' => Yii::t('admin','My Signature'),
			'enableBgFade'=>Yii::t('profile','Enable Background Fading'),
		);
	}
	
	public function behaviors() {
		return array(
			'ERememberFiltersBehavior' => array(
				'class' => 'application.components.ERememberFiltersBehavior',
				'defaults'=>array(),		/* optional line */
				'defaultStickOnClear'=>false	/* optional line */
			),
		);
	}
	
	public static function setDetailView($value) {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// set user's preference for contact detail view
		$model->showDetailView = ($value == 1)? 1 : 0;
		$model->save();
	}
	
	public static function getDetailView() {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preference for contact detail view
		return $model->showDetailView;
	}

	// public static function getSocialMedia() {
		// $model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preference for contact social media info
		// return $model->showSocialMedia;
	// }
	
	public function getSignature($html = false) {
		
		$adminRule = Yii::app()->params->admin->emailUseSignature;
		$userRule = $this->emailUseSignature;
		
		$userModel = CActiveRecord::model('UserChild')->findByPk($this->id);
		$signature = '';
		
		switch($adminRule) {
			case 'admin': $signature = Yii::app()->params->admin->emailSignature; break;
			case 'user':
				switch($userRule) {
					case 'user': $signature = $signature = $this->emailSignature; break;
					case 'admin': Yii::app()->params->admin->emailSignature; break;
					case 'group': $signature == ''; break;
					default: $signature == '';
				}
				break;
			case 'group': $signature == ''; break;
			default: $signature == '';
		}
		
		
		$signature = preg_replace(
			array(
				'/\{first\}/',
				'/\{last\}/',
				'/\{phone\}/',
				'/\{group\}/',
				'/\{email\}/',
			),
			array(
				$userModel->firstName,
				$userModel->lastName,
				$this->officePhone,
				'',
				$html? CHtml::mailto($this->emailAddress) : $this->emailAddress,
			),
			$signature
		);
		if($html)
			$signature = '<span style="color:grey;">' . x2base::convertLineBreaks($signature) . '</span>';
			
		return $signature;
	}
	
	public static function getResultsPerPage() {
	
		$resultsPerPage = Yii::app()->params->profile->resultsPerPage;
		// $model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preferred results per page
		// $resultsPerPage = $model->resultsPerPage;
		
		return empty($resultsPerPage)? 15 : $resultsPerPage;
	}
	
	// lookup user's settings for a gridview (visible columns, column widths)
	public static function getGridviewSettings($viewName = null) {
		$gvSettings = json_decode(Yii::app()->params->profile->gridviewSettings,true);	// converts JSON string to assoc. array
		if(isset($viewName)) {
			$viewName = strtolower($viewName);
			if(isset($gvSettings[$viewName]))
				return $gvSettings[$viewName];
			else
				return null;
		} else {
			return $gvSettings;
		}
	}
	// add/update settings for a specific gridview, or save all at once
	public static function setGridviewSettings($gvSettings,$viewName = null) {
		if(isset($viewName)) {
			$fullGvSettings = ProfileChild::getGridviewSettings();
			$fullGvSettings[strtolower($viewName)] = $gvSettings;
			Yii::app()->params->profile->gridviewSettings = json_encode($fullGvSettings);	// encode array in JSON
		} else {
			Yii::app()->params->profile->gridviewSettings = json_encode($gvSettings);	// encode array in JSON
		}
		return Yii::app()->params->profile->save();
	}
	
	// lookup user's settings for a gridview (visible columns, column widths)
	public static function getFormSettings($formName = null) {
		$formSettings = json_decode(Yii::app()->params->profile->formSettings,true);	// converts JSON string to assoc. array
		if(isset($formName)) {
			$formName = strtolower($formName);
			if(isset($formSettings[$formName]))
				return $formSettings[$formName];
			else
				return null;
		} else {
			return $formSettings;
		}
	}
	// add/update settings for a specific form, or save all at once
	public static function setFormSettings($formSettings,$formName = null) {
		if(isset($formName)) {
			$fullFormSettings = ProfileChild::getFormSettings();
			$fullFormSettings[strtolower($formName)] = $formSettings;
			Yii::app()->params->profile->formSettings = json_encode($fullFormSettings);	// encode array in JSON
		} else {
			Yii::app()->params->profile->formSettings = json_encode($formSettings);	// encode array in JSON
		}
		return Yii::app()->params->profile->save();
	}

	
	public static function getWidgets() {
		
		if(Yii::app()->user->isGuest)	// no widgets if the user isn't logged in
			return array();
		// $model = ProfileChild::model('ProfileChild')->findByPk(Yii::app()->user->getId());
		$model = Yii::app()->params->profile;
		
		$registeredWidgets = array_keys(Yii::app()->params->registeredWidgets);
		
		$widgetNames = ($model->widgetOrder=='')? array() : explode(":",$model->widgetOrder);
		$visibility = ($model->widgets=='')? array() : explode(":",$model->widgets);
		
		$widgetList = array();
		
		$updateRecord = false;
		
		for($i=0;$i<count($widgetNames);$i++) {
		
			if(!in_array($widgetNames[$i],$registeredWidgets)) {	// check the main cfg file
				unset($widgetNames[$i]);							// if widget isn't listed,
				unset($visibility[$i]);								// remove it from database fields
				$updateRecord = true;
			} else {
				$widgetList[$widgetNames[$i]] = array('id'=>'widget_'.$widgetNames[$i],'visibility'=>$visibility[$i]);
			}
		}

		foreach($registeredWidgets as $class) {			// check list of widgets in main cfg file
			if(!in_array($class,array_keys($widgetList))) {								// if they aren't in the list,
				$widgetList[$class] = array('id'=>'widget_'.$class,'visibility'=>1);	// add them at the bottom
				
				$widgetNames[] = $class;	// add new widgets to widgetOrder array
				$visibility[] = 1;			// and visibility array
				
				$updateRecord = true;
			}
		}

		if($updateRecord) {
			$model->widgetOrder = implode(':',$widgetNames);	// update database fields
			$model->widgets = implode(':',$visibility);			// if there are new widgets
			$model->save();
		}
		
		return $widgetList;
	}
}


















