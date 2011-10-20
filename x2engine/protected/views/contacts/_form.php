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

$showSocialMedia = ProfileChild::getSocialMedia();

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
Yii::app()->clientScript->registerScript('showSocialMedia', "
function showSocialMedia() {
	$('#social-media-1, #social-media-2, #social-media-3').show();
	$('#social-media-toggle').hide();
}
function hideSocialMedia() {
	$('#social-media-1, #social-media-2, #social-media-3').hide();
	$('#social-media-toggle').show();
}
$(function() {
".($showSocialMedia? "showSocialMedia(); });" : "hideSocialMedia(); });"),CClientScript::POS_HEAD);
Yii::app()->clientScript->registerScript('highlightSaveContact',"
$(function(){
	$('#contacts-form input, #contacts-form select, #contacts-form textarea').change(function(){
		$('#save-button, #save-button1, #save-button2').css('background','yellow');
	}
	);
}
);");

if (!isset($isQuickCreate)) {	//check if this form is being recycled in the quickCreate view

	echo '<div class="form no-border">';
	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'contacts-form',
		'enableAjaxValidation'=>false,
	));
	echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";
}

$attributeLabels = ContactChild::attributeLabels();

$showSocialMedia = ProfileChild::getSocialMedia();
?>
<?php
echo $form->errorSummary($contactModel);
?>

