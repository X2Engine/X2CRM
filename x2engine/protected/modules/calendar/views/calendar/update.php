<?php
/*****************************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2015 X2Engine Inc.
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

$modTitle = Modules::displayName();
$menuItems = array(
    array('label'=>Yii::t('calendar','{module}', array('{module}'=>$modTitle)), 'url'=>array('index')),
    array(
        'label'=>Yii::t('calendar', 'My {module} Permissions', array(
            '{module}' => $modTitle,
        )),
        'url'=>array('myCalendarPermissions')
    ),
    array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
    array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
    array('label'=>Yii::t('calendar','View'), 'url'=>array('view', 'id'=>$model->id)),
    array('label'=>Yii::t('calendar','Update')),
    array(
        'label'=>Yii::t('calendar','Delete'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete','id'=>$model->id),
            'confirm'=>'Are you sure you want to delete this item?'
        ),
    ),
);
if (Yii::app()->settings->googleIntegration) {
    $menuItems[] = array(
        'label'=>Yii::t('calendar','Sync My {actions} To Google Calendar', array(
            '{actions}' => Modules::displayName(true, "Actions"),
        )),
        'url'=>array('syncActionsToGoogleCalendar')
    );
}

$this->actionMenu=$this->formatMenu($menuItems);
?>

<h2 style="margin-bottom:0;">
    <?php echo Yii::t('quotes','Update {quote}: {name}',array(
        '{name}'=>$model->name,
        '{quote}' => Modules::displayName(false, "Quotes"),
    )); ?> <a class="x2-button" href="javascript:void(0);" onclick="$('#save-button').click();">Save</a></h2>

<?php

$users = User::getNames();
unset($users['Anyone']);
unset($users['admin']);

$form=$this->beginWidget('CActiveForm', array(
   'id'=>'calendar-form',
   'enableAjaxValidation'=>false,
));

$this->widget ('FormView', array(
    'model' => $model,
    'suppressQuickCreate' => true
));
//// echo $this->renderPartial('application.components.views.@FORMVIEW', 
	// array(
		// 'model'=>$model,
		// 'form'=>$form,
		// 'modelName'=>'calendar',
		// 'users'=>$users,
		// 'isQuickCreate'=>true, // let us create the CActiveForm in this file
	// )
// );
?>

<?php if(!$googleIntegration) { ?>

<div class="x2-layout form-view" style="margin-bottom: 0;">
	<div class="formSection">
		<div class="formSectionHeader">
			<span class="sectionTitle"><?php echo Yii::t('calendar', 'Google'); ?></span>
		</div>
	</div>
</div>

<div class="form" style="border:1px solid #ccc; border-top: 0; padding: 0; margin-top:-1px; border-radius:0;-webkit-border-radius:0; background:#eee;">
	<table frame="border">
		<td>
			<?php echo $form->labelEx($model, 'googleCalendar'); ?>
			<?php echo $form->checkbox($model, 'googleCalendar'); ?>
			<?php echo $form->labelEx($model, 'googleFeed'); ?>
			<?php echo $form->textField($model, 'googleFeed', array('size'=>75)); ?>
		</td>
	</table>
</div>

<?php } ?>
<?php $this->endWidget(); ?>
