<?php
/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

$attributeLabels = $model->attributeLabels();

if($model->complete=='Yes')
	$status = Yii::t('actions','Finished');
else {
	if($model->dueDate > time())
		$status = Yii::t('actions','Incomplete');
	else
		$status = Yii::t('actions','Overdue');
}

if($model->type=='note' || $model->type=='attachment') {
?>
<table class="details">
    <tr>
		<td class="label">
			<?php echo $model->getAttributeLabel('subject'); ?>
		</td>
		<td colspan="3" class="text-field">
			<?php
			echo $model->subject;
			?>
		</td>
	</tr>
	<tr>
		<td class="label">
			<?php echo $model->getAttributeLabel('actionDescription'); ?>
		</td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php
			if($model->type=='attachment')
				echo Media::attachmentActionText($model->actionDescription,true,true);
			else
				echo $this->convertUrls(CHtml::encode($model->actionDescription));
			?>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo $model->getAttributeLabel('completedBy'); ?></td>
		<td width="25%"><?php echo ($model->completedBy=="Email") ? "Email" : User::getUserLinks($model->completedBy) ?></td>
		<td class="label" width="15%"><?php echo $attributeLabels['createDate']; ?></td>
		<td><b><?php echo Formatter::formatLongDateTime($model->createDate); ?></b></td>
	</tr>
</table>

<?php
} else {
?>
<table class="details">
    <tr>
		<td class="label">
			<?php echo $model->getAttributeLabel('subject'); ?>
		</td>
		<td colspan="3" class="text-field">
			<?php
			echo $model->subject;
			?>
		</td>
	</tr>
    <?php if($model->type=='email' || $model->type=='emailOpened') { ?>
        <tr>
            <td colspan="6" class="text-field">
                <iframe style="width:100%;height:600px" src="<?php echo Yii::app()->controller->createAbsoluteUrl('/actions/actions/viewEmail',array('id'=>$model->id)); ?>"></iframe>
            </td>
        </tr>
    <?php } else { ?>
	<tr>
		<td class="label" width="20%">
			<?php echo $model->getAttributeLabel('actionDescription'); ?>
		</td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php echo $this->convertUrls(CHtml::encode($model->actionDescription)); ?>
		</td>
	</tr>
    <?php } ?>
<?php
if ($model->associationType!="none") {
?>
	<tr>
		<td class="label" width="20%">
			<?php echo $model->getAttributeLabel('associationName'); ?>
		</td>
		<td colspan="3">
			<?php echo CHtml::link($model->associationName,array('/'.$model->associationType.'/'.$model->associationId)); ?>
		</td>
	</tr>

<?php } ?>
    <tr>
		<td class="label"><?php echo $model->getAttributeLabel('assignedTo'); ?></td>
		<td><?php echo ($model->assignedTo=='Anyone')? $model->assignedTo : User::getUserLinks($model->assignedTo); ?></td>
		<td class="label" width="20%"><?php echo $attributeLabels['dueDate']; ?>
		<td><b><?php echo Formatter::formatLongDateTime($model->dueDate);?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo $model->getAttributeLabel('priority'); ?></td>
		<td><b><?php
        echo Yii::t('actions',($model->priority==1?'Low':($model->priority==2?'Medium':'High'))); ?></b></td>
		<td class="label"><?php echo $attributeLabels['createDate']; ?></td>
		<td><b><?php echo Formatter::formatLongDateTime($model->createDate); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo Yii::t('actions','Status'); ?></td>
		<td><b><?php echo $status; ?></b></td>
		<td class="label"><?php echo $attributeLabels['lastUpdated']; ?></td>
		<td><b><?php echo Formatter::formatLongDateTime($model->lastUpdated); ?></b></td>
	</tr>
        <?php


        ?>
</table>
<?php } ?>
