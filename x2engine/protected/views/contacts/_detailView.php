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

$attributeLabels = ContactChild::attributeLabels();
$showSocialMedia = ProfileChild::getSocialMedia();

Yii::app()->clientScript->registerScript('detailVewFields', "
function showField(field,focus){
	$('#'+field.id+' .detail-field').hide();
	$('#'+field.id+' .detail-form').show();
	if(focus)
		$('#'+field.id+' input').focus();
	highlightSave();
}
function highlightSave() {
	$('#save-changes').css('background','yellow');
}
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
Yii::app()->clientScript->registerScript('stopEdit','
	$(document).ready(function(){
		$("td#background a").click(function(e){
			e.stopPropagation();
		});
	});
');

function cleanupUrl($url) {
	if (!preg_match('/(http:\/\/|https:\/\/)/',$url))
		$url = 'http://'.$url;
	return $url;
}

// $template="<a href=".$this->createUrl('search/search?term=%23\\2')."> #\\2</a>";
		// $info=$model->backgroundInfo;
		// $info=mb_ereg_replace('(^|\s)#(\w\w+)',$template,$info);
?>
<div class="form no-border">
<table class="details">
	<tr>
		<td class="label"><?php echo $attributeLabels['firstName']; ?></td>
		<td id="firstName" onclick="showField(this,true);" width="145">
			<div class="detail-field"><?php echo $model->firstName; ?></div>
			<div class="detail-form">
			<?php
				echo $form->textField($model, 'firstName', array(
					'size'=>15,
					'maxlength'=>40,
					'tabindex'=>1,
					'style'=>'width:135px;'
				)); ?>
			</div>
		</td>
		<td class="label"><b><?php echo $attributeLabels['lastName']; ?></b></td>
		<td id="lastName" width="240" colspan="3" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->lastName; ?></div>
			<div class="detail-form">
				<?php
				echo $form->textField($model,'lastName',array(
					'size'=>15,
					'maxlength'=>40,
					'style'=>'width:225px;',
					'tabindex'=>2
				)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo (empty($model->phone))? "<b>".$attributeLabels['phone']."</b>" : CHtml::link($attributeLabels['phone'],'callto:+'.ereg_replace('[^0-9]', '',$model->phone)); ?></td>
		<td id="phone" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->phone; ?></div>
			<div class="detail-form">
				<?php
				echo $form->textField($model, 'phone', array(
					'size'=>30,
					'maxlength'=>20,
					'tabindex'=>3,
					'style'=>'width:135px;'
				)); ?>
			</div>
		</td>
		<?php 
			$str=substr(Yii::app()->request->getServerName(),4);
		?>
		<td class="label"><label><?php echo $model->email!=""?CHtml::mailto($attributeLabels['email'],$model->email."?cc=dropbox@".$str):$attributeLabels['email']; ?></label></td>
		<td id="email" colspan="3" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->email; ?></div>
			<div class="detail-form">
				<?php
				echo $form->textField($model, 'email', array(
					'size'=>15,
					'maxlength'=>100,
					'tabindex'=>4,
					'style'=>'width:225px;'
				)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo ($model->accountId==0)? "<b>".$attributeLabels['company']."</b>" : CHtml::link($attributeLabels['company'],array('accounts/view','id'=>$model->accountId)); ?></td>
		<td id="company" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->company; ?></div>
			<div class="detail-form">
				<?php echo $form->hiddenField($model, 'company');
					$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
					'name'=>'companyAutoComplete',
					'value'=>$model->company,
					'source' => $this->createUrl('contacts/getTerms'),
					'htmlOptions'=>array(
						'size'=>30,
						'maxlength'=>100,
						'tabindex'=>5,
						'style'=>
						'width:135px;'
					),
					'options'=>array(
						'minLength'=>'2',
						'select'=>'js:function( event, ui ) {
							$("#'.CHtml::activeId($model,'accountId').'").val(ui.item.id);
							$(this).val(ui.item.value);
							$("#'.CHtml::activeId($model,'company').'").val(ui.item.value);
							return false;
						}',
					),
				));
				echo $form->hiddenField($model, 'accountId');
				?>
			</div>
		</td>
		<td class="label" rowspan="4"><b><?php echo $attributeLabels['address']; ?></b></td>
		<td id="address" rowspan="4" colspan="3" style="padding:0.3em 0 0 0.6em;" onclick="showField(this,false);">
			<div class="detail-field">
				<?php if(!empty($model->address)) echo $model->address . '<br />'; ?>
				<?php echo $model->city; if(!empty($model->city) && !empty($model->state)) echo ', ';?>
				<?php echo $model->state; ?>
				<?php echo $model->zipcode; ?>
				<?php if(!empty($model->country)) echo '<br />' . $model->country; ?>
			</div>
			<div class="detail-form">
			<?php
			$default = empty($model->address);
			if($default)
				$model->address = $attributeLabels['address'];
			echo $form->textField($model, 'address', array(
				'size'=>30,
				'maxlength'=>100,
				'tabindex'=>6,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:225px;'.($default? 'color:#aaa;' : '')
			)); ?>
			<br />
			<?php
			$default = empty($model->city);
			if($default)
				$model->city = $attributeLabels['city'];
			echo $form->textField($model, 'city', array(
				'size'=>12,
				'maxlength'=>40,
				'tabindex'=>7,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : '')
			));
			$default = empty($model->state);
			if($default)
				$model->state = $attributeLabels['state'];
			echo $form->textField($model, 'state', array(
				'size'=>12,
				'maxlength'=>40,
				'tabindex'=>8,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:90px;'.($default? 'color:#aaa;' : '')
			)); ?>
			<br />
			<?php
			$default = empty($model->zipcode);
			if($default)
				$model->zipcode = $attributeLabels['zipcode'];
			echo $form->textField($model, 'zipcode', array(
				'size'=>12,
				'maxlength'=>20,
				'tabindex'=>9,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:90px;'.($default? 'color:#aaa;' : '')
			));
			$default = empty($model->country);
			if($default)
				$model->country = $attributeLabels['country'];
			echo $form->textField($model, 'country', array(
				'size'=>12,
				'maxlength'=>100,
				'tabindex'=>10,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : '')
			)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><b><?php echo $attributeLabels['rating']; ?></b></td>
		<td><?php
			$this->widget('CStarRating',array(
				'model'=>$model,
				'attribute'=>'rating',
				//'callback'=>'highlightSave',
				'minRating'=>1, //minimal valuez
				'maxRating'=>5,//max value
				'starCount'=>5, //number of stars
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><b><?php echo $attributeLabels['leadSource']; ?></b></td>
		<td id="leadSource" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->leadSource; ?></div>
			<div class="detail-form">
			<?php echo $form->textField($model,'leadSource',array(
				'size'=>25,
				'maxlength'=>100,
				'style'=>'width:135px;',
				'tabindex'=>11,
				)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo empty($model->website)? "<b>".$attributeLabels['website']."</b>" : CHtml::link($attributeLabels['website'],cleanupUrl($model->website)); ?></td>
		<td id="website" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->website; ?></div>
			<div class="detail-form">
				<?php echo $form->textField($model, 'website', array(
					'size'=>30,
					'maxlength'=>100,
					'style'=>'width:135px;',
					'tabindex'=>12
				)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><b><?php echo $attributeLabels['backgroundInfo']; ?></b></td>
		<td id="background" onclick="showField(this,true);" colspan="5" style="height:80px;">
			<div class="detail-field"><?php echo $this->convertUrls($model->backgroundInfo); ?></div>
			<div class="detail-form">
			<?php
			echo $form->textArea($model, 'backgroundInfo', array(
				'rows'=>3,
				'cols'=>50,
				'style'=>'width:455px;height:80px;',
				'tabindex'=>17
			)); ?>
			</div>
		</td>
	</tr>
	<tr id="social-media-toggle">
		<td class="label"><label><?php echo Yii::t('contacts','Social Media'); ?></label></td>
		<td colspan="5"><a href="#" onclick="showSocialMedia(); return false;"><?php echo Yii::t('app','Show'); ?></a></td>
	</tr>
	<tr id="social-media-1">
		<td class="label">
			<?php 
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/skype.png');
			if(!empty($model->skype))
				echo CHtml::link($img,'skype:'.$model->skype.'?call');
			else
				echo $img.' '; 
			?>
		</td>
		<td id="skype" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->skype; ?></div>
			<div class="detail-form">
			<?php
			echo $form->textField($model, 'skype', array(
				'size'=>10,
				'maxlength'=>32,
				'tabindex'=>13,
				'style'=>'width:135px;'
			));?>
			</div>
		</td>
		<td class="label">
			<?php
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/facebook.png');
			if(!empty($model->facebook))
				echo CHtml::link($img,cleanupUrl($model->facebook),array('target'=>'_blank'));
			else
				echo $img.' ';
			?>
		</td>
		<td id="facebook" onclick="showField(this,true);" colspan="3" >
			<div class="detail-field"><?php echo $model->facebook; ?></div>
			<div class="detail-form">
			<?php
			echo $form->textField($model, 'facebook', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>16,
				'style'=>'width:225px;'
			)); ?>
			</div>
		</td>
	</tr>
	<tr id="social-media-2">
		<td class="label">
			<?php
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/twitter.png');
			if(!empty($model->twitter))
				echo CHtml::link($img,'http://www.twitter.com/'.$model->twitter,array('target'=>'_blank'));
			else
				echo $img.' ';
			?>
		</td>
		<td id="twitter" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->twitter; ?></div>
			<div class="detail-form">
			<?php echo $form->textField($model, 'twitter', array(
				'size'=>10,
				'maxlength'=>20,
				'tabindex'=>14,
				'style'=>'width:135px;'
			)); ?>
			</div>
		</td>
		<td class="label">
			<?php
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/googleplus.png');
			if(!empty($model->googleplus))
				echo CHtml::link($img,cleanupUrl($model->googleplus),array('target'=>'_blank'));
			else
				echo $img.' ';
			?>
		</td>
		<td colspan="3" id="googleplus" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->googleplus; ?></div>
			<div class="detail-form">
			<?php
			echo $form->textField($model, 'googleplus', array(
				'size'=>10,
				'maxlength'=>100, 
				'tabindex'=>16,
				'style'=>'width:225px;'
			)); ?>
			</div>
		</td>
	</tr>
	<tr id="social-media-3">
		<td class="label">
			<?php
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/linkedin.png');
			if(!empty($model->linkedin))
				echo CHtml::link($img,cleanupUrl($model->linkedin),array('target'=>'_blank'));
			else
				echo $img.' ';
			?>
		</td>
		<td id="linkedin" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->linkedin; ?></div>
			<div class="detail-form">
			<?php
			echo $form->textField($model, 'linkedin', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>15,
				'style'=>'width:135px;'
			));?>
			</div>
		</td>
		<td class="label">
			<?php
			$img =  CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/other.png');
			if(!empty($model->otherUrl))
				echo CHtml::link($img,cleanupUrl($model->otherUrl),array('target'=>'_blank'));
			else
				echo $img.' ';
			?>
		</td>
		<td colspan="3" id="otherUrl" onclick="showField(this,true);">
			<div class="detail-field"><?php echo $model->otherUrl; ?></div>
			<div class="detail-form">
			<?php
			echo $form->textField($model, 'otherUrl', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>16,
				'style'=>'width:225px;'
			)); ?>
			</div>
		</td>
	</tr>
	<tr>
		<td class="label"><?php
		if(!empty($model->assignedTo) && $model->assignedTo != 'Anyone') {
			$assignedToUser = CActiveRecord::model('UserChild')->findByAttributes(array('username'=>$model->assignedTo));
			echo CHtml::link($attributeLabels['assignedTo'],array('profile/view','id'=>$assignedToUser->id));
		} else
			echo $form->label($model,'assignedTo');
		
		?></td>
		<td id="assignedTo">
				<?php echo $form->dropDownList($model,'assignedTo',$users,array('tabindex'=>18)); ?>
		</td>
		<td class="label"><b><?php echo $attributeLabels['priority']; ?></b></td>
		<td>
			<?php
			if(empty($model->priority))
				$model->priority = 'Medium';
			echo $form->dropDownList($model, 'priority', array(
				'Low'=>Yii::t('contacts','Low'),
				'Medium'=>Yii::t('contacts','Medium'),
				'High'=>Yii::t('contacts','High')
			),array('tabindex'=>19)); ?>
		</td>
		<td class="label"><b><?php echo $attributeLabels['visibility']; ?></b></td>
		<td>
			<?php 
			echo $form->dropDownList($model,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>21));
			// $model->createDate = time();
			// echo date("Y-m-d",$model->createDate);
			?>
		</td>
	</tr>
</table>
</div>