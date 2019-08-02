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
			echo CHtml::encode($model->subject);
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
				echo Media::attachmentActionText($model,true,true);
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
			echo CHtml::encode($model->subject);
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
			<?php
                            if ($model->associationType=="calendar")
                                echo CHtml::link(Yii::t('calendar', "{calendar}", array('{calendar}' => Modules::displayName(false, "Calendar"))), array('/'.$model->associationType.'/'));
                            else if ($model->isMultiassociated())
                                echo $model->renderMultiassociations ();
                            else
                                echo CHtml::link(CHtml::encode($model->associationName),array('/'.$model->associationType.'/'.$model->associationId));
                        ?>
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
        echo $model->getPriorityLabel(); ?></b></td>
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
