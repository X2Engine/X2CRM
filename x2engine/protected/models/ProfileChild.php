<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
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
	
	public static function getSocialMedia() {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preference for contact social media info
		return $model->showSocialMedia;
	}
	
	public static function getResultsPerPage() {
		$model = ProfileChild::model()->findByPk(Yii::app()->user->getId());	// get user's preferred results per page
		$resultsPerPage = $model->resultsPerPage;
		
		return empty($resultsPerPage)? 15 : $resultsPerPage;
	}
	
	public function getWidgets() {
		
		$model = ProfileChild::model('ProfileChild')->findByPk(Yii::app()->user->getId());
		
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


















