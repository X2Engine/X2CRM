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

include("protected/config/marketingConfig.php");
Yii::app()->clientScript->registerCss('campaignContentCss',"#Campaign_content_inputBox {min-height:300px;}");

$this->menu = array(
	array('label'=>Yii::t('module','{X} List',array('{X}'=>$moduleConfig['recordName'])), 'url'=>array('index')),
	array('label'=>Yii::t('module','Create'), 'url'=>array('create')),
	array('label'=>Yii::t('module','View')),
	array('label'=>Yii::t('module','Update'), 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>Yii::t('module','Delete'), 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>Yii::t('app','Are you sure you want to delete this item?'))),
);
?>

<h2><?php echo Yii::t('module','View {X}',array('{X}'=>$moduleConfig['recordName'])); ?>: <?php echo $model->name; ?></h2>

<?php $this->renderPartial('application.components.views._detailView',array('model'=>$model, 'modelName'=>'Campaign')); ?>
<div style="overflow:auto;margin-bottom:5px;">
<?php
echo CHtml::submitButton(Yii::t('app','Launch Now'),array('class'=>'x2-button highlight left','id'=>'save-button','style'=>'margin-left:0;'));

echo CHtml::ajaxButton(
	Yii::t('marketing','Launch Now'),
	array('launchCampaign','id'=>$model->id),
	array(
		'beforeSend'=>"function(a,b) { $('#email-sending-icon').show(); }",
		'update'=>'#test-email-result',
		'complete'=>"function(response) { $('#email-sending-icon').hide(); $('#test-email-result').slideDown(); }",
	),
	array(
		'id'=>'preview-email-button',
		'class'=>'x2-button left',
		'style'=>'cursor:pointer;'
	)
);
echo CHtml::ajaxButton(
	Yii::t('app','Send Test Email'),
	array('launchCampaign','id'=>$model->id,'test'=>1),
	array(
		'beforeSend'=>"function(a,b) { $('#email-sending-icon').show(); }",
		'update'=>'#test-email-result',
		'complete'=>"function(response) { $('#email-sending-icon').hide(); $('#test-email-result').slideDown(); }",
	),
	array(
		'id'=>'preview-email-button',
		'class'=>'x2-button left',
		'style'=>'cursor:pointer;'
	)
);
?><?php echo CHtml::image(Yii::app()->theme->getBaseUrl().'/images/loading.gif',Yii::t('app','Loading'),array('id'=>'email-sending-icon','style'=>'display:none;')); ?></div>
<div class="form no-border">
<div id="test-email-result" class="form" style="display:none;">

</div>
</div>
<?php

if(isset($contactList)) {

	$this->widget('application.components.X2GridView', array(
		'id'=>'contacts-grid',
		'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
		'template'=> '<h2>'.$contactList->name.'</h2><div class="title-bar">'
			.CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
			.CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
			.CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
			.'{summary}</div>{items}{pager}',
		'dataProvider'=>Contacts::model()->searchList($contactList->id,10),
		// 'enableSorting'=>false,
		// 'model'=>$model,
		// 'filter'=>$model,
		// 'columns'=>$columns,
		'modelName'=>'Contacts',
		'viewName'=>'campaignContacts',
		// 'columnSelectorId'=>'contacts-column-selector',
		'defaultGvSettings'=>array(
			'gvCheckbox'=>30,
			'name'=>210,
			'phone'=>100,
			'lastUpdated'=>100,
			'leadSource'=>145,
			// 'gvControls'=>66,
		),
		'specialColumns'=>array(
			'name'=>array(
				'name'=>'name',
				'header'=>Yii::t('contacts','Name'),
				'value'=>'CHtml::link($data->name,array("/contacts/view/".$data->id))',
				'type'=>'raw',
			),
		),
		'enableControls'=>true,
		'enableTags'=>true,
	));
}

$this->widget('InlineActionForm',
	array(
		'associationType'=>'Campaign',
		'associationId'=>$model->id,
		'assignedTo'=>Yii::app()->user->getName(),
		'users'=>$users,
		'startHidden'=>false
	)
);
?>

<?php
$this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$actionHistory,
	'itemView'=>'application.modules.actions.views.default._view',
	'htmlOptions'=>array('class'=>'action list-view'),
	'template'=> '<h3>'.Yii::t('app','History').'</h3>{summary}{sorter}{items}{pager}',
));
?>