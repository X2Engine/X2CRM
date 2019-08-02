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






Yii::app()->clientScript->registerCss('workflowReport',"
.form, #content {
    border: none !important;
    border-radius: 0 !important;
}

#grid-container {
    margin-top: 5px;
}

.x2-layout-island {
    margin-right: 0px !important;
}
");

$this->insertMenu(true);

Yii::app()->clientScript->registerScript('leadPerformance','
	$("#startDate,#endDate").change(function() {
		$("#dateRange").val("custom");
	});
',CClientScript::POS_READY);
?>
<div class='x2-layout-island'>
<div class="reports icon page-title"><h2>
    <?php echo Yii::t('workflow', '{process} Status', array(
        '{process}' => Modules::displayName(false, "Process"),
    )); ?>
</h2></div>
<div class="form">
	<?php $form = $this->beginWidget('CActiveForm', array(
		'action'=>'workflow',
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get'
	)); ?>
	<div class="row">
		<div class="cell">
            <?php echo CHtml::label(Yii::t('workflow', '{process}', array(
                '{process}' => Modules::displayName(false, "Process"),
            )),'workflow'); ?>
			<?php echo CHtml::dropDownList('workflow',$workflow,$workflowOptions,array('id'=>'workflow',
				'ajax'=>array(
					'type'=>'GET',
					'url'=>$this->createUrl('/workflow/workflow/getStages'), //url to call.
					'data'=>'js:"id="+$("#workflow").val()',
					'dataType'=>'json',
					'success'=>'function(response) {
						//console.log(response);
						$("#stage").html("");
						for(var key in response){
							$("<option value=\""+key+"\">"+response[key]+"</option>").appendTo("#stage");
                                                }
					}'
				)
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('workflow', 'Stage'),'stage'); ?>
			<?php echo CHtml::dropDownList('stage',$stage,$stageOptions); ?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Start Date'),'startDate'); ?>
			<?php
			Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');

			$this->widget('CJuiDateTimePicker',array(
				'name'=>'start',
				// 'value'=>$startDate,
				'value'=>Formatter::formatDate($dateRange['start']),
				// 'title'=>Yii::t('app','Start Date'),
				// 'model'=>$model, //Model object
				// 'attribute'=>$field->fieldName, //attribute name
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>Formatter::formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,

				), // jquery plugin options
				'htmlOptions'=>array('id'=>'startDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'End Date'),'startDate'); ?>
			<?php
			$this->widget('CJuiDateTimePicker',array(
				'name'=>'end',
				'value'=>Formatter::formatDate($dateRange['end']),
				// 'value'=>$endDate,
				'mode'=>'date', //use "time","date" or "datetime" (default)
				'options'=>array(
					'dateFormat'=>Formatter::formatDatePicker(),
					'changeMonth'=>true,
					'changeYear'=>true,
				),
				'htmlOptions'=>array('id'=>'endDate','width'=>20),
				'language' => (Yii::app()->language == 'en')? '':Yii::app()->getLanguage(),
			));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Date Range'),'range'); ?>
			<?php
			echo CHtml::dropDownList('range',$dateRange['range'],array(
				''=>Yii::t('charts','Custom'),
				'thisWeek'=>Yii::t('charts','This Week'),
				'thisMonth'=>Yii::t('charts','This Month'),
				'lastWeek'=>Yii::t('charts','Last Week'),
				'lastMonth'=>Yii::t('charts','Last Month'),
				// 'lastQuarter'=>Yii::t('charts','Last Quarter'),
				'thisYear'=>Yii::t('charts','This Year'),
				'lastYear'=>Yii::t('charts','Last Year'),
                                'all'=>Yii::t('charts','All Time'),

			),array('id'=>'dateRange'));
			?>
		</div>
		<div class="cell">
			<?php echo CHtml::label(Yii::t('charts', 'Strict Mode'),'strict'); ?>
			<?php
			echo CHtml::checkbox('strict',$dateRange['strict'],array('id'=>'strict'));
			?>
		</div>

		<div class="cell">
			<?php echo CHtml::submitButton(Yii::t('charts','Go'),array('name'=>'','class'=>'x2-button','style'=>'margin-top:13px;')); ?>
		</div>
	</div>
		<?php
		/* $columns=array(
			'user'=>array(
				'name'=>'user',
				'header'=>Yii::t('contacts','User'),
				'value'=>'empty($data["id"])? $data["name"] : CHtml::link($data["name"],array("/users/".$data["id"]))',
				'type'=>'raw',
				'headerHtmlOptions'=>array('style'=>'width:20%')
			),
			'leads'=>array(
				'name'=>'leads',
				'header'=>Yii::t('contacts','Contacts'),
				'value'=>'$data["leads"]',
				'type'=>'raw',
			),
		);
		if(count($stageIds)>0) {
			foreach($stageIds as $name=>$id) {
				$columns[$name]=array(
					'name'=>$name,
					'header'=>Yii::t('contacts',$name),
					'value'=>'$data["'.$name.'"]',
					'type'=>'raw',
				);
			}
		}
		if(isset($dataProvider)) {
		$this->widget('zii.widgets.grid.CGridView', array(
			'id'=>'lead-activity-grid',
			'baseScriptUrl'=>Yii::app()->request->baseUrl.'/themes/'.Yii::app()->theme->name.'/css/gridview',
			'template'=> '{items}{pager}',
			// 'template'=> '<h2>'.Yii::t('charts','Lead Activity').'</h2><div class="title-bar">'
				// .CHtml::link(Yii::t('app','Advanced Search'),'#',array('class'=>'search-button')) . ' | '
				// .CHtml::link(Yii::t('app','Clear Filters'),array(Yii::app()->controller->action->id,'clearFilters'=>1)) . ' | '
				// .CHtml::link(Yii::t('app','Columns'),'javascript:void(0);',array('class'=>'column-selector-link'))
				// .'{summary}</div>{items}{pager}',
			'dataProvider'=>$dataProvider,
			// 'enableSorting'=>false,
			'enablePagination'=>false,
			// 'model'=>$model,
			// 'filter'=>$model,
			// 'columns'=>$columns,
			//'modelName'=>'Contacts',
			// 'viewName'=>'leadpercontacts',
			'columns'=>$columns,
		));
	} */
	?>
</div>
</div>
<?php
	if(isset($dataProvider)) {
?>
<div id='grid-container' class='x2-layout-island'>
<?php

		$this->widget('X2GridView', array(
			'id'=>'reports-workflow-grid',
            'title'=>Yii::t('contacts','{contacts}', array(
                '{contacts}' => Modules::displayName(true, "Contacts"),
            )),
			// 'buttons'=>array('advancedSearch','clearFilters','columnSelector'),
			'template'=> '<div class="reports page-title rounded-top">{title}{buttons}{summary}</div>{items}{pager}',
			'dataProvider'=>$dataProvider,
			// 'enableSorting'=>false,
			// 'model'=>$model,
			// 'filter'=>$model,
			// 'columns'=>$columns,
			'modelName'=>'Contacts',
			'viewName'=>'contacts-workflow',
			// 'columnSelectorId'=>'contacts-column-selector',
			'defaultGvSettings'=>array(
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
					'value'=>'CHtml::link($data->name,array("/contacts/".$data->id), array("class" => "contact-name"))',
					// 'value'=>'$data->getLink()',
					'type'=>'raw',
				),
			),
			'enableControls'=>false,
			'enableTags'=>true,
			'fullscreen'=>true,
		));
	}
	// echo CHtml::link(Yii::t('app','New List From Selection'),'#',array('id'=>'createList','class'=>'list-action'));

	// $listNames = array();
	// foreach(X2List::model()->findAllByAttributes(array('type'=>'static')) as $list) {	// get all static lists
		// if($this->checkPermissions($list,'edit'))	// check permissions
			// $listNames[$list->id] = $list->name;
	// }

	// if(!empty($listNames)) {
		// echo ' | '.CHtml::link(Yii::t('app','Add to list:'),'#',array('id'=>'addToList','class'=>'list-action'));
		// echo CHtml::dropDownList('addToListTarget',null,$listNames, array('id'=>'addToListTarget'));
	// }
	?>
</div>
	<?php
	$form = $this->endWidget();
