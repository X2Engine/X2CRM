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

// Yii::app()->clientScript->registerScript('highlightSaveAction',"
// $(function(){
	// $('#action-form input, #action-form select, #action-form textarea').change(function(){
		// $('#save-button, #save-button1, #save-button2').css('background','yellow');
	// }
	// );
// }
// );");

?>
<div id="email-form" class="wide form">
	<?php echo CHtml::beginForm(array('site/inlineEmail'),'post'); ?>
	<?php echo CHtml::hiddenField('redirectType',$redirectType); ?>
	<?php echo CHtml::hiddenField('redirectId',$redirectId); ?>
	<div class="row">
		<?php
		$class = in_array('to',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','To:'),'to',array('class'=>$class));
		echo CHtml::textField('to',Yii::app()->controller->encodeQuotes($to),array('id'=>'email-to','class'=>$class));
		?>
	</div>
	<div class="row">
		<?php
		$class = in_array('subject',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','Subject:'),'subject',array('class'=>$class));
		echo CHtml::textField('subject',Yii::app()->controller->encodeQuotes($subject),array('id'=>'email-subject','class'=>$class));
		?>
	</div>
	<div class="row">
		<?php
		$class = in_array('message',$errors)? 'error':null;
		echo CHtml::label(Yii::t('app','Message:'),'message',array('class'=>$class));
		echo CHtml::textArea('message',Yii::app()->controller->encodeQuotes($message),array('id'=>'email-message','style'=>'height:80px;','class'=>$class));
		?>
	</div>
	
	<?php
	echo CHtml::ajaxSubmitButton(
		Yii::t('app','Send'),
		array('site/inlineEmail','ajax'=>1),
		array(
			'replace'=>'#email-form'
		
			// 'success'=>"function(response) {
					// if(response=='success') {
						// $('#quick-contact-form').html(response);
					// } else
				// }",
			),
		array('id'=>'send-button','class'=>'x2-button','style'=>'margin-left:90px;')
	);

	// echo CHtml::htmlButton(Yii::t('app','Send'),array('type'=>'submit','class'=>'x2-button','id'=>'send-button','style'=>'margin-left:90px;')); ?>
	<?php echo CHtml::endForm(); ?>
</div>