<table class="details">
	<tr>
		<td class="label"><label for="ContactChild_firstName"><?php echo Yii::t('contacts','Name'); ?><span class="required">*</span></label></td>
		<td colspan="3" id="firstName">
			<?php
			$default = empty($contactModel->firstName);
			if($default)
				$contactModel->firstName = Yii::t('contacts','First');
			echo $form->textField($contactModel, 'firstName', array(
				'maxlength'=>40,
				'style'=>'width:120px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>1,
			)); ?>
			<?php
			$default = empty($contactModel->lastName);
			if($default)
				$contactModel->lastName = Yii::t('contacts','Last');
			echo $form->textField($contactModel,'lastName',array(
				'maxlength'=>40,
				'style'=>'width:140px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>2
			)); ?>
			</td>
			<td class="label"><?php echo $form->label($contactModel,'rating'); ?></td>
			<td>
			<?php
			$this->widget('CStarRating',array(
				'model'=>$contactModel,
				'attribute'=>'rating',
				//'callback'=>'highlightSave',
				'minRating'=>1, //minimal valuez
				'maxRating'=>5,//max value
				'starCount'=>5, //number of stars
				'cssFile'=>Yii::app()->theme->getBaseUrl().'/css/rating/jquery.rating.css',
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo Yii::t('contacts','Position'); ?></td>
		<td id="title" colspan="5">
			<?php
			if($default)
				$contactModel->title = $contactModel->getAttributeLabel('title');
			echo $form->textField($contactModel,'title',array(
				'size'=>15,
				'maxlength'=>40,
				'style'=>'width:150px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>3,
			)); ?>
			<?php //echo $form->hiddenField($contactModel, 'company');
			if($default)
				$contactModel->company = $contactModel->getAttributeLabel('company');
			$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'model'=>$contactModel,
			'attribute'=>'company',
			//'value'=>$contactModel->company,
			'source' => $this->createUrl('contacts/getTerms'),
			'htmlOptions'=>array(
				'size'=>30,
				'maxlength'=>100,
				'style'=>'width:240px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>4,
			),
			'options'=>array(
				'minLength'=>1,
				'select'=>'js:function( event, ui ) {
					//$("#'.CHtml::activeId($contactModel,'accountId').'").val(ui.item.id);
					$(this).val(ui.item.value);
					//$("#'.CHtml::activeId($contactModel,'company').'").val(ui.item.value);
					return false;
				}',
			),
		));
		?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo Yii::t('contacts','Contact'); ?></td>
		<td colspan="5" id="contact">
			<?php
			$default = empty($contactModel->phone);
			if($default)
				$contactModel->phone = $contactModel->getAttributeLabel('phone');
			echo $form->textField($contactModel, 'phone', array(
				'maxlength'=>20,
				'style'=>'width:120px;margin-bottom:4px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>5,
			)); ?>
			<?php
			$default = empty($contactModel->email);
			if($default)
				$contactModel->email = $contactModel->getAttributeLabel('email');
			echo $form->textField($contactModel, 'email', array(
				'maxlength'=>20,
				'style'=>'width:240px;margin-bottom:4px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>6,
			)); ?>
			<br />
			<?php
			$default = empty($contactModel->phone2);
			if($default)
				$contactModel->phone2 = $contactModel->getAttributeLabel('phone2');
			echo $form->textField($contactModel, 'phone2', array(
				'maxlength'=>20,
				'style'=>'width:120px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>7,
			)); ?>
			<?php
			$default = empty($contactModel->website);
			if($default)
				$contactModel->website = $contactModel->getAttributeLabel('website');
			echo $form->textField($contactModel, 'website', array(
				'maxlength'=>100,
				'style'=>'width:240px;'.($default?'color:#aaa;':''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>8
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'address'); ?></td>
		<td id="address" colspan="5">
			<?php
			$default = empty($contactModel->address);
				$contactModel->address = $attributeLabels['address'];
			echo $form->textField($contactModel, 'address', array(
				'maxlength'=>100,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:240px;'.($default? 'color:#aaa;' : ''),
				'tabindex'=>9,
			)); ?>
			<?php
			$default = empty($contactModel->city);
			if($default)
				$contactModel->city = $attributeLabels['city'];
			echo $form->textField($contactModel, 'city', array(
				'maxlength'=>40,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : ''),
				'tabindex'=>10,
			)).' '; ?><br />
			<?php
			$default = empty($contactModel->state);
			if($default)
				$contactModel->state = $attributeLabels['state'];
			echo $form->textField($contactModel, 'state', array(
				'maxlength'=>40,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : ''),
				'tabindex'=>11,
			)); ?>
			<?php
			$default = empty($contactModel->zipcode);
			if($default)
				$contactModel->zipcode = $attributeLabels['zipcode'];
			echo $form->textField($contactModel, 'zipcode', array(
				'maxlength'=>20,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:80px;'.($default? 'color:#aaa;' : ''),
				'tabindex'=>12,
			)).' ';
			?>
			<?php
			$default = empty($contactModel->country);
			if($default)
				$contactModel->country = $attributeLabels['country'];
			echo $form->textField($contactModel, 'country', array(
				'maxlength'=>100,
				'style'=>'width:115px;'.($default? 'color:#aaa;' : ''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>13,
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'backgroundInfo'); ?></td>
		<td id="background" colspan="5"><div class="spacer"></div>
			<?php
			$default = empty($contactModel->leadSource);
			if($default)
				$contactModel->leadSource = $attributeLabels['leadSource'];
			echo $form->textField($contactModel,'leadSource',array(
				'maxlength'=>100,
				'style'=>'width:200px;margin-bottom:5px;'.($default? 'color:#aaa;' : ''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>14,
				)); ?><br />
			<?php
			$default = empty($contactModel->backgroundInfo);
			if($default)
				$contactModel->backgroundInfo = $contactModel->getAttributeLabel('backgroundInfo');
			echo $form->textArea($contactModel, 'backgroundInfo', array(
				'style'=>'width:440px;height:60px;'.($default? 'color:#aaa;' : ''),
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'tabindex'=>15
			)); ?>
		</td>
	</tr>
	<tr id="social-media-toggle">
		<td class="label"><label><?php echo Yii::t('contacts','Social Media'); ?></label></td>
		<td colspan="5"><a href="#" onclick="showSocialMedia(); return false;"><?php echo Yii::t('app','Show'); ?></a></td>
	</tr>
	<tr id="social-media-1">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/skype.png','Skype',array('title'=>'Skype')); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'skype', array(
				'size'=>10,
				'maxlength'=>32,
				'tabindex'=>16,
				'style'=>'width:135px;'
			));?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/facebook.png','Facebook',array('title'=>'Facebook')); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'facebook', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>17,
				'style'=>'width:220px;'
			)); ?>
		</td>
	</tr>
	<tr id="social-media-2">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/twitter.png','Twitter',array('title'=>'Twitter')); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'twitter', array(
				'size'=>10,
				'maxlength'=>20,
				'tabindex'=>18,
				'style'=>'width:135px;'
			)); ?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/googleplus.png','Google+',array('title'=>'Google+')); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'googleplus', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>19,
				'style'=>'width:220px;'
			)); ?>
		</td>
	</tr>
	<tr id="social-media-3">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/linkedin.png','LinkedIn',array('title'=>'LinkedIn')); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'linkedin', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>20,
				'style'=>'width:135px;'
			));?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/other.png',Yii::t('contacts','Other'),array('title'=>Yii::t('contacts','Other'))); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'otherUrl', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>21,
				'style'=>'width:220px;'
			)); ?><br />
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'assignedTo'); ?></td>
		<td id="assignedTo">
			
				<?php
				if(empty($contactModel->assignedTo))
					$contactModel->assignedTo = Yii::app()->user->getName();
				echo $form->dropDownList($contactModel,'assignedTo',$users,array('tabindex'=>22)); ?>

		</td>
		<td class="label"><?php echo $form->labelEx($contactModel,'priority'); ?></td>
		<td>
			<?php
			if(empty($contactModel->priority))
				$contactModel->priority = 'Medium';
			echo $form->dropDownList($contactModel, 'priority', array(
				'Low'=>Yii::t('contacts','Low'),
				'Medium'=>Yii::t('contacts','Medium'),
				'High'=>Yii::t('contacts','High')
			),array('tabindex'=>23)); ?>
		</td>
		<td class="label"><?php echo $form->label($contactModel,'visibility'); ?></td>
		<td>
			<?php 
			echo $form->dropDownList($contactModel,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>24));
			// $contactModel->createDate = time();
			// echo date("Y-m-d",$contactModel->createDate);
			?>
		</td>
	</tr>
</table>


<?php

if (!isset($isQuickCreate)) {	//if we're not in quickCreate, end the form
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton($contactModel->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','id'=>'save-button','tabindex'=>24))."\n";
echo "	</div>\n";

$this->endWidget();
echo "</div>\n";
}
?>






