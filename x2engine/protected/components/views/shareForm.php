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
<div id="email-form">
<?php

if(!empty($status)) {
	$index = array_search('200',$status);
	if($index !== false) {
		unset($status[$index]);
		$message = '';
		$subject = '';
	}
	echo '<div class="form">';
	foreach($status as &$status_msg) echo $status_msg." \n";
	echo '</div>';
}
?>
<div class="wide form">
	<?php echo CHtml::beginForm(array('/site/inlineEmail'),'post'); ?>
	<?php echo CHtml::hiddenField('redirect',$redirect); ?>
	<?php //echo CHtml::hiddenField('redirectId',$redirectId); ?>
	<div class="row">
		<?php
		$class = in_array('to',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','To:'),'to',array('class'=>$class));
		echo '&lt;'.$name.'&gt; '.$address;
		echo CHtml::hiddenField('inlineEmail_name',$name,array('id'=>'email-to','class'=>$class));
		echo CHtml::hiddenField('inlineEmail_address',$address,array('id'=>'email-address','class'=>$class));
		?>
	</div>
	<div class="row">
		<?php
		$class = in_array('subject',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','Subject:'),'inlineEmail_subject',array('class'=>$class));
		echo CHtml::textField('inlineEmail_subject',$subject,array('id'=>'email-subject','class'=>$class));
		?>
	</div>
	<div class="row">
		<?php
		$class = in_array('message',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','Message:'),'inlineEmail_message',array('class'=>$class));
		echo CHtml::textArea('inlineEmail_message',$message,array('id'=>'email-message','style'=>'height:80px;','class'=>$class));
		?>
	</div>
	<div class="row buttons">
	<?php
	// echo CHtml::submitButton(Yii::t('app','Send'),array('class'=>'x2-button'));

	echo CHtml::ajaxSubmitButton(
		Yii::t('app','Send'),
		array('/site/inlineEmail','ajax'=>1),
		array(
			'update'=>'#email-form'
		
			// 'success'=>"function(response) {
					// if(response=='success') {
						// $('#quick-contact-form').html(response);
					// } else
				// }",
			),
		array('id'=>'send-button','class'=>'x2-button highlight','style'=>'margin-left:-20px;')
	);
	?>
	<?php
	echo CHtml::resetButton(Yii::t('app','Cancel'),array('class'=>'x2-button','onclick'=>"$('#email-form').toggle('blind',300);"));
	// echo CHtml::htmlButton(Yii::t('app','Send'),array('type'=>'submit','class'=>'x2-button','id'=>'send-button','style'=>'margin-left:90px;')); ?>
	</div>
	<?php echo CHtml::endForm(); ?>
</div>
</div>
