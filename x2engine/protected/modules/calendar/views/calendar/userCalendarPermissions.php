<?php
/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
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

if(Yii::app()->params->admin->googleIntegration) { // menu if google integration is enables has additional options
	if(Yii::app()->params->isAdmin) {
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
	if(Yii::app()->params->isAdmin) {
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
	?>
	
	<div class="page-title"><h2><?php echo Yii::t('calendar', 'View Permission'); ?></h2></div>
	<div class="form">
		<?php echo Yii::t('calendar', "These users can view {fullname}'s calendar.", array ('{fullname}' => $fullname)); ?>
		<?php
		echo CHtml::listBox('view-permission', $viewPermission, $names, array(
			'class'=>'user-permission',
			'multiple'=>'multiple',
			'onChange'=>'giveSaveButtonFocus();',
		));
		?>
		<br>
	</div>
	<div class="page-title rounded-top"><h2><?php echo Yii::t('calendar', 'Edit Permission'); ?></h2></div>
	<div class="form">
		<?php echo Yii::t('calendar', "These users can edit {fullname}'s calendar.", array ('{fullname}' => $fullname)); ?>
		<?php
		echo CHtml::listBox('edit-permission', $editPermission, $names, array(
			'class'=>'user-permission',
			'multiple'=>'multiple',
			'onChange'=>'giveSaveButtonFocus();',
		));
		?>
		<br>
		<div class="row buttons">
			<?php echo CHtml::submitButton(Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button', 'name'=>'save-button', 'tabindex'=>24)); ?>
		    <?php echo CHtml::link(Yii::t('calendar', 'Back To User List'), $this->createUrl(''), array('class'=>'x2-button')); ?>
		</div>
	</div>
	<?php
$this->endWidget();
	?>

	<?php
} else {
	?>
	<div class="page-title"><h2><?php echo Yii::t('calendar', 'User Calendar Permissions'); ?></h2></div>
	<div style="padding: 8px">
	<?php
	foreach($users as $user) {
			echo CHtml::link($user->firstName . ' ' . $user->lastName, $this->createUrl('', array('id'=>$user->id)));
			echo "<br>\n";
	}
	?>
	</div>
	<?php
}

?>
