<?php
/*********************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
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
	array('label'=>Yii::t('profile','Social Feed'),'url'=>array('/profile')),
	array('label'=>Yii::t('users','Manage Users')),
	array('label'=>Yii::t('users','Create User'), 'url'=>array('create')),
	array('label'=>Yii::t('users','Invite Users'), 'url'=>array('inviteUsers')),
));

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
	$('.search-form').toggle();
	return false;
});
$('.search-form form').submit(function(){
	$.fn.yiiGridView.update('users-grid', {
		data: $(this).serialize()
	});
	return false;
});
");
?>

<?php 

    if(isset($_GET['offset'])){
        $offset=$_GET['offset'];
    }else
        $offset='first day of this week';
?>
<h2><?php echo Yii::t('users','Manage Users'); ?></h2>
<div class="search-form" style="display:none">
<?php $this->renderPartial('_search',array(
	'model'=>$model,
)); ?>
</div><!-- search-form -->
<?php
	$this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'users-grid',
	'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview/',
	'template'=> '<div class="title-bar">'
		.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
		.CHtml::link(Yii::t('app','Clear Filters'),array('admin','clearFilters'=>1)). ' | '
		.CHtml::link(Yii::t('app','Records Today'),array('admin','offset'=>'0:00')). ' | '
		.CHtml::link(Yii::t('app','Records This Week'),array('admin','offset'=>'first day of this week')). ' | '
		.CHtml::link(Yii::t('app','Records This Month'),array('admin','offset'=>'first day of this month')). ' | '
		.X2GridView::getFilterHint()
		.'{summary}</div>{items}{pager}',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		array(
                    'name'=>'username',
                    'value'=>'CHtml::link($data->username,$data->id)',
                    'type'=>'raw',
                ),
                'firstName',
		'lastName',
		array(
                    'name'=>'login',
                    'header'=>'Last Login',
                    'value'=>'date("Y-m-d",$data->login)',
                    'type'=>'raw',
                ),
                array(
                    'header'=>'<b>Records Updated</b>',
                    'value'=>'(Changelog::model()->countByAttributes(array(),"changedBy=\"$data->username\" AND timestamp > '.strtotime("$offset").'"))',
                    'type'=>'raw',
                ),
		'emailAddress',
		//'cellPhone',
		//'homePhone',
		//'address',
		//'officePhone',
		//'emailAddress',
		//'status',
	),
));
?>
<?php if($count > 0){ ?>
<br />
<h2><?php echo "Invited Users";?></h2>
<div class="form">
<b><?php echo "$count user(s) have been invited but have not yet completed registration."; ?></b>
<br /><br />
<?php echo "To delete all users who have not completed their invite, click the button below." ?>
<br /><br />
<?php echo CHtml::link('Delete Unregistered','#',array('class'=>'x2-button','submit'=>'deleteTemporary','confirm'=>'Are you sure you want to delete these users?')); ?>
</div>
<?php } ?>
