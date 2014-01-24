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