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
		<td width="20%" class="label"><?php echo $form->labelEx($contactModel,'firstName'); ?></td>
		<td width="20%" id="firstName">
			<?php
			echo $form->textField($contactModel, 'firstName', array(
				'size'=>15,
				'maxlength'=>40,
				'tabindex'=>1,
				'style'=>'width:135px;'
			)); ?>
		</td>
		<td width="18%" class="label"><?php echo $form->labelEx($contactModel,'lastName'); ?></td>
		<td id="lastName" colspan="3">
			<?php
			echo $form->textField($contactModel,'lastName',array(
				'size'=>15,
				'maxlength'=>40,
				'style'=>'width:225px;',
				'tabindex'=>2
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'phone'); ?></td>
		<td id="phone">
			<?php
			echo $form->textField($contactModel, 'phone', array(
				'size'=>30,
				'maxlength'=>20,
				'tabindex'=>3,
				'style'=>'width:135px;'
			)); ?>
		</td>
		<td class="label"><?php echo $form->labelEx($contactModel,'email'); ?></td>
		<td id="email" colspan="3">
			<?php
			echo $form->textField($contactModel, 'email', array(
				'size'=>15,
				'maxlength'=>100,
				'tabindex'=>4,
				'style'=>'width:225px;'
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'company'); ?></td>
		<td id="company">
			<?php echo $form->hiddenField($contactModel, 'company');
				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
				'name'=>'companyAutoComplete',
				'value'=>$contactModel->company,
				'source' => $this->createUrl('contacts/getTerms'),
				'htmlOptions'=>array(
					'size'=>30,
					'maxlength'=>100,
					'tabindex'=>5,
					'style'=>'width:135px;'
				),
				'options'=>array(
					'minLength'=>'2',
					'select'=>'js:function( event, ui ) {
						$("#'.CHtml::activeId($contactModel,'accountId').'").val(ui.item.id);
						$(this).val(ui.item.value);
						$("#'.CHtml::activeId($contactModel,'company').'").val(ui.item.value);
						return false;
					}',
				),
			));
			echo $form->hiddenField($contactModel, 'accountId');?>
		</td>
		<td class="label" rowspan="4"><?php echo $form->labelEx($contactModel,'address'); ?></td>
		<td id="address" rowspan="4" colspan="3">
			<?php
			$default = empty($contactModel->address);
				$contactModel->address = $attributeLabels['address'];
			echo $form->textField($contactModel, 'address', array(
				'size'=>30,
				'maxlength'=>100,
				'tabindex'=>6,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:225px;'.($default? 'color:#aaa;' : '')
			)); ?>
			<br />
			<?php
			$default = empty($contactModel->city);
			if($default)
				$contactModel->city = $attributeLabels['city'];
			echo $form->textField($contactModel, 'city', array(
				'size'=>12,
				'maxlength'=>40,
				'tabindex'=>7,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : '')
			));
			$default = empty($contactModel->state);
			if($default)
				$contactModel->state = $attributeLabels['state'];
			echo $form->textField($contactModel, 'state', array(
				'size'=>12,
				'maxlength'=>40,
				'tabindex'=>8,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:90px;'.($default? 'color:#aaa;' : '')
			)); ?>
			<br />
			<?php
			$default = empty($contactModel->zipcode);
			if($default)
				$contactModel->zipcode = $attributeLabels['zipcode'];
			echo $form->textField($contactModel, 'zipcode', array(
				'size'=>12,
				'maxlength'=>20,
				'tabindex'=>9,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:90px;'.($default? 'color:#aaa;' : '')
			));
			$default = empty($contactModel->country);
			if($default)
				$contactModel->country = $attributeLabels['country'];
			echo $form->textField($contactModel, 'country', array(
				'size'=>12,
				'maxlength'=>100,
				'tabindex'=>10,
				'onfocus'=>$default? 'toggleText(this);' : null,
				'onblur'=>$default? 'toggleText(this);' : null,
				'style'=>'width:120px;'.($default? 'color:#aaa;' : '')
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'rating'); ?></td>
		<td><?php
			$this->widget('CStarRating',array(
				'model'=>$contactModel,
				'attribute'=>'rating',
				//'callback'=>'highlightSave',
				'minRating'=>1, //minimal valuez
				'maxRating'=>5,//max value
				'starCount'=>5, //number of stars
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'leadSource'); ?></td>
		<td id="leadSource">
			<?php echo $form->textField($contactModel,'leadSource',array(
				'size'=>25,
				'maxlength'=>100,
				'style'=>'width:135px;',
				'tabindex'=>11,
				)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'website'); ?></td>
		<td id="website">
			<?php echo $form->textField($contactModel, 'website', array(
				'size'=>30,
				'maxlength'=>100,
				'style'=>'width:135px;',
				'tabindex'=>12
			)); ?>
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'backgroundInfo'); ?></td>
		<td id="background" colspan="5"><div class="spacer"></div>
			<?php
			echo $form->textArea($contactModel, 'backgroundInfo', array(
				'rows'=>3,
				'cols'=>50,
				'style'=>'width:470px;height:80px;',
				'tabindex'=>13
			)); ?>
		</td>
	</tr>
	<tr id="social-media-toggle">
		<td class="label"><label><?php echo Yii::t('contacts','Social Media'); ?></label></td>
		<td colspan="5"><a href="#" onclick="showSocialMedia(); return false;"><?php echo Yii::t('app','Show'); ?></a></td>
	</tr>
	<tr id="social-media-1">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/skype.png'); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'skype', array(
				'size'=>10,
				'maxlength'=>32,
				'tabindex'=>14,
				'style'=>'width:135px;'
			));?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/facebook.png'); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'facebook', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>15,
				'style'=>'width:225px;'
			)); ?>
		</td>
	</tr>
	<tr id="social-media-2">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/twitter.png'); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'twitter', array(
				'size'=>10,
				'maxlength'=>20,
				'tabindex'=>16,
				'style'=>'width:135px;'
			)); ?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/googleplus.png'); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'googleplus', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>17,
				'style'=>'width:225px;'
			)); ?>
		</td>
	</tr>
	<tr id="social-media-3">
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/linkedin.png'); ?></td>
		<td>
			<?php
			echo $form->textField($contactModel, 'linkedin', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>18,
				'style'=>'width:135px;'
			));?>
		</td>
		<td class="label"><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/etc/other.png'); ?></td>
		<td colspan="3">
			<?php
			echo $form->textField($contactModel, 'otherUrl', array(
				'size'=>10,
				'maxlength'=>100,
				'tabindex'=>19,
				'style'=>'width:225px;'
			)); ?><br />
		</td>
	</tr>
	<tr>
		<td class="label"><?php echo $form->labelEx($contactModel,'assignedTo'); ?></td>
		<td id="assignedTo">
			
				<?php
				if(empty($contactModel->assignedTo))
					$contactModel->assignedTo = Yii::app()->user->getName();
				echo $form->dropDownList($contactModel,'assignedTo',$users,array('tabindex'=>20)); ?>

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
			),array('tabindex'=>21)); ?>
		</td>
		<td class="label"><?php echo $form->labelEx($contactModel,'visibility'); ?></td>
		<td>
			<?php 
			echo $form->dropDownList($contactModel,'visibility',array(
				1=>Yii::t('contacts','Public'),
				0=>Yii::t('contacts','Private')
			),array('tabindex'=>22));
			// $contactModel->createDate = time();
			// echo date("Y-m-d",$contactModel->createDate);
			?>
		</td>
	</tr>
