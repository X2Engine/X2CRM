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
			<?php echo $attributeLabels['actionDescription']; ?>
		</td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php
			if($model->type=='attachment')
				echo MediaChild::attachmentActionText($model->actionDescription,true,true);
			else
				echo $this->convertUrls(CHtml::encode($model->actionDescription));
			?>
		</td>
	</tr>
	<tr>
		<td class="label" width="20%"><?php echo $attributeLabels['completedBy']; ?></td>
		<td width="25%"><?php echo ($model->completedBy=="Email") ? "Email" : User::getUserLinks($model->completedBy) ?></td>
		<td class="label" width="15%"><?php echo $attributeLabels['createDate']; ?></td>
		<td><b><?php echo $this->formatLongDateTime($model->createDate); ?></b></td>
	</tr>
</table>

<?php
} else {
?>
<table class="details">
	<tr>
		<td class="label" width="20%">
			<?php echo $attributeLabels['actionDescription']; ?>
		</td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php echo $this->convertUrls(CHtml::encode($model->actionDescription)); ?>
		</td>
	</tr>
<?php
if ($model->associationType!="none") {
?>
	<tr>
		<td class="label" width="20%">
			<?php echo $attributeLabels['associationName']; ?>
		</td>
		<td colspan="3">
			<?php echo CHtml::link($model->associationName,array('/'.$model->associationType.'/'.$model->associationId)); ?>
		</td>
	</tr>
	<tr>
<?php } ?>
		<td class="label"><?php echo $attributeLabels['assignedTo']; ?></td>
		<td><?php echo ($model->assignedTo=='Anyone')? $model->assignedTo : User::getUserLinks($model->assignedTo); ?></td>
		<td class="label" width="20%"><?php echo $attributeLabels['dueDate']; ?>
		<td><b><?php echo $this->formatLongDateTime($model->dueDate);?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo $attributeLabels['priority']; ?></td>
		<td><b><?php echo Yii::t('actions',$model->priority); ?></b></td>
		<td class="label"><?php echo $attributeLabels['createDate']; ?></td>
		<td><b><?php echo $this->formatLongDateTime($model->createDate); ?></b></td>
	</tr>
	<tr>
		<td class="label"><?php echo Yii::t('actions','Status'); ?></td>
		<td><b><?php echo $status; ?></b></td>
		<td class="label"><?php echo $attributeLabels['lastUpdated']; ?></td>
		<td><b><?php echo $this->formatLongDateTime($model->lastUpdated); ?></b></td>
	</tr>
        <?php 
        
        
        ?>
</table>
<?php } ?>