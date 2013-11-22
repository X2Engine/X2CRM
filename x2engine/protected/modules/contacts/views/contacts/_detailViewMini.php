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

// $attributeLabels = $model->getAttributeLabel();

if(isset($actionModel) && $actionModel->associationId!=0)
	$link = CHtml::link(CHtml::encode($model->name),
		array('/contacts/contacts/view','id'=>$model->id));
else if(isset($serviceModel) && $serviceModel->contactId != 0)
	$link = CHtml::link(CHtml::encode($model->name), array('/contacts/contacts/view','id'=>$model->id));
else
	$link = Yii::t('actions','No one');
?>

<table class="details">
	<tr>
		<td class="label" width="20%"><?php echo Yii::t('contacts','Name'); ?></td>
		<td width="25%">
			<b><?php echo $link; ?></b>
		</td>
		<td class="label" width="15%"><?php echo $model->getAttributeLabel('email'); ?></td>
		<td>
			<b><?php echo CHtml::mailto($model->email,$model->email); ?></b>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $model->getAttributeLabel('assignedTo'); ?></td>
		<td>
			<?php echo ($model->assignedTo=='Anyone')? $model->assignedTo : User::getUserLinks($model->assignedTo); ?>
		</td>
		<td class="label"><?php echo $model->getAttributeLabel('phone'); ?></td>
		<td>
			<?php
				$phone = $model->phone;
				// see if we need/can to format the phone number
				$phoneCheck = PhoneNumber::model()->findByAttributes(array('modelId' => $model->id, 'modelType' => 'Contacts', 'fieldName' => 'phone'));
				if(isset($phoneCheck) && strlen($phoneCheck->number) == 10) {
				    $temp = $phoneCheck->number;
				    $phone = "(" . substr($temp, 0, 3) . ") " . substr($temp, 3, 3) . "-" . substr($temp, 6, 4);
				}
			?>
			<b><?php echo $phone; ?></b>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $model->getAttributeLabel('priority'); ?></td>
		<td>
			<b><?php echo Yii::t('contacts',$model->priority); ?></b>
		</td>
		<td class="label"><?php echo $model->getAttributeLabel('address'); ?></td>
		<td>
			<?php echo $model->address; ?>
		</td>
	</tr>

	<tr>
		<td class="label"><?php echo $model->getAttributeLabel('backgroundInfo'); ?></td>
		<td colspan="3" class="text-field"><div class="spacer"></div>
			<?php echo $this->convertUrls($model->backgroundInfo); ?>
		</td>
	</tr>
</table>