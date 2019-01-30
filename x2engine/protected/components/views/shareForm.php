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
