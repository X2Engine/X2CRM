<?php
/*********************************************************************************
 * X2Engine is a contact management program developed by
 * X2Engine, Inc. Copyright (C) 2011 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2Engine, X2Engine DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. at P.O. Box 66752,
 * Scotts Valley, CA 95067, USA. or at email address contact@X2Engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 ********************************************************************************/

$attributeLabels = Templates::attributeLabels();
include("protected/config/templatesConfig.php");
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#description a").click(function(e){
			e.stopPropagation();
		});
	});
');
?>
<table class="details">
	<tr>
		<td class="label" width="25%"><?php echo $attributeLabels['name']; ?></td>
		<td><?php echo $model->name; ?></td>
	</tr>
	<?php if($moduleConfig['descriptionDisplay']=='1'){ ?>
	<tr>
		<td class="label">
			<?php echo $attributeLabels['description']; ?>
		</td>
		<td class="text-field"><div class="spacer"></div>
			<?php echo $this->convertUrls($model->description); ?>
		</td>
	</tr><?php } ?>
	<?php if($moduleConfig['assignedToDisplay']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['assignedTo']; ?></td>
		<td><?php echo ($model->assignedTo=='Anyone')? $model->assignedTo : UserChild::getUserLinks($model->assignedTo); ?></td>
	</tr><?php } ?>
	<?php if($moduleConfig['displayOne']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['fieldOne']; ?></td>
		<td><?php echo $model->fieldOne; ?></td>
	</tr><?php }?>
	<?php if($moduleConfig['displayTwo']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['fieldTwo']; ?></td>
		<td><?php echo $model->fieldTwo; ?></td>
	</tr><?php } ?>
	<?php if($moduleConfig['displayThree']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['fieldThree']; ?></td>
		<td><?php echo $model->fieldThree; ?></td>
	</tr><?php } ?>
	<?php if($moduleConfig['displayFour']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['fieldFour']; ?></td>
		<td><?php echo $model->fieldFour; ?></td>
	</tr><?php } ?>
	<?php if($moduleConfig['displayFive']=='1'){ ?>
	<tr>
		<td class="label"><?php echo $attributeLabels['fieldFive']; ?></td>
		<td><?php echo $model->fieldFive; ?></td>
	</tr><?php } ?>
</table>