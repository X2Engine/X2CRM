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
?>

<?php
if(Yii::app()->params->admin->googleIntegration) { // menu if google integration is enables has additional options
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions')),
//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
		array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
	);
} else {
	$menuItems = array(
		array('label'=>Yii::t('calendar','Calendar'), 'url'=>array('index')),
		array('label'=>Yii::t('calendar', 'My Calendar Permissions')),
//		array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
//		array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
	);
}
$this->actionMenu = $this->formatMenu($menuItems);
?>



<?php

$users = User::model()->findAll(array('select'=>'id, username, firstName, lastName', 'index'=>'id'));

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
});
",CClientScript::POS_HEAD);

$names = array();
foreach($users as $user)
    if($user->username != 'admin' && $user->id != Yii::app()->user->id)
    	$names[$user->id] = $user->firstName . ' ' . $user->lastName;
    	
$viewPermission = X2CalendarPermissions::getUserIdsWithViewPermission(Yii::app()->user->id);
$editPermission = X2CalendarPermissions::getUserIdsWithEditPermission(Yii::app()->user->id);
?>
<div class="page-title"><h2><?php echo Yii::t('calendar', 'View Permission'); ?></h2></div>
<div class="form">
	<?php echo Yii::t('calendar', 'These users can view your calendar.'); ?>
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
	<?php echo Yii::t('calendar', 'These users can edit your calendar.'); ?>
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
	</div>
</div>
<?php
$this->endWidget();
