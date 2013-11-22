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
?>
<div class="view">

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->name), array('view', 'id'=>$data->id)); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('assignedTo')); ?>:</b>
	<?php 
	if($data->assignedTo=='Anyone')
		echo CHtml::encode($data->assignedTo);
	else {
		$user=User::model()->findByAttributes(array('username'=>$data->assignedTo));
		echo CHtml::link(CHtml::encode($user->firstName." ".$user->lastName),array('/users/users/view','id'=>$user->id));
	}
	?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('quoteAmount')); ?>:</b>
	<?php echo CHtml::encode($data->quoteAmount); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('salesStage')); ?>:</b>
	<?php echo CHtml::encode($data->salesStage); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('expectedCloseDate')); ?>:</b>
	<?php echo CHtml::encode($data->expectedCloseDate); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('probability')); ?>:</b>
	<?php echo CHtml::encode($data->probability); ?>
	<br />

	<?php /*
	<b><?php echo CHtml::encode($data->getAttributeLabel('leadSource')); ?>:</b>
	<?php echo CHtml::encode($data->leadSource); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('description')); ?>:</b>
	<?php echo CHtml::encode($data->description); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('createDate')); ?>:</b>
	<?php echo CHtml::encode($data->createDate); ?>
	<br />

	*/ ?>

</div>