</table>


<?php

if (!isset($isQuickCreate)) {	//if we're not in quickCreate, end the form
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton($contactModel->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button','tabindex'=>23))."\n";
echo "	</div>\n";

$this->endWidget();
echo "</div>\n";
}

 /*
if (!isset($isQuickCreate)) {	//check if this form is being recycled in the quickCreate view
	echo '<div class="form">'."\n";

	$form=$this->beginWidget('CActiveForm', array(
		'id'=>'contacts-form',
		'enableAjaxValidation'=>false,
	));
	echo '<em>'.Yii::t('app','Fields with <span class="required">*</span> are required.')."</em>\n";
}
echo $form->errorSummary($contactModel);
//$accounts=CJSON::encode($accounts);
?>
<div class="top row">
	<div class="cell">
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'firstName'); ?>
				<?php echo $form->textField($contactModel,'firstName',array('size'=>20,'maxlength'=>40,'tabindex'=>1)); ?>
				<?php echo $form->error($contactModel,'firstName'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'lastName'); ?>
				<?php echo $form->textField($contactModel,'lastName',array('size'=>25,'maxlength'=>40,'tabindex'=>2)); ?>
				<?php echo $form->error($contactModel,'lastName'); ?>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'phone'); ?>
				<?php echo $form->textField($contactModel,'phone',array('size'=>15,'maxlength'=>40,'tabindex'=>4)); ?>
				<?php echo $form->error($contactModel,'phone'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'email'); ?>
				<?php echo $form->textField($contactModel,'email',array('size'=>30,'maxlength'=>100,'tabindex'=>5)); ?>
				<?php echo $form->error($contactModel,'email'); ?>
			</div>
		</div>
		<div class="row">
			<?php echo $form->labelEx($contactModel,'address'); ?>
			<?php echo $form->textField($contactModel,'address',array('size'=>50,'maxlength'=>100,'style'=>'width:290px;','tabindex'=>7)); ?>
			<?php echo $form->error($contactModel,'address'); ?>
		</div>
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'city'); ?>
				<?php echo $form->textField($contactModel,'city',array('size'=>30,'maxlength'=>40,'tabindex'=>8)); ?>
				<?php echo $form->error($contactModel,'city'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'state'); ?>
				<?php echo $form->textField($contactModel,'state',array('size'=>15,'maxlength'=>40,'tabindex'=>9)); ?>
				<?php echo $form->error($contactModel,'state'); ?>
			</div>
		</div>
	</div>
	<div class="cell right" id="auto_complete">
		<?php
		echo $form->label($contactModel,'company');
		echo $form->hiddenField($contactModel,'company');
		$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
			'name'=>'companyAutoComplete',
			'source' => $this->createUrl('contacts/getTerms'),
			'htmlOptions'=>array('size'=>25,'maxlength'=>100,'tabindex'=>3),
			'options'=>array(
				'minLength'=>'2',
				'select'=>'js:function( event, ui ) {
					$("#'.CHtml::activeId($contactModel,'accountId').'").val(ui.item.id);
					$(this).val(ui.item.value);
					$("#'.CHtml::activeId($contactModel,'company').'").val(ui.item.value);
					return false;
				}',
			),
		));
		echo $form->error($contactModel,'company');
		echo $form->hiddenField($contactModel,'accountId');
		?>

		<?php echo $form->labelEx($contactModel,'backgroundInfo'); ?>
		<?php echo $form->textArea($contactModel,'backgroundInfo',array('rows'=>6,'cols'=>25,'style'=>'width:160px;height:60px;','tabindex'=>6)); ?>
		<?php echo $form->error($contactModel,'backgroundInfo'); ?>
		<?php echo $form->labelEx($contactModel,'rating'); ?>
		<?php $this->widget('CStarRating',array(
				'model'=>$contactModel,
				'attribute'=>'rating',
				'minRating'=>1, //minimal value
				'maxRating'=>5,//max value
				'starCount'=>5, //number of stars
				
			)); ?>
	</div>
</div>
<div class="row">
	<div class="cell">
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'zipcode'); ?>
				<?php echo $form->textField($contactModel,'zipcode',array('size'=>15,'maxlength'=>20,'tabindex'=>10)); ?>
				<?php echo $form->error($contactModel,'zipcode'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'country'); ?>
				<?php echo $form->textField($contactModel,'country',array('size'=>30,'maxlength'=>40,'tabindex'=>11)); ?>
				<?php echo $form->error($contactModel,'country'); ?>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'website'); ?>
				<?php echo $form->textField($contactModel, 'website', array('size'=>30, 'maxlength'=>100)); ?>
				<?php echo $form->error($contactModel,'website'); ?>
			</div>
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'twitter'); ?>
				<?php echo $form->textField($contactModel, 'twitter', array('size'=>15, 'maxlength'=>20)); ?>
				<?php echo $form->error($contactModel,'twitter'); ?>
			</div>
		</div>
		<div class="row">
			<div class="cell">
				<?php echo $form->labelEx($contactModel,'priority'); ?>
				<?php echo $form->dropDownList($contactModel,'priority', array('Low'=>'Low', 'Medium'=>'Medium', 'High'=>'High'),array('tabindex'=>13)); ?>
				<?php echo $form->error($contactModel,'priority'); ?>
			</div>
			<div class="cell">
				<?php $contactModel->assignedTo=Yii::app()->user->getName(); ?>
				<?php echo $form->labelEx($contactModel,'assignedTo'); ?>
				<?php echo $form->dropDownList($contactModel,'assignedTo',$users,array('tabindex'=>14)); ?>
				<?php echo $form->error($contactModel,'assignedTo'); ?>
			</div>
			<div class="cell">
				<?php echo $form->label($contactModel,'visibility'); ?>
				<?php echo $form->dropDownList($contactModel,'visibility', array(1=>'Public', 0=>'Private'),array('tabindex'=>15)); ?>
				<?php // echo $form->checkBox($contactModel,'visibility',array('value'=>'1','uncheckedValue'=>'0')); ?> 
			</div>
		</div>
	</div>
	<div class="cell right">
		<?php echo $form->labelEx($contactModel,'leadSource'); ?>
		<?php echo $form->textField($contactModel,'leadSource',array('size'=>25,'maxlength'=>100,'tabindex'=>12)); ?>
		<?php echo $form->error($contactModel,'leadSource'); ?>
	</div>	
	<div class="cell">
		<?php echo $form->hiddenField($contactModel,'accountId'); ?>
	</div>
	
</div>
<?php

if (!isset($isQuickCreate)) {	//if we're not in quickCreate, end the form
echo '	<div class="row buttons">'."\n";
echo '		'.CHtml::submitButton($contactModel->isNewRecord ? Yii::t('app','Create'):Yii::t('app','Save'),array('class'=>'x2-button'))."\n";
echo "	</div>\n";

$this->endWidget();
echo "</div>\n";
}
*/
?>






