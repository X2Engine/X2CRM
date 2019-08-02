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






$this->insertMenu(true);

Yii::app()->clientScript->registerScript('leadPerformance',"
	$('#startDate,#endDate').change(function() {
		$('#dateRange').val('custom');
	});

	$('#lead-volume-chart').bind('jqplotDataHighlight', function (e, seriesIndex, pointIndex, data) {
			$('#pie-tooltip').html('<div style=\"font-size:14px;font-weight:bold;padding:5px;color:#000;background:white;background:rgba(255,255,255);\">' + data[0] + '</span>');
			$('#pie-tooltip').show();

			var chart_left = $('#lead-volume-chart').position().left;
				var chart_top = $('#lead-volume-chart').position().top;

			$(document).bind('mousemove.pieChart',function(e) {
				x = e.pageX; //plot.axes.xaxis.u2p(data[0]),  // convert x axis unita to pixels
				y = e.pageY; //plot.axes.yaxis.u2p(data[1]);  // convert y axis units to pixels
				$('#pie-tooltip').css({left:x-chart_left+5, top:y-50});
			});
		});

		// Bind a function to the unhighlight event to clean up after highlighting.
		$('#lead-volume-chart').bind('jqplotDataUnhighlight',
		function (e, seriesIndex, pointIndex, data) {
			$(document).unbind('mousemove.pieChart');
			$('#pie-tooltip').empty();
			$('#pie-tooltip').hide();
		});

",CClientScript::POS_READY);
?>
<div class="reports icon page-title"><h2><?php echo Yii::t('charts', 'Lead Performance'); ?></h2></div>
<div class="form">

	<?php
	$_GET = array();

	$form = $this->beginWidget('CActiveForm', array(
		'action'=>'leadPerformance',
		'id'=>'dateRangeForm',
		'enableAjaxValidation'=>false,
		'method'=>'get'
	));

	// $range = 30; //$model->dateRange;

	// echo $startDate .' '.$endDate;
	$userName = Yii::app()->user->getName();
	$filters = array(
		'createDate BETWEEN '.$dateRange['start'].' AND '.$dateRange['end']
	);
	?>
	<div class="row">
		<div class="cell">
			<?php echo CHtml::label(Yii::t('workflow', 'Process'),'workflow'); ?>
			<?php
			$workflowOptions = array();
			$query = Yii::app()->db->createCommand()
			->select('id,name')
			->from('x2_workflows')->query();
			while(($row = $query->read()) !== false)
				$workflowOptions[$row['id']] = $row['name'];

			echo CHtml::dropDownList('workflow',$workflow,$workflowOptions);
			?>
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
				'custom'=>Yii::t('charts','Custom'),
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
	</div>
	<div class="row">
		<div class="cell">
                    <label><?php echo Yii::t('charts','Field');?></label>
			<?php

                                $fields=Fields::model()->findAllByAttributes(array('modelName'=>'Contacts'));
                                $data=array();
                                foreach($fields as $field){
                                    $data[$field->fieldName]=X2Model::model('Contacts')->getAttributeLabel($field->fieldName);
                                }

                                echo CHtml::dropDownList('field','leadSource',$data,
                                    array(
                                        'id'=>'field-selector',
                                        'ajax' => array(
                                        'type'=>'GET', //request type
                                        'url'=>CController::createUrl('getOptions'), //url to call.
                                        //Style: CController::createUrl('currentController/methodToCall')
                                        'update'=>'#field-options', //selector to update
                                        'complete'=>'function(){
                                            $("#field-options").attr("name","Contacts["+$("#field-selector").val()+"]");
                                            $("#field-options").autocomplete( "option", "source", "getOptions?fieldType="+$("#field-selector").val());
                                         }',
                                        //'data'=>'js:"modelType="+$("'.CHtml::activeId($model,'modelType').'").val()'
                                        //leave out the data key to pass all form values through
                                    )));

				$this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                                    'name'=>'Contacts['.$fieldName.']',
                                    'source'=>'getOptions?fieldType='.$fieldName,
                                    'value'=>$input,
                                    // additional javascript options for the autocomplete plugin

                                    'htmlOptions'=>array(
                                        'id'=>'field-options',
                                    ),
                                ));
			?>
		</div>

		<div class="cell">
			<?php echo CHtml::submitButton(Yii::t('charts','Go'),array('class'=>'x2-button','style'=>'margin-top:13px;')); ?>
		</div>
	</div>
		<?php
                $columns=array(
                    'user'=>array(
                            'name'=>'user',
                            'header'=>Yii::t('contacts','User'),
                            'value'=>'User::getUserLinks($data["id"])',
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
                if(count($stageIds)>0){
                    foreach($stageIds as $name=>$id){
                        $columns[$name]=array(
                                            'name'=>$name,
                                            'header'=>$name,
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
			//'filter'=>$model,
			// 'columns'=>$columns,
			//'modelName'=>'Contacts',
			// 'viewName'=>'leadpercontacts',
			'columns'=>$columns,
		));
	}
	?><br>
	<?php
	$form = $this->endWidget();

	?>

</div>








