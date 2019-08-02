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




$this->actionMenu = $this->formatMenu(array(
	array('label'=>Yii::t('profile','Social Feed')),
	array('label'=>Yii::t('profile','People'),'url'=>array('profiles')),
));

Yii::app()->clientScript->registerScript('highlightButton','
$("#feed-form textarea").bind("focus blur",function(){ x2.forms.toggleText(this); })
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
		echo CHtml::button(Yii::t('app','Attach A File/Photo'),array('class'=>'x2-button','onclick'=>"x2.FileUploader.toggle('activity')"));
		?>
	</div>
	<?php $this->endWidget(); ?>
</div>


<?php 
$this->widget ('FileUploader',array(
    'id' => 'activity',
    'url' => '/site/upload',
    'mediaParams' => array(
        'profileId' => $profileId, 
        'associationType' => 'feed',
        'associationId' => Yii::app()->user->getId(),
    ),
    'viewParams' => array (
        'showButton' => false
    )
));
?>
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
