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
        $feed->visibility=1;
		echo $form->dropDownList($feed,'visibility',array(1=>Yii::t('actions','Public'),0=>Yii::t('actions','Private')));
        echo $form->dropDownList($feed,'subtype',json_decode(Dropdowns::model()->findByPk(14)->options,true));
		echo CHtml::submitButton(Yii::t('app','Post'),array('class'=>'x2-button','id'=>'save-button'));
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"$('#attachments').toggle();"));
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>


<div id="attachments" style="display:none;">
<?php $this->widget('Attachments',array('associationType'=>'feed','associationId'=>Yii::app()->user->getId())); ?>
</div>
<?php 
$allFlag=(isset($_GET['filter']) && $_GET['filter']=='all') || !isset($_GET['filter']);
$publicFlag=isset($_GET['filter']) && $_GET['filter']=='public';
$privateFlag=isset($_GET['filter']) && $_GET['filter']=='private';
$subtypeFlag=isset($_GET['subtype'])?true:false;
$subtype=$subtypeFlag?$_GET['subtype']:'all';
$socialTabs = array(
			'all'=>$allFlag?'All':CHtml::link('All','index?filter=all&subtype='.$subtype),
			'public'=>$publicFlag?'Public':CHtml::link('Public','index?filter=public&subtype='.$subtype),
			'private'=>$privateFlag?'Private':CHtml::link('Private','index?filter=private&subtype='.$subtype),
		);
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'../social/_viewFull', 
    
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/listview',
	'template'=>'<div class="social-tabs" style="float:left;">'.implode(' | ',array_values($socialTabs)).' || '.implode(' | ',array_values($subtypes)).'</div> {summary}{items}{pager}',
)); ?>
