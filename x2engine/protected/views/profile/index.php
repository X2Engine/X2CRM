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

Yii::app()->clientScript->registerScriptFile(Yii::app()->getBaseUrl().'/js/x2forms.js');
$this->menu=array(
	array('label'=>Yii::t('profile','Social Feed')),
	array('label'=>Yii::t('profile','People'),'url'=>array('profiles')),
);
Yii::app()->clientScript->registerScript('highlightButton',"
$(function(){
	$('#feed-form input, #feed-form select, #feed-form textarea').change(function(){
		$('#save-button').css('background','yellow');
	}
	);
}
);");
?>

<h2><?php echo Yii::t('profile','Social Feed'); ?></h2>
<?php echo Yii::t('profile','A blog-like discussion forum');?>
<div class="form">
	<?php $feed=new Social; ?>
	<?php $form = $this->beginWidget('CActiveForm', array(
	'id'=>'feed-form',
	'enableAjaxValidation'=>false,
	'method'=>'post',
	'action'=>array('addPost','id'=>Yii::app()->user->getId(),'redirect'=>'index'),
	
	)); ?>	
	<div class="float-row">
		<?php
		$feed->data = Yii::t('app','Enter text here...');
		echo $form->textArea($feed,'data',array('onfocus'=>'toggleText(this);','onblur'=>'toggleText(this);','style'=>'width:558px;height:50px;color:#aaa;'));
		echo $form->dropDownList($feed,'associationId',$users);
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"$('#attachments').toggle();"));
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>


<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('type'=>'feed','associationId'=>Yii::app()->user->getId())); ?>
</div>
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull', 
	'template'=>'{items}'
)); ?>
