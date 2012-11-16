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

if(Yii::app()->params->admin->googleIntegration) { // menu if google integration is enables has additional options
	if(Yii::app()->user->checkAccess('AdminIndex')) {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar'), 'url'=>array('index')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
			array('label'=>Yii::t('calendar', 'User Calendar Permissions')),
//			array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//			array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
			array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
		);
	} else {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar'), 'url'=>array('index')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
//			array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//			array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
			array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
		);
	}
} else {
	if(Yii::app()->user->checkAccess('AdminIndex')) {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar'), 'url'=>array('index')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
			array('label'=>Yii::t('calendar', 'User Calendar Permissions')),
//			array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//			array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
		);
	} else {
		$menuItems = array(
			array('label'=>Yii::t('calendar', 'Calendar'), 'url'=>array('index')),
			array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
//			array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//			array('label'=>Yii::t('calendar', 'Create'), 'url'=>array('create')),
		);
	}
}
$this->actionMenu = $this->formatMenu($menuItems);
?>

<script type="text/javascript">
function giveSaveButtonFocus() {
$('#save-button')
    .css('background', '')
    .css('color', '');
$('#save-button')
    .css('background', '#579100')
    .css('color', 'white')
    .focus();
}
</script>

<?php

$users = User::model()->findAll(array(
	'select'=>'id, username, firstName, lastName, CONCAT(firstName," ",lastName) AS fullname', 
	'index'=>'id',
	'order'=>'fullname ASC',

));

if(isset($id)) {

	$this->beginWidget('CActiveForm', array(
		'id'=>'user-permission-form',
		'enableAjaxValidation'=>false,
	));

	Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/multiselect/js/ui.multiselect.js');
	Yii::app()->clientScript->registerCssFile(Yii::app()->getBaseUrl().'/js/multiselect/css/ui.multiselect.css','screen, projection');
	Yii::app()->clientScript->registerCss('userPermissionCss',"
	.user-permission {
		width: 460px;
		height: 200px;
	}
	#switcher {
		margin-top: 20px;
	}
	",'screen, projection');
	Yii::app()->clientScript->registerScript('userCalendarPermission', "
	$(function() {
		$('.user-permission').multiselect();
		$('.ui-icon').click(function() {
			giveSaveButtonFocus();
		});
	});
	",CClientScript::POS_HEAD);
	
	$names = array();
	foreach($users as $user)
		if($user->username != 'admin' && $user->id != $id)
			$names[$user->id] = $user->firstName . ' ' . $user->lastName;
			
	$viewPermission = X2CalendarPermissions::getUserIdsWithViewPermission($id);
	$editPermission = X2CalendarPermissions::getUserIdsWithEditPermission($id);
	
	$first = $users[$id]->firstName;
	$last = $users[$id]->lastName;
	$fullname = $first . ' ' . $last;
	
	echo CHtml::hiddenField('user-id', $id); // save user id for POST
	
	echo "<h2>" . Yii::t('calendar', 'View Permission') . "</h2>";
	echo "These users can view $fullname's calendar.";
	echo CHtml::listBox('view-permission', $viewPermission, $names, array(
		'class'=>'user-permission',
		'multiple'=>'multiple',
		'onChange'=>'giveSaveButtonFocus();',
	));
	echo "<br>\n";
	
	echo "<h2>" . Yii::t('calendar', 'Edit Permission') . "</h2>";
	echo "These users can edit $fullname's calendar.";
	echo CHtml::listBox('edit-permission', $editPermission, $names, array(
		'class'=>'user-permission',
		'multiple'=>'multiple',
		'onChange'=>'giveSaveButtonFocus();',
	));
	
	echo '	<div class="row buttons">'."\n";
	echo '		'.CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button', 'name'=>'save-button', 'tabindex'=>24, 'style'=>'display: inline-block'))."\n";
	echo '		'.CHtml::link(Yii::t('calendar', 'Back To User List'), $this->createUrl(''), array('class'=>'x2-button'));
	echo "	</div>\n";
	
	$this->endWidget();

} else {
	foreach($users as $user) {
			echo CHtml::link($user->firstName . ' ' . $user->lastName, $this->createUrl('', array('id'=>$user->id)));
			echo "<br>\n";
	}
}

?>
