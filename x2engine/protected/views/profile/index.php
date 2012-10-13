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
 * Copyright � 2011-2012 by X2Engine Inc. www.X2Engine.com
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

$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed')),
	array('label'=>Yii::t('profile','People'),'url'=>array('profiles')),
));

Yii::app()->clientScript->registerScript('highlightButton','
$("#feed-form textarea").bind("focus blur",function(){ toggleText(this); })
	.change(function(){
		if($(this).val()=="")
			$("#save-button").removeClass("highlight");
		else
			$("#save-button").addClass("highlight");
	});
',CClientScript::POS_READY);
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
		echo $form->textArea($feed,'data',array('style'=>'width:558px;height:50px;color:#aaa;display:block;clear:both;'));
		echo $form->dropDownList($feed,'associationId',$users);
        $feed->private=1;
		echo $form->dropDownList($feed,'private',array(0=>Yii::t('actions','Public'),1=>Yii::t('actions','Private')));
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"$('#attachments').toggle();"));
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>


<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed','associationId'=>Yii::app()->user->getId())); ?>
</div>
<?php $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull', 
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
	'template'=>'{summary}{sorter}{items}{pager}'
)); ?>
