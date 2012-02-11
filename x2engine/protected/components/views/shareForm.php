<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright © 2011-2012 by X2Engine Inc. www.X2Engine.com
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
	<?php echo CHtml::beginForm(array('site/inlineEmail'),'post'); ?>
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
		array('site/inlineEmail','ajax'=>1),
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