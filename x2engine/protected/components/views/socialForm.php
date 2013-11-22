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
<div class="form">
	<?php $feed=new Events; ?>
	<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'action'=>array('/profile/addPost','id'=>Yii::app()->user->getId()),
	
	)); ?>	
	<div class="float-row">
		<?php
		$feed->text = Yii::t('app','Enter text here...');
		echo $form->textArea($feed,'data',array('onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'width:558px;height:50px;color:#aaa;'));
		echo $form->dropDownList($feed,'associationId',$users);
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button'));
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>
