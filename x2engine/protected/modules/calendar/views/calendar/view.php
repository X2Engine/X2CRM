<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




/* if(Yii::app()->settings->googleIntegration) { // menu if google integration is enables has additional options
    $menuItems = array(
        array('label'=>Yii::t('calendar','Calendar')),
        array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
        array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
        array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
        array('label'=>Yii::t('calendar','View')),
        array('label'=>Yii::t('calendar','Update'), 'url'=>array('update', 'id'=>$model->id)),
        array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
        array('label'=>Yii::t('calendar', 'Sync My Actions To Google Calendar'), 'url'=>array('syncActionsToGoogleCalendar')),
    );
} else {
    $menuItems = array(
        array('label'=>Yii::t('calendar','Calendar')),
        array('label'=>Yii::t('calendar', 'My Calendar Permissions'), 'url'=>array('myCalendarPermissions')),
        array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
        array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
        array('label'=>Yii::t('calendar','View')),
        array('label'=>Yii::t('calendar','Update'), 'url'=>array('update', 'id'=>$model->id)),
        array('label'=>Yii::t('calendar','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
    );
} */

$modTitle = Modules::displayName();
$menuItems = array(
    array('label'=>Yii::t('calendar','{module}', array('{module}' => $modTitle))),
    array(
        'label'=>Yii::t('calendar', 'My {module} Permissions', array(
            '{module}' => $modTitle,
        )),
        'url'=>array('myCalendarPermissions')
    ),
    array('label'=>Yii::t('calendar', 'List'),'url'=>array('list')),
    array('label'=>Yii::t('calendar','Create'), 'url'=>array('create')),
    array('label'=>Yii::t('calendar','View')),
    array(
        'label'=>Yii::t('calendar','Update'),
        'url'=>array('update', 'id'=>$model->id)
    ),
    array(
        'label'=>Yii::t('calendar','Delete'),
        'url'=>'#',
        'linkOptions'=>array(
            'submit'=>array('delete','id'=>$model->id),
            'confirm'=>'Are you sure you want to delete this item?'
    )),
);

$this->actionMenu = $this->formatMenu($menuItems);
?>

<h2><?php echo Yii::t('calendar','Shared {module}:', array('{module}'=>$modTitle)); ?> <b><?php echo $model->name; ?></b> <a class="x2-button" href="<?php echo $this->createUrl('update', array('id'=>$model->id));?>">Edit</a></h2>

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id'=>'quotes-form',
    'enableAjaxValidation'=>false,
    'action'=>array('saveChanges','id'=>$model->id),
));
$this->widget('DetailView', array(
    'model'   => $model,
));
// $this->renderPartial('application.components.views.@DETAILVIEW',array('model'=>$model,'modelName'=>'calendar'));
?>
</div>
<?php $this->endWidget(); ?>

<?php /*
<a class="x2-button" href="#" onClick="x2.forms.toggleForm('#attachment-form',200);return false;"><span><?php echo Yii::t('app','Attach A File/Photo'); ?></span></a>
<br /><br />

<div id="attachment-form" style="display:none;">
    <?php $this->widget('Attachments',array('type'=>'quotes','associationId'=>$model->id)); ?>
</div>
<?php

$this->widget('InlineActionForm',
    array(
        'associationType'=>'calendar',
        'associationId'=>$model->id,
        'assignedTo'=>Yii::app()->user->getName(),
        'users'=>$users,
        'startHidden'=>false
    )
);

if(isset($_GET['history']))
    $history=$_GET['history'];
else
    $history="all";

$this->widget('zii.widgets.CListView', array(
    'dataProvider'=>$actionHistory,
    'itemView'=>'../actions/_view',
    'htmlOptions'=>array('class'=>'action list-view'),
    'template'=> 
            ($history=='all'?'<h3>'.Yii::t('app','History')."</h3>":CHtml::link(Yii::t('app','History'),"?history=all")).
            " | ".($history=='actions'?'<h3>'.Yii::t('app','Actions')."</h3>":CHtml::link(Yii::t('app','Actions'),"?history=actions")).
            " | ".($history=='comments'?'<h3>'.Yii::t('app','Comments')."</h3>":CHtml::link(Yii::t('app','Comments'),"?history=comments")).
            " | ".($history=='attachments'?'<h3>'.Yii::t('app','Attachments')."</h3>":CHtml::link(Yii::t('app','Attachments'),"?history=attachments")).
            '</h3>{summary}{sorter}{items}{pager}',
)); */
?>
